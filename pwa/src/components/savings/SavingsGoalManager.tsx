import { useEffect, useMemo, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import {
  createSavingsGoal,
  updateSavingsGoal,
  deleteSavingsGoal,
  setActiveGoal,
  fetchSavingsGoals,
} from '@/store/savingsGoalsSlice';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Plus, Target, Edit, Trash2, Loader2, AlertTriangle } from 'lucide-react';
import type { SavingsGoal } from '@/types/api';
import { useToast } from '@/hooks/use-toast';

interface SavingsGoalManagerProps {
  memberId: number;
}

export function SavingsGoalManager({ memberId }: SavingsGoalManagerProps) {
  const dispatch = useDispatch<AppDispatch>();
  const { goals, activeGoal, loading, error } = useSelector((state: RootState) => state.savingsGoals);
  const { toast } = useToast();

  const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
  const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
  const [editingGoal, setEditingGoal] = useState<SavingsGoal | null>(null);
  
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    target_amount: '',
    target_date: '',
  });

  useEffect(() => {
    if (!goals.length) {
      dispatch(fetchSavingsGoals(undefined));
    }
  }, [dispatch, goals.length]);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const resetForm = () => {
    setFormData({ title: '', description: '', target_amount: '', target_date: '' });
  };

  const handleCreateGoal = async () => {
    if (!formData.title || !formData.target_amount) return;

    try {
      await dispatch(
        createSavingsGoal({
          title: formData.title,
          description: formData.description || undefined,
          target_amount: parseFloat(formData.target_amount),
          target_date: formData.target_date || undefined,
        })
      ).unwrap();

      resetForm();
      setIsCreateDialogOpen(false);
      toast({
        title: 'Savings goal created',
        description: 'We have added your new savings goal.',
      });
    } catch (createError) {
      toast({
        title: 'Could not create goal',
        description: createError instanceof Error ? createError.message : 'Please try again.',
        variant: 'destructive',
      });
    }
  };

  const handleEditGoal = async () => {
    if (!editingGoal || !formData.title || !formData.target_amount) return;

    try {
      await dispatch(
        updateSavingsGoal({
          goalId: editingGoal.id,
          updates: {
            title: formData.title,
            description: formData.description || undefined,
            target_amount: parseFloat(formData.target_amount),
            target_date: formData.target_date || undefined,
          },
        })
      ).unwrap();

      resetForm();
      setEditingGoal(null);
      setIsEditDialogOpen(false);
      toast({
        title: 'Savings goal updated',
        description: 'Your goal details were saved.',
      });
    } catch (updateError) {
      toast({
        title: 'Could not update goal',
        description: updateError instanceof Error ? updateError.message : 'Please try again.',
        variant: 'destructive',
      });
    }
  };

  const handleDeleteGoal = async (goalId: number) => {
    if (!confirm('Are you sure you want to delete this savings goal?')) {
      return;
    }

    try {
      await dispatch(deleteSavingsGoal(goalId)).unwrap();
      toast({
        title: 'Savings goal removed',
        description: 'We have deleted the savings goal.',
      });
    } catch (deleteError) {
      toast({
        title: 'Could not delete goal',
        description: deleteError instanceof Error ? deleteError.message : 'Please try again.',
        variant: 'destructive',
      });
    }
  };

  const handleSetActiveGoal = (goal: SavingsGoal) => {
    dispatch(setActiveGoal(goal));
  };

  const openEditDialog = (goal: SavingsGoal) => {
    setEditingGoal(goal);
    setFormData({
      title: goal.title,
      description: goal.description || '',
      target_amount: goal.target_amount.toString(),
      target_date: goal.target_date || '',
    });
    setIsEditDialogOpen(true);
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active':
        return 'bg-green-100 text-green-700 dark:bg-green-900/20';
      case 'completed':
        return 'bg-blue-100 text-blue-700 dark:bg-blue-900/20';
      case 'paused':
        return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20';
      case 'cancelled':
        return 'bg-gray-100 text-gray-700 dark:bg-gray-900/20';
      default:
        return 'bg-gray-100 text-gray-700 dark:bg-gray-900/20';
    }
  };

  const orderedGoals = useMemo(
    () =>
      [...goals].sort((a, b) => {
        const aDate = a.created_at ? new Date(a.created_at).getTime() : 0;
        const bDate = b.created_at ? new Date(b.created_at).getTime() : 0;
        return bDate - aDate;
      }),
    [goals]
  );

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold font-heading">Savings Goals</h2>
          <p className="text-muted-foreground">Set and track your savings targets</p>
        </div>
        <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
          <DialogTrigger asChild>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              New Goal
            </Button>
          </DialogTrigger>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Create Savings Goal</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="title">Goal Title</Label>
                <Input
                  id="title"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  placeholder="e.g., Emergency Fund"
                />
              </div>
              <div>
                <Label htmlFor="description">Description (Optional)</Label>
                <Textarea
                  id="description"
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  placeholder="Describe your savings goal..."
                />
              </div>
              <div>
                <Label htmlFor="target_amount">Target Amount</Label>
                <Input
                  id="target_amount"
                  type="number"
                  value={formData.target_amount}
                  onChange={(e) => setFormData({ ...formData, target_amount: e.target.value })}
                  placeholder="1000000"
                />
              </div>
              <div>
                <Label htmlFor="target_date">Target Date (Optional)</Label>
                <Input
                  id="target_date"
                  type="date"
                  value={formData.target_date}
                  onChange={(e) => setFormData({ ...formData, target_date: e.target.value })}
                />
              </div>
              <div className="flex gap-2">
                <Button onClick={handleCreateGoal} className="flex-1" disabled={loading}>
                  {loading ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Plus className="w-4 h-4 mr-2" />}
                  Create Goal
                </Button>
                <Button variant="outline" onClick={() => setIsCreateDialogOpen(false)}>
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      {error && (
        <Alert variant="destructive">
          <AlertTriangle className="h-4 w-4" />
          <AlertTitle>Unable to load savings goals</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      {/* Goals List */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {orderedGoals.map((goal) => (
          <Card key={goal.id} className="hover:shadow-md transition-shadow">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-lg">{goal.title}</CardTitle>
                <div className="flex gap-1">
                  {activeGoal?.id === goal.id && (
                    <Badge variant="default" className="text-xs">
                      Active
                    </Badge>
                  )}
                  <Badge className={`text-xs ${getStatusColor(goal.status)}`}>
                    {goal.status}
                  </Badge>
                </div>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              {goal.description && (
                <p className="text-sm text-muted-foreground">{goal.description}</p>
              )}

              <div className="space-y-2">
                <div className="flex justify-between text-sm">
                  <span>Target:</span>
                  <span className="font-semibold">{formatCurrency(goal.target_amount)}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span>Current:</span>
                  <span className="font-semibold">{formatCurrency(goal.current_amount)}</span>
                </div>
                <div className="w-full bg-accent rounded-full h-2">
                  <div
                    className="bg-primary h-2 rounded-full transition-all duration-500"
                    style={{ width: `${Math.min((goal.progress?.percentage ?? (goal.current_amount / goal.target_amount) * 100), 100)}%` }}
                  />
                </div>
                <div className="text-center text-xs text-muted-foreground">
                  {Math.min(100, Math.round(goal.progress?.percentage ?? (goal.current_amount / goal.target_amount) * 100))}% achieved
                </div>
              </div>

              {goal.target_date && (
                <div className="text-xs text-muted-foreground">
                  Target date: {new Date(goal.target_date).toLocaleDateString()}
                </div>
              )}

              {goal.nudge?.should_display && (
                <Alert className="border-amber-300 bg-amber-50 text-amber-900">
                  <AlertTriangle className="h-4 w-4" />
                  <AlertTitle>Keep going</AlertTitle>
                  <AlertDescription>{goal.nudge.message}</AlertDescription>
                </Alert>
              )}

              <div className="flex gap-2">
                {activeGoal?.id !== goal.id && (
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => handleSetActiveGoal(goal)}
                    className="flex-1"
                  >
                    <Target className="w-3 h-3 mr-1" />
                    Set Active
                  </Button>
                )}
                <Button size="sm" variant="outline" onClick={() => openEditDialog(goal)}>
                  <Edit className="w-3 h-3" />
                </Button>
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() => handleDeleteGoal(goal.id)}
                  className="text-red-600 hover:text-red-700"
                >
                  <Trash2 className="w-3 h-3" />
                </Button>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {orderedGoals.length === 0 && !loading && (
        <Card>
          <CardContent className="text-center py-12">
            <Target className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
            <h3 className="text-lg font-medium text-muted-foreground mb-2">
              No Savings Goals
            </h3>
            <p className="text-sm text-muted-foreground mb-4">
              Create your first savings goal to start tracking your progress.
            </p>
            <Button onClick={() => setIsCreateDialogOpen(true)}>
              <Plus className="w-4 h-4 mr-2" />
              Create Goal
            </Button>
          </CardContent>
        </Card>
      )}

      {/* Edit Dialog */}
      <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Edit Savings Goal</DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label htmlFor="edit-title">Goal Title</Label>
              <Input
                id="edit-title"
                value={formData.title}
                onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                placeholder="e.g., Emergency Fund"
              />
            </div>
            <div>
              <Label htmlFor="edit-description">Description (Optional)</Label>
              <Textarea
                id="edit-description"
                value={formData.description}
                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                placeholder="Describe your savings goal..."
              />
            </div>
            <div>
              <Label htmlFor="edit-target_amount">Target Amount</Label>
              <Input
                id="edit-target_amount"
                type="number"
                value={formData.target_amount}
                onChange={(e) => setFormData({ ...formData, target_amount: e.target.value })}
                placeholder="1000000"
              />
            </div>
            <div>
              <Label htmlFor="edit-target_date">Target Date (Optional)</Label>
              <Input
                id="edit-target_date"
                type="date"
                value={formData.target_date}
                onChange={(e) => setFormData({ ...formData, target_date: e.target.value })}
              />
            </div>
            <div className="flex gap-2">
              <Button onClick={handleEditGoal} className="flex-1" disabled={loading}>
                {loading ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : null}
                Update Goal
              </Button>
              <Button variant="outline" onClick={() => setIsEditDialogOpen(false)}>
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}

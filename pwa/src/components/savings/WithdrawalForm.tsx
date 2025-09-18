import { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { useToast } from '@/hooks/use-toast';
import { RootState } from '@/store';
import { makeWithdrawal } from '@/store/transactionsSlice';
import { ArrowDownRight, CreditCard, AlertTriangle } from 'lucide-react';
import type { SavingsAccount } from '@/types/api';

interface WithdrawalFormProps {
  isOpen: boolean;
  onClose: () => void;
  account?: SavingsAccount;
}

export function WithdrawalForm({ isOpen, onClose, account }: WithdrawalFormProps) {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { loading } = useSelector((state: RootState) => state.transactions);
  const { user } = useSelector((state: RootState) => state.auth);
  
  const [formData, setFormData] = useState({
    amount: '',
    description: '',
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!account) {
      toast({
        title: "Error",
        description: "No account selected",
        variant: "destructive",
      });
      return;
    }

    const amount = parseFloat(formData.amount);
    if (!amount || amount <= 0) {
      toast({
        title: "Error",
        description: "Please enter a valid amount",
        variant: "destructive",
      });
      return;
    }

    if (amount > account.available_balance) {
      toast({
        title: "Error",
        description: "Insufficient balance for this withdrawal",
        variant: "destructive",
      });
      return;
    }

    const remainingBalance = account.available_balance - amount;
    if (remainingBalance < account.minimum_balance) {
      toast({
        title: "Error",
        description: `Withdrawal would leave balance below minimum of ${formatCurrency(account.minimum_balance)}`,
        variant: "destructive",
      });
      return;
    }

    try {
      const result = await dispatch(makeWithdrawal({
        member_id: user?.id || 0,
        account_id: account.id,
        amount,
        description: formData.description || `Withdrawal from ${account.account_number}`,
      }) as any);

      if (makeWithdrawal.fulfilled.match(result)) {
        toast({
          title: "Success",
          description: `Withdrawal of KES ${amount.toLocaleString()} successful`,
        });
        setFormData({ amount: '', description: '' });
        onClose();
      }
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to make withdrawal",
        variant: "destructive",
      });
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', {
      style: 'currency',
      currency: 'KES',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const amount = parseFloat(formData.amount) || 0;
  const remainingBalance = account ? account.available_balance - amount : 0;
  const belowMinimum = account ? remainingBalance < account.minimum_balance : false;

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <ArrowDownRight className="w-5 h-5 text-red-600" />
            Make Withdrawal
          </DialogTitle>
        </DialogHeader>

        {account && (
          <div className="bg-muted/50 p-4 rounded-lg mb-4">
            <div className="flex items-center gap-2 mb-2">
              <CreditCard className="w-4 h-4" />
              <span className="font-medium">{account.account_number}</span>
            </div>
            <p className="text-sm text-muted-foreground">
              {account.savings_product.name}
            </p>
            <p className="text-lg font-semibold">
              Available Balance: {formatCurrency(account.available_balance)}
            </p>
            <p className="text-sm text-muted-foreground">
              Minimum Balance: {formatCurrency(account.minimum_balance)}
            </p>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <Label htmlFor="amount">Amount (KES) *</Label>
            <Input
              id="amount"
              type="number"
              min="1"
              step="0.01"
              max={account?.available_balance}
              value={formData.amount}
              onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
              placeholder="Enter withdrawal amount"
              required
              disabled={loading}
            />
            {amount > 0 && account && (
              <div className="mt-2 space-y-1">
                <p className="text-xs text-muted-foreground">
                  Remaining balance: {formatCurrency(remainingBalance)}
                </p>
                {account.savings_product.withdrawal_fee && (
                  <p className="text-xs text-muted-foreground">
                    Withdrawal fee: {formatCurrency(account.savings_product.withdrawal_fee)}
                  </p>
                )}
              </div>
            )}
          </div>

          {belowMinimum && (
            <Alert variant="destructive">
              <AlertTriangle className="h-4 w-4" />
              <AlertDescription>
                This withdrawal would leave your balance below the minimum required balance of {formatCurrency(account!.minimum_balance)}.
              </AlertDescription>
            </Alert>
          )}

          <div>
            <Label htmlFor="description">Description (Optional)</Label>
            <Textarea
              id="description"
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              placeholder="Enter description for this withdrawal"
              rows={3}
              disabled={loading}
            />
          </div>

          <div className="flex gap-3 pt-4">
            <Button 
              type="submit" 
              disabled={loading || belowMinimum || amount <= 0 || (account && amount > account.available_balance)} 
              className="flex-1"
              variant="destructive"
            >
              {loading ? 'Processing...' : 'Make Withdrawal'}
            </Button>
            <Button type="button" variant="outline" onClick={onClose} disabled={loading}>
              Cancel
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
import { useState } from 'react';
import { Plus, Minus, TrendingUp, FileText } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { makeDeposit, makeWithdrawal } from '@/store/savingsSlice';
import { useToast } from '@/hooks/use-toast';

export function QuickActions() {
  const [depositAmount, setDepositAmount] = useState('');
  const [withdrawAmount, setWithdrawAmount] = useState('');
  const [selectedAccount, setSelectedAccount] = useState('');
  const [isDepositOpen, setIsDepositOpen] = useState(false);
  const [isWithdrawOpen, setIsWithdrawOpen] = useState(false);

  const dispatch = useDispatch<AppDispatch>();
  const { toast } = useToast();
  const { accounts } = useSelector((state: RootState) => state.savings);
  const { loading } = useSelector((state: RootState) => state.savings);

  const handleDeposit = async () => {
    if (!selectedAccount || !depositAmount) {
      toast({
        title: "Error",
        description: "Please select an account and enter an amount",
        variant: "destructive",
      });
      return;
    }

    try {
      await dispatch(makeDeposit({ 
        accountId: parseInt(selectedAccount), 
        amount: parseFloat(depositAmount) 
      }));
      
      toast({
        title: "Success",
        description: "Deposit completed successfully",
      });
      
      setDepositAmount('');
      setSelectedAccount('');
      setIsDepositOpen(false);
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to process deposit",
        variant: "destructive",
      });
    }
  };

  const handleWithdrawal = async () => {
    if (!selectedAccount || !withdrawAmount) {
      toast({
        title: "Error",
        description: "Please select an account and enter an amount",
        variant: "destructive",
      });
      return;
    }

    try {
      await dispatch(makeWithdrawal({ 
        accountId: parseInt(selectedAccount), 
        amount: parseFloat(withdrawAmount) 
      }));
      
      toast({
        title: "Success",
        description: "Withdrawal completed successfully",
      });
      
      setWithdrawAmount('');
      setSelectedAccount('');
      setIsWithdrawOpen(false);
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to process withdrawal",
        variant: "destructive",
      });
    }
  };

  const actions = [
    {
      icon: Plus,
      label: 'Deposit',
      description: 'Add money to account',
      color: 'text-green-600',
      bgColor: 'bg-green-50 hover:bg-green-100',
      onClick: () => setIsDepositOpen(true),
    },
    {
      icon: Minus,
      label: 'Withdraw',
      description: 'Take money out',
      color: 'text-red-600',
      bgColor: 'bg-red-50 hover:bg-red-100',
      onClick: () => setIsWithdrawOpen(true),
    },
    {
      icon: TrendingUp,
      label: 'Buy Shares',
      description: 'Invest in shares',
      color: 'text-blue-600',
      bgColor: 'bg-blue-50 hover:bg-blue-100',
      onClick: () => {}, // TODO: Implement shares purchase
    },
    {
      icon: FileText,
      label: 'Statement',
      description: 'View account statement',
      color: 'text-purple-600',
      bgColor: 'bg-purple-50 hover:bg-purple-100',
      onClick: () => {}, // TODO: Implement statement generation
    },
  ];

  return (
    <Card>
      <CardHeader>
        <CardTitle className="font-heading">Quick Actions</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {actions.map((action) => (
            <Button
              key={action.label}
              variant="ghost"
              className={`h-20 flex-col gap-2 ${action.bgColor} ${action.color} border border-border/50`}
              onClick={action.onClick}
            >
              <action.icon className="w-6 h-6" />
              <div className="text-center">
                <p className="font-medium text-sm">{action.label}</p>
                <p className="text-xs opacity-75">{action.description}</p>
              </div>
            </Button>
          ))}
        </div>

        {/* Deposit Dialog */}
        <Dialog open={isDepositOpen} onOpenChange={setIsDepositOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Make a Deposit</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="deposit-account">Select Account</Label>
                <Select value={selectedAccount} onValueChange={setSelectedAccount}>
                  <SelectTrigger>
                    <SelectValue placeholder="Choose savings account" />
                  </SelectTrigger>
                  <SelectContent>
                    {accounts.map((account) => (
                      <SelectItem key={account.id} value={account.id.toString()}>
                        {account.product_name} - {account.account_number}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              
              <div>
                <Label htmlFor="deposit-amount">Amount (UGX)</Label>
                <Input
                  id="deposit-amount"
                  type="number"
                  placeholder="0.00"
                  value={depositAmount}
                  onChange={(e) => setDepositAmount(e.target.value)}
                />
              </div>
              
              <Button 
                onClick={handleDeposit} 
                disabled={loading}
                className="w-full"
              >
                {loading ? 'Processing...' : 'Confirm Deposit'}
              </Button>
            </div>
          </DialogContent>
        </Dialog>

        {/* Withdrawal Dialog */}
        <Dialog open={isWithdrawOpen} onOpenChange={setIsWithdrawOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Make a Withdrawal</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="withdraw-account">Select Account</Label>
                <Select value={selectedAccount} onValueChange={setSelectedAccount}>
                  <SelectTrigger>
                    <SelectValue placeholder="Choose savings account" />
                  </SelectTrigger>
                  <SelectContent>
                    {accounts.map((account) => (
                      <SelectItem key={account.id} value={account.id.toString()}>
                        {account.product_name} - {account.account_number}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              
              <div>
                <Label htmlFor="withdraw-amount">Amount (UGX)</Label>
                <Input
                  id="withdraw-amount"
                  type="number"
                  placeholder="0.00"
                  value={withdrawAmount}
                  onChange={(e) => setWithdrawAmount(e.target.value)}
                />
              </div>
              
              <Button 
                onClick={handleWithdrawal} 
                disabled={loading}
                className="w-full"
              >
                {loading ? 'Processing...' : 'Confirm Withdrawal'}
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </CardContent>
    </Card>
  );
}
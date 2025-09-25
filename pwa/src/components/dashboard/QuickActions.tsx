import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { makeDeposit, makeWithdrawal } from '@/store/savingsSlice';
import { useToast } from '@/hooks/use-toast';
import { reportsAPI } from '@/api/reports';
import { Coins, FileText, PiggyBank, Waypoints,  } from 'lucide-react';

export function QuickActions() {
  const navigate = useNavigate();
  const [depositAmount, setDepositAmount] = useState('');
  const [withdrawAmount, setWithdrawAmount] = useState('');
  const [selectedAccount, setSelectedAccount] = useState('');
  const [isDepositOpen, setIsDepositOpen] = useState(false);
  const [isWithdrawOpen, setIsWithdrawOpen] = useState(false);
  const [downloadingStatement, setDownloadingStatement] = useState(false);

  const dispatch = useDispatch<AppDispatch>();
  const { toast } = useToast();
  const { accounts, loading } = useSelector((state: RootState) => state.savings);
  const { user } = useSelector((state: RootState) => state.auth);

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
      const result = await dispatch(makeDeposit({ 
        account_id: parseInt(selectedAccount), 
        amount: parseFloat(depositAmount),
        payment_method: 'cash',
        description: 'Quick deposit from dashboard',
        member_id: (user as any)?.id
      }) as any);

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
      const result = await dispatch(makeWithdrawal({ 
        account_id: parseInt(selectedAccount), 
        amount: parseFloat(withdrawAmount),
        description: 'Quick withdrawal from dashboard',
        member_id: (user as any)?.id
      }) as any);

      if (makeWithdrawal.fulfilled.match(result)) {
        toast({
          title: "Success",
          description: "Withdrawal completed successfully",
        });
        
        setWithdrawAmount('');
        setSelectedAccount('');
        setIsWithdrawOpen(false);
      }
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to process withdrawal",
        variant: "destructive",
      });
    }
  };

  const handleBuyShares = () => {
    navigate('/shares');
  };

  const handleDownloadStatement = async () => {
    setDownloadingStatement(true);
    try {
      const blob = await reportsAPI.downloadStatement({
        from_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        to_date: new Date().toISOString().split('T')[0],
      });
      
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `statement-${new Date().toISOString().split('T')[0]}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
      
      toast({
        title: "Success",
        description: "Statement downloaded successfully",
      });
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to download statement",
        variant: "destructive",
      });
    } finally {
      setDownloadingStatement(false);
    }
  };

  const actions = [
    {
      icon: PiggyBank,
      label: 'Deposit',
      gradient: 'from-emerald-400 to-green-600',
      shadow: 'shadow-green-500/25',
      hoverShadow: 'hover:shadow-green-500/40',
      textColor: 'text-emerald-600',
      bgColor: 'bg-gradient-to-br from-emerald-50 to-green-50',
      onClick: () => setIsDepositOpen(true),
    },
    {
      icon: Coins,
      label: 'Withdraw',
      gradient: 'from-red-400 to-rose-600',
      shadow: 'shadow-red-500/25',
      hoverShadow: 'hover:shadow-red-500/40',
      textColor: 'text-red-600',
      bgColor: 'bg-gradient-to-br from-red-50 to-rose-50',
      onClick: () => setIsWithdrawOpen(true),
    },
    {
      icon: Waypoints,
      label: 'Buy Shares',
      gradient: 'from-blue-400 to-indigo-600',
      shadow: 'shadow-blue-500/25',
      hoverShadow: 'hover:shadow-blue-500/40',
      textColor: 'text-blue-600',
      bgColor: 'bg-gradient-to-br from-blue-50 to-indigo-50',
      onClick: handleBuyShares
    },
    {
      icon: FileText,
      label: 'Statement',
      gradient: 'from-purple-400 to-violet-600',
      shadow: 'shadow-purple-500/25',
      hoverShadow: 'hover:shadow-purple-500/40',
      textColor: 'text-purple-600',
      bgColor: 'bg-gradient-to-br from-purple-50 to-violet-50',
      onClick: handleDownloadStatement,
      loading: downloadingStatement
    },
  ];

  return (
    <div className="w-full">

      {/* Actions Grid */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {actions.map((action) => {
          const IconComponent = action.icon;
          return (
            <button
              key={action.label}
              onClick={action.onClick}
              disabled={action.loading}
              className={`group relative overflow-hidden rounded-2xl sm:rounded-3xl p-4 sm:p-6 
                ${action.bgColor} border border-white/20 backdrop-blur-sm
                transform transition-all duration-300 ease-out
                hover:scale-105 hover:-translate-y-1 ${action.hoverShadow}
                active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed
                ${action.shadow} hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
            >
              {/* Background Gradient Overlay */}
              <div className={`absolute inset-0 bg-gradient-to-br ${action.gradient} opacity-0 
                group-hover:opacity-5 transition-opacity duration-300`} />
              
              {/* Content */}
              <div className="relative z-10 flex flex-col items-center text-center space-y-2 sm:space-y-3">
                {/* Icon */}
                <div className={`${action.textColor} transform transition-transform duration-300 
                  group-hover:scale-110 group-active:scale-95`}>
                  <IconComponent className="w-6 h-6 sm:w-8 sm:h-8" />
                </div>
                
                {/* Text */}
                <div className="space-y-0.5 sm:space-y-1">
                  <h3 className={`font-semibold text-sm sm:text-base ${action.textColor} 
                    group-hover:font-bold transition-all duration-200`}>
                    {action.loading ? 'Loading...' : action.label}
                  </h3>
                </div>
              </div>

              {/* Hover Effect Border */}
              <div className={`absolute inset-0 rounded-2xl sm:rounded-3xl border-2 border-transparent 
                bg-gradient-to-br ${action.gradient} opacity-0 group-hover:opacity-20 
                transition-opacity duration-300 -z-10`} />
            </button>
          );
        })}
      </div>

      {/* Deposit Dialog */}
      <Dialog open={isDepositOpen} onOpenChange={setIsDepositOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <PiggyBank className="w-5 h-5 text-green-600" />
              Make a Deposit
            </DialogTitle>
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
                      {(account as any)?.product_name || 'Savings Account'} - {account.account_number}
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
              className="w-full bg-green-600 hover:bg-green-700"
            >
              {loading ? 'Processing...' : 'Confirm Deposit'}
            </Button>
          </div>
        </DialogContent>
      </Dialog>

      {/* Withdrawal Dialog */}
      <Dialog open={isWithdrawOpen} onOpenChange={setIsWithdrawOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <Coins className="w-5 h-5 text-red-600" />
              Make a Withdrawal
            </DialogTitle>
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
                      {(account as any)?.product_name || 'Savings Account'} - {account.account_number}
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
              className="w-full bg-red-600 hover:bg-red-700"
            >
              {loading ? 'Processing...' : 'Confirm Withdrawal'}
            </Button>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
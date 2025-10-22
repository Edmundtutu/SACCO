import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { fetchWalletBalance } from '@/store/walletSlice';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Wallet, ArrowUpRight, ArrowDownRight, RefreshCw, Send } from 'lucide-react';

interface WalletCardProps {
  accountId: number;
  onTopup: () => void;
  onWithdraw: () => void;
  onTransfer?: () => void;
  compact?: boolean;
}

export function WalletCard({ accountId, onTopup, onWithdraw, onTransfer, compact = false }: WalletCardProps) {
  const dispatch = useDispatch<AppDispatch>();
  const { balance, loading } = useSelector((state: RootState) => state.wallet);

  useEffect(() => {
    if (accountId) {
      dispatch(fetchWalletBalance(accountId));
    }
  }, [dispatch, accountId]);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const handleRefresh = () => {
    dispatch(fetchWalletBalance(accountId));
  };

  if (!balance && loading) {
    return (
      <Card>
        <CardContent className="p-6">
          <div className="animate-pulse space-y-3">
            <div className="h-4 bg-muted rounded w-24"></div>
            <div className="h-8 bg-muted rounded w-32"></div>
          </div>
        </CardContent>
      </Card>
    );
  }

  if (!balance) {
    return (
      <Card>
        <CardContent className="p-6 text-center">
          <Wallet className="w-12 h-12 text-muted-foreground mx-auto mb-2" />
          <p className="text-sm text-muted-foreground">No wallet account found</p>
        </CardContent>
      </Card>
    );
  }

  if (compact) {
    return (
      <Card className="border-primary/20 bg-gradient-to-br from-primary/5 to-primary/10">
        <CardContent className="p-4">
          <div className="flex items-center justify-between mb-3">
            <div className="flex items-center gap-2">
              <div className="h-10 w-10 bg-primary/10 rounded-full flex items-center justify-center">
                <Wallet className="h-5 w-5 text-primary" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground font-medium">Wallet Balance</p>
                <p className="text-xs text-muted-foreground font-mono">{balance.account_number}</p>
              </div>
            </div>
            <Button variant="ghost" size="icon" onClick={handleRefresh} disabled={loading}>
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
            </Button>
          </div>
          <p className="text-2xl font-bold text-primary mb-3">{formatCurrency(balance.balance)}</p>
          <div className="flex gap-2">
            <Button onClick={onTopup} size="sm" className="flex-1">
              <ArrowUpRight className="w-3 h-3 mr-1" />
              Top-up
            </Button>
            <Button onClick={onWithdraw} size="sm" variant="outline" className="flex-1">
              <ArrowDownRight className="w-3 h-3 mr-1" />
              Withdraw
            </Button>
            {onTransfer && (
              <Button onClick={onTransfer} size="sm" variant="outline">
                <Send className="w-3 h-3" />
              </Button>
            )}
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className="border-primary/20 bg-gradient-to-br from-primary/5 to-primary/10 hover:shadow-md transition-shadow">
      <CardContent className="p-6">
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-3">
            <div className="h-12 w-12 bg-primary/10 rounded-lg flex items-center justify-center">
              <Wallet className="h-6 w-6 text-primary" />
            </div>
            <div>
              <h3 className="font-semibold text-lg">Wallet</h3>
              <p className="text-sm text-muted-foreground font-mono">{balance.account_number}</p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Badge variant="secondary" className="bg-green-100 text-green-700">Active</Badge>
            <Button variant="ghost" size="icon" onClick={handleRefresh} disabled={loading}>
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
            </Button>
          </div>
        </div>

        <div className="space-y-1 mb-4">
          <p className="text-sm text-muted-foreground">Available Balance</p>
          <p className="text-3xl font-bold text-primary">{formatCurrency(balance.balance)}</p>
          {balance.last_transaction_date && (
            <p className="text-xs text-muted-foreground">
              Last transaction: {new Date(balance.last_transaction_date).toLocaleDateString()}
            </p>
          )}
        </div>

        <div className="grid grid-cols-2 gap-2">
          <Button onClick={onTopup} className="w-full">
            <ArrowUpRight className="w-4 h-4 mr-2" />
            Top-up
          </Button>
          <Button onClick={onWithdraw} variant="outline" className="w-full">
            <ArrowDownRight className="w-4 h-4 mr-2" />
            Withdraw
          </Button>
          {onTransfer && (
            <Button onClick={onTransfer} variant="outline" className="col-span-2">
              <Send className="w-4 h-4 mr-2" />
              Transfer to Savings
            </Button>
          )}
        </div>
      </CardContent>
    </Card>
  );
}

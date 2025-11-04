import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Wallet, TrendingUp, Eye, ArrowUpRight, ArrowDownRight } from 'lucide-react';
import { DepositForm } from './DepositForm';
import { WithdrawalForm } from './WithdrawalForm';

import type { Account } from '@/types/api';
import { getSavingsAccount } from '@/utils/accountHelpers';

interface AccountsListProps {
  accounts: Account[];
  loading: boolean;
  onAccountSelect: (accountId: number) => void;
}

export function AccountsList({ accounts, loading, onAccountSelect }: AccountsListProps) {
  const [depositForm, setDepositForm] = useState<{ isOpen: boolean; account?: Account }>({
    isOpen: false,
    account: undefined,
  });
  const [withdrawalForm, setWithdrawalForm] = useState<{ isOpen: boolean; account?: Account }>({
    isOpen: false,
    account: undefined,
  });

  const handleDeposit = (account: Account) => {
    setDepositForm({ isOpen: true, account });
  };

  const handleWithdrawal = (account: Account) => {
    setWithdrawalForm({ isOpen: true, account });
  };

  if (loading) {
    return (
      <div className="space-y-4">
        {[1, 2, 3].map((i) => (
          <Card key={i}>
            <CardContent className="p-6">
              <div className="space-y-3">
                <Skeleton className="h-4 w-48" />
                <Skeleton className="h-6 w-32" />
                <Skeleton className="h-4 w-24" />
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    );
  }

  if (accounts.length === 0) {
    return (
      <Card>
        <CardContent className="flex flex-col items-center justify-center py-12">
          <Wallet className="w-12 h-12 text-muted-foreground mb-4" />
          <h3 className="text-lg font-medium mb-2">No Savings Accounts</h3>
          <p className="text-muted-foreground text-center mb-4">
            You don't have any savings accounts yet. Open your first account to start saving.
          </p>
          <Button>Open Savings Account</Button>
        </CardContent>
      </Card>
    );
  }

  return (
    <>
      <div className="grid gap-4 md:grid-cols-2">
        {accounts.map((accountWrapper) => {
          const account = getSavingsAccount(accountWrapper);
          if (!account) return null; // Skip if not a savings account

          return (
            <Card key={accountWrapper.id} className="hover:shadow-md transition-shadow">
              <CardHeader className="pb-3">
                <div className="flex justify-between items-start">
                  <div>
                    <CardTitle className="text-lg">{account.savings_product?.name || 'Savings Account'}</CardTitle>
                    <p className="text-sm text-muted-foreground font-mono">
                      {accountWrapper.account_number}
                    </p>
                  </div>
                  <Badge 
                    variant={accountWrapper.status === 'active' ? 'default' : 'secondary'}
                  >
                    {accountWrapper.status}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <p className="text-sm text-muted-foreground">Current Balance</p>
                  <p className="text-2xl font-bold text-primary">
                    UGX {account.balance?.toLocaleString() || '0'}
                  </p>
                  {account.available_balance !== account.balance && (
                    <p className="text-sm text-muted-foreground">
                      Available: UGX {account.available_balance?.toLocaleString() || '0'}
                    </p>
                  )}
                </div>

                <div className="flex justify-between text-sm">
                  <div>
                    <p className="text-muted-foreground">Interest Rate</p>
                    <p className="font-medium flex items-center gap-1">
                      <TrendingUp className="w-3 h-3 text-success" />
                      {account.interest_rate || 0}% p.a.
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="text-muted-foreground">Opened</p>
                    <p className="font-medium">
                      {new Date(account.created_at).toLocaleDateString()}
                    </p>
                  </div>
                </div>

                <div className="flex gap-2">
                  <Button 
                    variant="outline" 
                    size="sm" 
                    onClick={() => onAccountSelect(accountWrapper.id)}
                  >
                    <Eye className="w-4 h-4 mr-1" />
                    Transactions
                  </Button>
                  <Button 
                    size="sm" 
                    variant="default"
                    onClick={() => handleDeposit(accountWrapper)}
                    disabled={accountWrapper.status !== 'active'}
                  >
                    <ArrowUpRight className="w-4 h-4 mr-1" />
                    Deposit
                  </Button>
                  <Button 
                    size="sm" 
                    variant="outline"
                    onClick={() => handleWithdrawal(accountWrapper)}
                    disabled={accountWrapper.status !== 'active' || (account.available_balance || 0) <= (account.minimum_balance || 0)}
                  >
                    <ArrowDownRight className="w-4 h-4 mr-1" />
                    Withdraw
                  </Button>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>

      <DepositForm 
        isOpen={depositForm.isOpen}
        onClose={() => setDepositForm({ isOpen: false, account: undefined })}
        account={depositForm.account}
      />

      <WithdrawalForm 
        isOpen={withdrawalForm.isOpen}
        onClose={() => setWithdrawalForm({ isOpen: false, account: undefined })}
        account={withdrawalForm.account}
      />
    </>
  );
}
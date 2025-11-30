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
  onAccountSelect: (account: Account) => void;
}

export function AccountsList({ accounts, loading, onAccountSelect }: AccountsListProps) {
  const [selectedAccountId, setSelectedAccountId] = useState<number | null>(null);
  const [depositForm, setDepositForm] = useState<{ isOpen: boolean; account?: Account }>({
    isOpen: false,
    account: undefined,
  });
  const [withdrawalForm, setWithdrawalForm] = useState<{ isOpen: boolean; account?: Account }>({
    isOpen: false,
    account: undefined,
  });

  const handleDeposit = (account: Account) => {
    setSelectedAccountId(account.id);
    setDepositForm({ isOpen: true, account });
  };

  const handleWithdrawal = (account: Account) => {
    setSelectedAccountId(account.id);
    setWithdrawalForm({ isOpen: true, account });
  };

  const handleSelectAccount = (account: Account) => {
    setSelectedAccountId(account.id);
    onAccountSelect(account);
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
      <div className="grid gap-4 grid-cols-1">
        {accounts.map((accountWrapper) => {
          const account = getSavingsAccount(accountWrapper);
          if (!account) return null;
          
          const isSelected = selectedAccountId === accountWrapper.id;

          return (
            <Card 
              key={accountWrapper.id} 
              className={`transition-all duration-300 cursor-pointer ${
                isSelected 
                  ? 'border-2 border-primary shadow-lg shadow-primary/20 bg-primary/5' 
                  : 'hover:shadow-md hover:border-primary/30'
              }`}
              onClick={() => handleSelectAccount(accountWrapper)}
            >
              <CardHeader className="pb-3 px-4 md:px-6 py-4">
                <div className="flex justify-between items-start gap-3">
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2">
                      <CardTitle className="text-base md:text-lg truncate">{account.savings_product?.name || 'Savings Account'}</CardTitle>
                      {isSelected && (
                        <div className="flex-shrink-0 w-2 h-2 rounded-full bg-primary animate-pulse" />
                      )}
                    </div>
                    <p className="text-xs md:text-sm text-muted-foreground font-mono truncate">
                      {accountWrapper.account_number}
                    </p>
                  </div>
                  <Badge 
                    variant={accountWrapper.status === 'active' ? 'default' : 'secondary'}
                    className="flex-shrink-0 text-xs"
                  >
                    {accountWrapper.status}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-4 px-4 md:px-6 pb-4">
                <div>
                  <p className="text-xs md:text-sm text-muted-foreground">Current Balance</p>
                  <p className="text-xl md:text-2xl font-bold text-primary truncate">
                    UGX {account.balance?.toLocaleString() || '0'}
                  </p>
                  {account.available_balance !== account.balance && (
                    <p className="text-xs md:text-sm text-muted-foreground truncate">
                      Available: UGX {account.available_balance?.toLocaleString() || '0'}
                    </p>
                  )}
                </div>

                <div className="grid grid-cols-2 gap-3 text-xs md:text-sm">
                  <div>
                    <p className="text-muted-foreground">Interest Rate</p>
                    <p className="font-medium flex items-center gap-1">
                      <TrendingUp className="w-3 h-3 text-success flex-shrink-0" />
                      <span className="truncate">{account.savings_product?.interest_rate || 0}% p.a.</span>
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="text-muted-foreground">Opened</p>
                    <p className="font-medium truncate">
                      {new Date(account.created_at).toLocaleDateString()}
                    </p>
                  </div>
                </div>

                <div className="flex flex-wrap gap-2">
                  <Button 
                    variant="outline" 
                    size="sm" 
                    onClick={(e) => {
                      e.stopPropagation();
                      handleSelectAccount(accountWrapper);
                    }}
                    className="flex-1 min-w-[100px] text-xs md:text-sm"
                  >
                    <Eye className="w-3 h-3 md:w-4 md:h-4 mr-1 flex-shrink-0" />
                    <span className="truncate">Transactions</span>
                  </Button>
                  <Button 
                    size="sm" 
                    variant="default"
                    onClick={(e) => {
                      e.stopPropagation();
                      handleDeposit(accountWrapper);
                    }}
                    disabled={accountWrapper.status !== 'active'}
                    className="flex-1 min-w-[90px] text-xs md:text-sm"
                  >
                    <ArrowUpRight className="w-3 h-3 md:w-4 md:h-4 mr-1 flex-shrink-0" />
                    <span className="truncate">Deposit</span>
                  </Button>
                  <Button 
                    size="sm" 
                    variant="outline"
                    onClick={(e) => {
                      e.stopPropagation();
                      handleWithdrawal(accountWrapper);
                    }}
                    disabled={accountWrapper.status !== 'active' || (account.available_balance || 0) <= (account.minimum_balance || 0)}
                    className="flex-1 min-w-[90px] text-xs md:text-sm"
                  >
                    <ArrowDownRight className="w-3 h-3 md:w-4 md:h-4 mr-1 flex-shrink-0" />
                    <span className="truncate">Withdraw</span>
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

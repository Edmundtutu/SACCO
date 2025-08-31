import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Wallet, TrendingUp, Eye } from 'lucide-react';

interface SavingsAccount {
  id: number;
  account_number: string;
  product_name: string;
  balance: number;
  interest_rate: number;
  status: 'active' | 'dormant' | 'closed';
  created_at: string;
}

interface AccountsListProps {
  accounts: SavingsAccount[];
  loading: boolean;
  onAccountSelect: (accountId: number) => void;
}

export function AccountsList({ accounts, loading, onAccountSelect }: AccountsListProps) {
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
    <div className="grid gap-4 md:grid-cols-2">
      {accounts.map((account) => (
        <Card key={account.id} className="hover:shadow-md transition-shadow">
          <CardHeader className="pb-3">
            <div className="flex justify-between items-start">
              <div>
                <CardTitle className="text-lg">{account.product_name}</CardTitle>
                <p className="text-sm text-muted-foreground font-mono">
                  {account.account_number}
                </p>
              </div>
              <Badge 
                variant={account.status === 'active' ? 'default' : 'secondary'}
              >
                {account.status}
              </Badge>
            </div>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <p className="text-sm text-muted-foreground">Current Balance</p>
              <p className="text-2xl font-bold text-primary">
                KES {account.balance.toLocaleString()}
              </p>
            </div>

            <div className="flex justify-between text-sm">
              <div>
                <p className="text-muted-foreground">Interest Rate</p>
                <p className="font-medium flex items-center gap-1">
                  <TrendingUp className="w-3 h-3 text-success" />
                  {account.interest_rate}% p.a.
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
                className="flex-1"
                onClick={() => onAccountSelect(account.id)}
              >
                <Eye className="w-4 h-4 mr-2" />
                View Transactions
              </Button>
              <Button size="sm" className="flex-1">
                Manage Account
              </Button>
            </div>
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
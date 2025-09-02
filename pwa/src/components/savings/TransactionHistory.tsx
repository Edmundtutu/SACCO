import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { ArrowUpRight, ArrowDownRight, TrendingUp, CreditCard } from 'lucide-react';

interface Transaction {
  id: number;
  type: 'deposit' | 'withdrawal' | 'interest' | 'fee';
  amount: number;
  balance_after: number;
  description: string;
  created_at: string;
}

interface TransactionHistoryProps {
  transactions: Transaction[];
  loading: boolean;
  accountId: number;
}

export function TransactionHistory({ transactions, loading, accountId }: TransactionHistoryProps) {
  const getTransactionIcon = (type: string) => {
    switch (type) {
      case 'deposit':
        return <ArrowDownRight className="w-4 h-4 text-success" />;
      case 'withdrawal':
        return <ArrowUpRight className="w-4 h-4 text-destructive" />;
      case 'interest':
        return <TrendingUp className="w-4 h-4 text-primary" />;
      case 'fee':
        return <CreditCard className="w-4 h-4 text-muted-foreground" />;
      default:
        return <CreditCard className="w-4 h-4 text-muted-foreground" />;
    }
  };

  const getTransactionColor = (type: string) => {
    switch (type) {
      case 'deposit':
      case 'interest':
        return 'text-success';
      case 'withdrawal':
      case 'fee':
        return 'text-destructive';
      default:
        return 'text-foreground';
    }
  };

  const getAmountPrefix = (type: string) => {
    return type === 'deposit' || type === 'interest' ? '+' : '-';
  };

  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Transaction History</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {[1, 2, 3, 4, 5].map((i) => (
            <div key={i} className="flex items-center space-x-4">
              <Skeleton className="w-8 h-8 rounded-full" />
              <div className="flex-1 space-y-2">
                <Skeleton className="h-4 w-48" />
                <Skeleton className="h-3 w-32" />
              </div>
              <Skeleton className="h-4 w-24" />
            </div>
          ))}
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Transaction History</CardTitle>
        <p className="text-sm text-muted-foreground">
          Recent transactions for account #{accountId}
        </p>
      </CardHeader>
      <CardContent>
        {transactions.length === 0 ? (
          <div className="text-center py-8">
            <CreditCard className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
            <p className="text-muted-foreground">No transactions found</p>
          </div>
        ) : (
          <div className="space-y-4">
            {transactions.map((transaction) => (
              <div 
                key={transaction.id}
                className="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors"
              >
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-muted rounded-full">
                    {getTransactionIcon(transaction.type)}
                  </div>
                  <div>
                    <p className="font-medium capitalize">{transaction.type}</p>
                    <p className="text-sm text-muted-foreground">
                      {transaction.description}
                    </p>
                    <p className="text-xs text-muted-foreground">
                      {new Date(transaction.created_at).toLocaleString()}
                    </p>
                  </div>
                </div>

                <div className="text-right">
                  <p className={`font-bold ${getTransactionColor(transaction.type)}`}>
                    {getAmountPrefix(transaction.type)}UGX {transaction.amount.toLocaleString()}
                  </p>
                  <p className="text-sm text-muted-foreground">
                    Bal: UGX {transaction.balance_after.toLocaleString()}
                  </p>
                </div>
              </div>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
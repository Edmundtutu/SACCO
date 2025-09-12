import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { fetchSavingsAccounts, fetchSavingsProducts, fetchTransactions } from '@/store/savingsSlice';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Target, TrendingUp, ArrowUpRight, ArrowDownRight } from 'lucide-react';
import { SavingsProgress } from '@/components/savings/SavingsProgress';
import { AccountsList } from '@/components/savings/AccountsList';
import { TransactionHistory } from '@/components/savings/TransactionHistory';

export default function Savings() {
  const dispatch = useDispatch<AppDispatch>();
  const { accounts, products, transactions, loading } = useSelector((state: RootState) => state.savings);
  const [selectedAccountId, setSelectedAccountId] = useState<number | null>(null);

  useEffect(() => {
    dispatch(fetchSavingsAccounts());
    dispatch(fetchSavingsProducts());
  }, [dispatch]);

  useEffect(() => {
    if (selectedAccountId) {
      dispatch(fetchTransactions(selectedAccountId));
    }
  }, [dispatch, selectedAccountId]);

  const totalBalance = accounts.reduce((sum, account) => sum + account.balance, 0);
  const savingsTarget = 50000; // Monthly target - could come from user settings
  const progressPercentage = Math.min((totalBalance / savingsTarget) * 100, 100);

  return (
    <div className="p-4 space-y-6 max-w-6xl mx-auto">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-heading font-bold text-foreground">Savings</h1>
          <p className="text-muted-foreground">Manage your savings accounts and track progress</p>
        </div>
      </div>

      {/* Gamified Progress Tracker */}
      <SavingsProgress 
        currentAmount={totalBalance}
        targetAmount={savingsTarget}
        progressPercentage={progressPercentage}
      />

      <Tabs defaultValue="accounts" className="space-y-6">
        <TabsList className="grid w-full grid-cols-3">
          <TabsTrigger value="accounts">My Accounts</TabsTrigger>
          <TabsTrigger value="transactions">Transactions</TabsTrigger>
          <TabsTrigger value="products">Products</TabsTrigger>
        </TabsList>

        <TabsContent value="accounts">
          <AccountsList 
            accounts={accounts}
            loading={loading}
            onAccountSelect={setSelectedAccountId}
          />
        </TabsContent>

        <TabsContent value="transactions">
          {selectedAccountId ? (
            <TransactionHistory 
              transactions={transactions}
              loading={loading}
              accountId={selectedAccountId}
            />
          ) : (
            <Card>
              <CardContent className="flex items-center justify-center h-32">
                <p className="text-muted-foreground">Select an account to view transactions</p>
              </CardContent>
            </Card>
          )}
        </TabsContent>

        <TabsContent value="products">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {products.map((product) => (
              <Card key={product.id} className="hover:shadow-md transition-shadow">
                <CardHeader>
                  <CardTitle className="text-lg">{product.name}</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <p className="text-sm text-muted-foreground">{product.description}</p>
                  
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span>Interest Rate:</span>
                      <span className="font-medium text-success">{product.interest_rate}%</span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span>Min. Balance:</span>
                      <span className="font-medium">UGX {product.minimum_balance.toLocaleString()}</span>
                    </div>
                  </div>

                  <div className="space-y-2">
                    <h4 className="text-sm font-medium">Features:</h4>
                    <div className="flex flex-wrap gap-1">
                      {product?.features?.map((feature, index) => (
                        <Badge key={index} variant="secondary" className="text-xs">
                          {feature}
                        </Badge>
                      ))}
                    </div>
                  </div>

                  <Button className="w-full" variant="outline">
                    Open Account
                  </Button>
                </CardContent>
              </Card>
            ))}
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}
import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { StatementViewer } from '@/components/reports/StatementViewer';
import { RootState } from '@/store';
import { fetchSavingsAccounts } from '@/store/savingsSlice';
import { fetchLoans } from '@/store/loansSlice';
import { fetchShares } from '@/store/sharesSlice';
import { FileText, TrendingUp, PieChart, CreditCard } from 'lucide-react';

export default function Reports() {
  const dispatch = useDispatch();
  const { accounts } = useSelector((state: RootState) => state.savings);
  const { loans } = useSelector((state: RootState) => state.loans);
  const { account: sharesAccount } = useSelector((state: RootState) => state.shares);

  useEffect(() => {
    dispatch(fetchSavingsAccounts() as any);
    dispatch(fetchLoans() as any);
    dispatch(fetchShares() as any);
  }, [dispatch]);

  const totalSavings = accounts.reduce((sum, account) => sum + account.balance, 0);
  const totalLoans = loans.reduce((sum, loan) => sum + loan.outstanding_balance, 0);
  const totalShares = sharesAccount?.total_value || 0;
  const interestEarned = accounts.reduce((sum, account) => sum + account.interest_earned, 0);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  return (
    <div className="p-4 space-y-6 max-w-6xl mx-auto">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-heading font-bold text-foreground">Reports & Statements</h1>
          <p className="text-muted-foreground">View your financial reports and download statements</p>
        </div>
      </div>

      {/* Financial Summary */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Total Savings</p>
                <p className="text-2xl font-bold text-green-600">{formatCurrency(totalSavings)}</p>
              </div>
              <div className="h-12 w-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                <TrendingUp className="h-6 w-6 text-green-600" />
              </div>
            </div>
            <p className="text-xs text-muted-foreground mt-2">
              Interest earned: {formatCurrency(interestEarned)}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Active Loans</p>
                <p className="text-2xl font-bold text-orange-600">{formatCurrency(totalLoans)}</p>
              </div>
              <div className="h-12 w-12 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                <CreditCard className="h-6 w-6 text-orange-600" />
              </div>
            </div>
            <p className="text-xs text-muted-foreground mt-2">
              {loans.filter(loan => loan.status === 'active').length} active loan(s)
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Share Capital</p>
                <p className="text-2xl font-bold text-blue-600">{formatCurrency(totalShares)}</p>
              </div>
              <div className="h-12 w-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                <PieChart className="h-6 w-6 text-blue-600" />
              </div>
            </div>
            <p className="text-xs text-muted-foreground mt-2">
              {sharesAccount?.total_shares || 0} shares owned
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Net Worth</p>
                <p className="text-2xl font-bold text-primary">{formatCurrency(totalSavings + totalShares - totalLoans)}</p>
              </div>
              <div className="h-12 w-12 bg-primary/10 rounded-lg flex items-center justify-center">
                <TrendingUp className="h-6 w-6 text-primary" />
              </div>
            </div>
            <p className="text-xs text-muted-foreground mt-2">
              Assets minus liabilities
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Reports Tabs */}
      <Tabs defaultValue="statement" className="space-y-6">
        <TabsList className="grid w-full grid-cols-3">
          <TabsTrigger value="statement">Account Statement</TabsTrigger>
          <TabsTrigger value="savings">Savings Summary</TabsTrigger>
          <TabsTrigger value="loans">Loans Summary</TabsTrigger>
        </TabsList>

        <TabsContent value="statement">
          <StatementViewer />
        </TabsContent>

        <TabsContent value="savings">
          <Card>
            <CardHeader>
              <CardTitle>Savings Summary</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {accounts.map((account) => (
                  <div key={account.id} className="flex justify-between items-center p-4 bg-muted/50 rounded-lg">
                    <div>
                      <p className="font-medium">{account.savings_product.name}</p>
                      <p className="text-sm text-muted-foreground">{account.account_number}</p>
                    </div>
                    <div className="text-right">
                      <p className="font-bold">{formatCurrency(account.balance)}</p>
                      <p className="text-sm text-muted-foreground">
                        Interest: {formatCurrency(account.interest_earned)}
                      </p>
                    </div>
                  </div>
                ))}
                
                {accounts.length === 0 && (
                  <p className="text-center text-muted-foreground py-8">
                    No savings accounts found
                  </p>
                )}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="loans">
          <Card>
            <CardHeader>
              <CardTitle>Loans Summary</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {loans.map((loan) => (
                  <div key={loan.id} className="flex justify-between items-center p-4 bg-muted/50 rounded-lg">
                    <div>
                      <p className="font-medium">{loan.product_name}</p>
                      <p className="text-sm text-muted-foreground">{loan.loan_number}</p>
                      <p className="text-xs text-muted-foreground">
                        Next payment: {new Date(loan.next_payment_date).toLocaleDateString()}
                      </p>
                    </div>
                    <div className="text-right">
                      <p className="font-bold text-orange-600">{formatCurrency(loan.outstanding_balance)}</p>
                      <p className="text-sm text-muted-foreground">
                        Monthly: {formatCurrency(loan.monthly_payment)}
                      </p>
                    </div>
                  </div>
                ))}
                
                {loans.length === 0 && (
                  <p className="text-center text-muted-foreground py-8">
                    No loans found
                  </p>
                )}
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
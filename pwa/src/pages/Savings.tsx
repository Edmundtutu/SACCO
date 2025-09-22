import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { fetchSavingsAccounts } from '@/store/savingsSlice';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { DepositForm } from '@/components/savings/DepositForm';
import { WithdrawalForm } from '@/components/savings/WithdrawalForm';
import { AccountsList } from '@/components/savings/AccountsList';
import { TransactionHistory } from '@/components/transactions/TransactionHistory';
import { SavingsProgress } from '@/components/savings/SavingsProgress';
import { 
  Plus, 
  Minus, 
  TrendingUp, 
  CreditCard, 
  Target,
  History,
  PiggyBank
} from 'lucide-react';

export default function Savings() {
  const dispatch = useDispatch<AppDispatch>();
  const { user } = useSelector((state: RootState) => (state.auth as any));
  const { accounts, loading } = useSelector((state: RootState) => state.savings);
  
  const totalSavings = accounts.reduce((sum, account) => sum + account.balance, 0);
  
  const [depositModalOpen, setDepositModalOpen] = useState(false);
  const [withdrawalModalOpen, setWithdrawalModalOpen] = useState(false);
  const [selectedAccount, setSelectedAccount] = useState<any>(null);

  useEffect(() => {
    dispatch(fetchSavingsAccounts());
  }, [dispatch]);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const totalBalance = accounts.reduce((sum, account) => sum + account.balance, 0);
  const totalAvailableBalance = accounts.reduce((sum, account) => sum + account.available_balance, 0);

  return (
    <div className="p-4 md:p-6 space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="font-heading text-2xl md:text-3xl font-bold">Savings</h1>
          <p className="text-muted-foreground">Manage your savings accounts and transactions</p>
        </div>
        <div className="flex gap-2">
          <Button 
            onClick={() => setDepositModalOpen(true)}
            className="bg-green-600 hover:bg-green-700"
          >
            <Plus className="w-4 h-4 mr-2" />
            Deposit
          </Button>
          <Button 
            variant="outline"
            onClick={() => setWithdrawalModalOpen(true)}
          >
            <Minus className="w-4 h-4 mr-2" />
            Withdraw
          </Button>
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Total Balance</p>
                <p className="text-2xl font-bold font-heading">{formatCurrency(totalBalance)}</p>
              </div>
              <div className="h-12 w-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                <PiggyBank className="h-6 w-6 text-green-600" />
              </div>
            </div>
            <div className="mt-4 flex items-center gap-2">
              <Badge variant="secondary" className="bg-green-100 text-green-700 dark:bg-green-900/20">
                {accounts.length} account{accounts.length !== 1 ? 's' : ''}
              </Badge>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Available Balance</p>
                <p className="text-2xl font-bold font-heading">{formatCurrency(totalAvailableBalance)}</p>
              </div>
              <div className="h-12 w-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                <CreditCard className="h-6 w-6 text-blue-600" />
              </div>
            </div>
            <div className="mt-4">
              <p className="text-xs text-muted-foreground">
                Ready for withdrawal
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Interest Earned</p>
                <p className="text-2xl font-bold font-heading">
                  {formatCurrency(accounts.reduce((sum, account) => sum + (account.interest_earned || 0), 0))}
                </p>
              </div>
              <div className="h-12 w-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                <TrendingUp className="h-6 w-6 text-purple-600" />
              </div>
            </div>
            <div className="mt-4">
              <p className="text-xs text-muted-foreground">
                This year
              </p>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Main Content */}
      <Tabs defaultValue="accounts" className="space-y-4">
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="accounts" className="flex items-center gap-2">
            <CreditCard className="w-4 h-4" />
            Accounts
          </TabsTrigger>
          <TabsTrigger value="transactions" className="flex items-center gap-2">
            <History className="w-4 h-4" />
            Transactions
          </TabsTrigger>
          <TabsTrigger value="progress" className="flex items-center gap-2">
            <Target className="w-4 h-4" />
            Progress
          </TabsTrigger>
          <TabsTrigger value="products" className="flex items-center gap-2">
            <TrendingUp className="w-4 h-4" />
            Products
          </TabsTrigger>
        </TabsList>

        <TabsContent value="accounts" className="space-y-4">
          <AccountsList 
            accounts={accounts}
            loading={loading}
            onAccountSelect={setSelectedAccount}
          />
        </TabsContent>

        <TabsContent value="transactions" className="space-y-4">
          <TransactionHistory memberId={user?.id || 0} />
        </TabsContent>

        <TabsContent value="progress" className="space-y-4">
          <SavingsProgress 
            currentAmount={totalSavings}
            targetAmount={100000}
            progressPercentage={Math.min((totalSavings / 100000) * 100, 100)}
          />
        </TabsContent>

        <TabsContent value="products" className="space-y-4">
          <Card>
                <CardHeader>
              <CardTitle>Available Savings Products</CardTitle>
                </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {[
                  {
                    name: "Regular Savings",
                    description: "Standard savings account with competitive interest rates",
                    interestRate: "8%",
                    minimumDeposit: 10000,
                    features: ["Monthly interest", "Easy withdrawals", "No fees"]
                  },
                  {
                    name: "Fixed Deposit",
                    description: "Higher interest rates for fixed-term deposits",
                    interestRate: "12%",
                    minimumDeposit: 100000,
                    features: ["Higher interest", "Fixed term", "Guaranteed returns"]
                  },
                  {
                    name: "Youth Savings",
                    description: "Special account for members under 25 years",
                    interestRate: "10%",
                    minimumDeposit: 5000,
                    features: ["Higher interest", "Lower minimum", "Youth benefits"]
                  }
                ].map((product, index) => (
                  <Card key={index} className="hover:shadow-md transition-shadow">
                    <CardContent className="p-4">
                      <h3 className="font-semibold text-lg mb-2">{product.name}</h3>
                      <p className="text-sm text-muted-foreground mb-3">{product.description}</p>
                  <div className="space-y-2">
                        <div className="flex justify-between">
                          <span className="text-sm">Interest Rate:</span>
                          <span className="font-semibold text-green-600">{product.interestRate} p.a.</span>
                    </div>
                        <div className="flex justify-between">
                          <span className="text-sm">Min Deposit:</span>
                          <span className="font-semibold">{formatCurrency(product.minimumDeposit)}</span>
                    </div>
                  </div>
                      <div className="mt-3">
                        <h4 className="text-sm font-medium mb-2">Features:</h4>
                        <ul className="text-xs text-muted-foreground space-y-1">
                          {product.features.map((feature, idx) => (
                            <li key={idx} className="flex items-center gap-1">
                              <div className="w-1 h-1 bg-primary rounded-full"></div>
                          {feature}
                            </li>
                      ))}
                        </ul>
                    </div>
                      <Button className="w-full mt-4" size="sm">
                        Learn More
                  </Button>
                </CardContent>
              </Card>
            ))}
          </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {/* Modals */}
      <DepositForm 
        isOpen={depositModalOpen}
        onClose={() => setDepositModalOpen(false)}
        account={selectedAccount}
      />
      
      <WithdrawalForm 
        isOpen={withdrawalModalOpen}
        onClose={() => setWithdrawalModalOpen(false)}
        account={selectedAccount}
      />
    </div>
  );
}
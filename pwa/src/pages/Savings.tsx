import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { fetchSavingsAccounts } from '@/store/savingsSlice';
import type { Account } from '@/types/api';
import { 
  getTotalSavingsBalance, 
  getTotalAvailableBalance, 
  getTotalInterestEarned
} from '@/utils/accountHelpers';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { DepositForm } from '@/components/savings/DepositForm';
import { WithdrawalForm } from '@/components/savings/WithdrawalForm';
import { AccountsList } from '@/components/savings/AccountsList';
import { TransactionHistory } from '@/components/transactions/TransactionHistory';
import { SavingsProgress } from '@/components/savings/SavingsProgress';
import { SavingsGoalManager } from '@/components/savings/SavingsGoalManager';
import { DashboardPage } from '@/components/layout/DashboardPage';
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
  const { user } = useSelector((state: RootState) => state.auth);
  const { accounts = [], loading } = useSelector((state: RootState) => state.savings);
  
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  // Use helper functions to calculate totals from polymorphic accounts
  const totalBalance = getTotalSavingsBalance(accounts);
  const totalAvailableBalance = getTotalAvailableBalance(accounts);
  const totalInterestEarned = getTotalInterestEarned(accounts);

  const [depositModalOpen, setDepositModalOpen] = useState(false);
  const [withdrawalModalOpen, setWithdrawalModalOpen] = useState(false);
  const [selectedAccount, setSelectedAccount] = useState<Account | null>(null);

  useEffect(() => {
    dispatch(fetchSavingsAccounts());
  }, [dispatch]);

  const toolbarActions = (
    <>
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
    </>
  );

  const mobileActions = (
    <>
      <button
        onClick={() => setDepositModalOpen(true)}
        className="group relative overflow-hidden rounded-2xl sm:rounded-3xl p-4 sm:p-6 
          bg-gradient-to-br from-emerald-50 to-green-50 border border-white/20 backdrop-blur-sm
          transform transition-all duration-300 ease-out
          hover:scale-105 hover:-translate-y-1 hover:shadow-green-500/40
          active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed
          shadow-green-500/25 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500
          flex-1"
      >
        {/* Background Gradient Overlay */}
        <div className="absolute inset-0 bg-gradient-to-br from-emerald-400 to-green-600 opacity-0 
          group-hover:opacity-5 transition-opacity duration-300" />
        
        {/* Content */}
        <div className="relative z-10 flex items-center justify-center gap-2">
          <Plus className="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 transform transition-transform duration-300 
            group-hover:scale-110 group-active:scale-95" />
          <span className="font-semibold text-sm sm:text-base text-emerald-600 
            group-hover:font-bold transition-all duration-200">
            Deposit
          </span>
        </div>

        {/* Hover Effect Border */}
        <div className="absolute inset-0 rounded-2xl sm:rounded-3xl border-2 border-transparent 
          bg-gradient-to-br from-emerald-400 to-green-600 opacity-0 group-hover:opacity-20 
          transition-opacity duration-300 -z-10" />
      </button>

      <button
        onClick={() => setWithdrawalModalOpen(true)}
        className="group relative overflow-hidden rounded-2xl sm:rounded-3xl p-4 sm:p-6 
          bg-gradient-to-br from-red-50 to-rose-50 border border-white/20 backdrop-blur-sm
          transform transition-all duration-300 ease-out
          hover:scale-105 hover:-translate-y-1 hover:shadow-red-500/40
          active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed
          shadow-red-500/25 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500
          flex-1"
      >
        {/* Background Gradient Overlay */}
        <div className="absolute inset-0 bg-gradient-to-br from-red-400 to-rose-600 opacity-0 
          group-hover:opacity-5 transition-opacity duration-300" />
        
        {/* Content */}
        <div className="relative z-10 flex items-center justify-center gap-2">
          <Minus className="w-4 h-4 sm:w-5 sm:h-5 text-red-600 transform transition-transform duration-300 
            group-hover:scale-110 group-active:scale-95" />
          <span className="font-semibold text-sm sm:text-base text-red-600 
            group-hover:font-bold transition-all duration-200">
            Withdraw
          </span>
        </div>

        {/* Hover Effect Border */}
        <div className="absolute inset-0 rounded-2xl sm:rounded-3xl border-2 border-transparent 
          bg-gradient-to-br from-red-400 to-rose-600 opacity-0 group-hover:opacity-20 
          transition-opacity duration-300 -z-10" />
      </button>
    </>
  );

  return (
    <DashboardPage 
      title="Savings" 
      subtitle="Manage your savings accounts and transactions"
      toolbarActions={toolbarActions}
    >
        {/* Mobile Hero Section with Glass Card */}
        <div className="md:hidden relative -mx-4 bg-gradient-to-br from-primary via-primary to-secondary px-4 pt-6 pb-8 mb-3">
          {/* Decorative circles */}
          <div className="absolute top-0 right-0 w-64 h-64 bg-background/10 rounded-full blur-3xl"></div>
          <div className="absolute bottom-0 left-0 w-48 h-48 bg-secondary/20 rounded-full blur-2xl"></div>
          
          <div className="relative z-10">
            {/* Main Balance Card - Mobile Optimized */}
            <div className="bg-background/95 backdrop-blur-lg rounded-3xl p-6 shadow-2xl border border-border/20">
              <div className="flex items-start justify-between mb-4">
                <div>
                  <p className="text-sm text-muted-foreground mb-1">Total Balance</p>
                  <h2 className="text-3xl font-bold text-foreground">
                    {formatCurrency(totalBalance)}
                  </h2>
                </div>
                <div className="bg-primary p-3 rounded-2xl shadow-lg">
                  <PiggyBank className="w-6 h-6 text-primary-foreground" />
                </div>
              </div>
              
              {/* Mini Stats Row */}
              <div className="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-border">
                <div className="bg-muted rounded-2xl p-3">
                  <div className="flex items-center gap-2 mb-1">
                    <div className="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                      <CreditCard className="w-4 h-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <span className="text-xs text-muted-foreground font-medium">Available</span>
                  </div>
                  <p className="text-lg font-bold text-foreground truncate">{formatCurrency(totalAvailableBalance)}</p>
                </div>
                
                <div className="bg-muted rounded-2xl p-3">
                  <div className="flex items-center gap-2 mb-1">
                    <div className="w-8 h-8 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                      <TrendingUp className="w-4 h-4 text-green-600 dark:text-green-400" />
                    </div>
                    <span className="text-xs text-muted-foreground font-medium">Interest</span>
                  </div>
                  <p className="text-lg font-bold text-foreground truncate">{formatCurrency(totalInterestEarned)}</p>
                </div>
              </div>
              
              {/* Accounts Badge */}
              <div className="mt-3 text-center">
                <Badge variant="secondary" className="bg-muted text-muted-foreground">
                  {accounts.length} account{accounts.length !== 1 ? 's' : ''}
                </Badge>
              </div>
            </div>

            {/* Mobile Action Buttons */}
            <div className="flex gap-3 mt-4">
              <button
                onClick={() => setDepositModalOpen(true)}
                className="group relative overflow-hidden rounded-2xl sm:rounded-3xl p-4 sm:p-6 
                  bg-gradient-to-br from-emerald-50 to-green-50 border border-white/20 backdrop-blur-sm
                  transform transition-all duration-300 ease-out
                  hover:scale-105 hover:-translate-y-1 hover:shadow-green-500/40
                  active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed
                  shadow-green-500/25 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500
                  flex-1"
              >
                {/* Background Gradient Overlay */}
                <div className="absolute inset-0 bg-gradient-to-br from-emerald-400 to-green-600 opacity-0 
                  group-hover:opacity-5 transition-opacity duration-300" />
                
                {/* Content */}
                <div className="relative z-10 flex items-center justify-center gap-2">
                  <Plus className="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 transform transition-transform duration-300 
                    group-hover:scale-110 group-active:scale-95" />
                  <span className="font-semibold text-sm sm:text-base text-emerald-600 
                    group-hover:font-bold transition-all duration-200">
                    Deposit
                  </span>
                </div>

                {/* Hover Effect Border */}
                <div className="absolute inset-0 rounded-2xl sm:rounded-3xl border-2 border-transparent 
                  bg-gradient-to-br from-emerald-400 to-green-600 opacity-0 group-hover:opacity-20 
                  transition-opacity duration-300 -z-10" />
              </button>

              <button
                onClick={() => setWithdrawalModalOpen(true)}
                className="group relative overflow-hidden rounded-2xl sm:rounded-3xl p-4 sm:p-6 
                  bg-gradient-to-br from-red-50 to-rose-50 border border-white/20 backdrop-blur-sm
                  transform transition-all duration-300 ease-out
                  hover:scale-105 hover:-translate-y-1 hover:shadow-red-500/40
                  active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed
                  shadow-red-500/25 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500
                  flex-1"
              >
                {/* Background Gradient Overlay */}
                <div className="absolute inset-0 bg-gradient-to-br from-red-400 to-rose-600 opacity-0 
                  group-hover:opacity-5 transition-opacity duration-300" />
                
                {/* Content */}
                <div className="relative z-10 flex items-center justify-center gap-2">
                  <Minus className="w-4 h-4 sm:w-5 sm:h-5 text-red-600 transform transition-transform duration-300 
                    group-hover:scale-110 group-active:scale-95" />
                  <span className="font-semibold text-sm sm:text-base text-red-600 
                    group-hover:font-bold transition-all duration-200">
                    Withdraw
                  </span>
                </div>

                {/* Hover Effect Border */}
                <div className="absolute inset-0 rounded-2xl sm:rounded-3xl border-2 border-transparent 
                  bg-gradient-to-br from-red-400 to-rose-600 opacity-0 group-hover:opacity-20 
                  transition-opacity duration-300 -z-10" />
              </button>
            </div>
          </div>
        </div>

        {/* Desktop Summary - Grid */}
        <div className="hidden md:grid grid-cols-3 gap-4">
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
                    {formatCurrency(totalInterestEarned)}
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
          <TabsList className="grid w-full grid-cols-4 h-12 md:h-10">
            <TabsTrigger value="accounts" className="flex items-center gap-1 md:gap-2 text-xs md:text-sm">
              <CreditCard className="w-3 h-3 md:w-4 md:h-4" />
              <span>Accounts</span>
            </TabsTrigger>
            <TabsTrigger value="transactions" className="flex items-center gap-1 md:gap-2 text-xs md:text-sm">
              <History className="w-3 h-3 md:w-4 md:h-4" />
              <span>History</span>
            </TabsTrigger>
            <TabsTrigger value="progress" className="flex items-center gap-1 md:gap-2 text-xs md:text-sm">
              <Target className="w-3 h-3 md:w-4 md:h-4" />
              <span>Progress</span>
            </TabsTrigger>
            <TabsTrigger value="products" className="flex items-center gap-1 md:gap-2 text-xs md:text-sm">
              <TrendingUp className="w-3 h-3 md:w-4 md:h-4" />
              <span>Products</span>
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
            <TransactionHistory memberId={user?.id || 0} context="savings" />
          </TabsContent>

          <TabsContent value="progress" className="space-y-4">
            <SavingsGoalManager memberId={user?.id || 0} />
          </TabsContent>

          <TabsContent value="products" className="space-y-4">
            <Card>
              <CardHeader className="px-4 md:px-6 py-4">
                <CardTitle className="text-lg md:text-xl">Available Savings Products</CardTitle>
              </CardHeader>
              <CardContent className="px-3 md:px-6 pb-4">
                <div className="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3 md:gap-4">
                  {[
                    {
                      name: "Regular Savings",
                      description: "Standard savings account with competitive interest rates",
                      interestRate: "8%",
                      minimumDeposit: 10000,
                      idealFor: "Everyday saving",
                      features: ["Monthly interest", "Easy withdrawals", "No fees"]
                    },
                    {
                      name: "Fixed Deposit",
                      description: "Higher interest rates for fixed-term deposits",
                      interestRate: "12%",
                      minimumDeposit: 100000,
                      idealFor: "Planned goals",
                      features: ["Higher interest", "Fixed term", "Guaranteed returns"]
                    },
                    {
                      name: "Youth Savings",
                      description: "Special account for members under 25 years",
                      interestRate: "10%",
                      minimumDeposit: 5000,
                      idealFor: "Students & youth",
                      features: ["Higher interest", "Lower minimum", "Youth benefits"]
                    }
                  ].map((product, index) => (
                    <Card 
                      key={index} 
                      className="rounded-3xl border-border overflow-hidden hover:shadow-md transition-all"
                    >
                      {/* Product Header */}
                      <div className="bg-primary p-4">
                        <h3 className="text-xl font-bold text-primary-foreground mb-1">{product.name}</h3>
                        <p className="text-primary-foreground/90 text-sm">{product.description}</p>
                      </div>

                      {/* Product Details */}
                      <CardContent className="p-4 space-y-3">
                        <div className="flex items-center justify-between py-2 border-b border-border">
                          <span className="text-sm text-muted-foreground">Interest Rate</span>
                          <span className="text-lg font-bold text-green-600 dark:text-green-400">{product.interestRate} p.a.</span>
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                          <div className="bg-muted rounded-xl p-3">
                            <p className="text-xs text-muted-foreground mb-1">Min Deposit</p>
                            <p className="text-sm font-bold text-foreground truncate">{formatCurrency(product.minimumDeposit)}</p>
                          </div>
                          <div className="bg-muted rounded-xl p-3">
                            <p className="text-xs text-muted-foreground mb-1">Benefits</p>
                            <p className="text-sm font-bold text-foreground">{product.features.length}+ perks</p>
                          </div>
                        </div>

                        <div className="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3 flex items-center justify-between">
                          <span className="text-sm text-muted-foreground">Ideal For</span>
                          <span className="text-sm font-bold text-blue-600 dark:text-blue-400">{product.idealFor}</span>
                        </div>

                        <div>
                          <h4 className="text-xs md:text-sm font-medium mb-2">Key Features</h4>
                          <ul className="text-xs text-muted-foreground space-y-1">
                            {product.features.map((feature, idx) => (
                              <li key={idx} className="flex items-center gap-1">
                                <div className="w-1 h-1 bg-primary rounded-full"></div>
                                {feature}
                              </li>
                            ))}
                          </ul>
                        </div>

                        <Button 
                          className="w-full mt-2 rounded-2xl" 
                          size="sm"
                        >
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
    </DashboardPage>
  );
}
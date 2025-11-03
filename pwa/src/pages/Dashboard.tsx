import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { fetchSavingsAccounts } from '@/store/savingsSlice';
import { fetchLoans } from '@/store/loansSlice';
import { fetchShares } from '@/store/sharesSlice';
import { fetchTransactionHistory } from '@/store/transactionsSlice';
import { fetchSavingsGoals, updateCurrentAmount } from '@/store/savingsGoalsSlice';
import { OverviewChart } from '@/components/dashboard/OverviewChart';
import { QuickActions } from '@/components/dashboard/QuickActions';
import { MobileToolbar } from '@/components/layout/MobileToolbar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { WalletCard } from '@/components/wallet/WalletCard';
import { WalletTopupForm } from '@/components/wallet/WalletTopupForm';
import { WalletWithdrawalForm } from '@/components/wallet/WalletWithdrawalForm';
import { ArrowUpRight, ArrowDownLeft, TrendingUp, Clock, PieChart, User } from 'lucide-react';

export function Dashboard() {
  const dispatch = useDispatch<AppDispatch>();
  const { user } = useSelector((state: RootState) => state.auth);
  const { accounts } = useSelector((state: RootState) => state.savings);
  const { loans } = useSelector((state: RootState) => state.loans);
  const { account: sharesAccount } = useSelector((state: RootState) => state.shares);
  const { transactions } = useSelector((state: RootState) => state.transactions);
  const { activeGoal, goals } = useSelector((state: RootState) => state.savingsGoals);
  const { balance: walletBalance } = useSelector((state: RootState) => state.wallet);

  // Add responsive state
  const [isMobile, setIsMobile] = useState(false);
  const [walletTopupOpen, setWalletTopupOpen] = useState(false);
  const [walletWithdrawOpen, setWalletWithdrawOpen] = useState(false);

  // Find wallet account
  const walletAccount = accounts.find(acc => acc.savings_product?.code === 'WL001'); // wallet product code

  useEffect(() => {
    dispatch(fetchSavingsAccounts());
    dispatch(fetchLoans());
    dispatch(fetchShares());
    // if wallet account is not found, log console error
    if (!walletAccount) {
      console.error('Wallet account not found');
    }
    // Fetch recent transactions for dashboard
    if (user?.id) {
      dispatch(fetchTransactionHistory({
        member_id: user.id,
        per_page: 5 // Only get the 5 most recent transactions
      }));
      dispatch(fetchSavingsGoals(user.id));
    }
  }, [dispatch, user?.id]);

  // Calculate derived values
  const totalSavings = accounts.reduce((sum, account) => sum + account.balance, 0);
  const totalLoans = loans.reduce((sum, loan) => sum + loan.outstanding_balance, 0);
  const totalShares = sharesAccount?.total_value || 0;
  const activeLoan = loans.find(loan => ['active', 'disbursed', 'approved'].includes(loan.status));
  const nextPayment = (activeLoan as any)?.next_payment_date;
  const nextPaymentAmount = (activeLoan as any)?.next_payment_amount || 0;

  // Sync savings goals with actual savings balance
  useEffect(() => {
    if (goals.length > 0 && totalSavings > 0) {
      goals.forEach(goal => {
        if (goal.current_amount !== totalSavings) {
          dispatch(updateCurrentAmount({ goalId: goal.id, amount: totalSavings }));
        }
      });
    }
  }, [totalSavings, goals, dispatch]);

  // Handle responsive behavior
  useEffect(() => {
    const checkIfMobile = () => {
      setIsMobile(window.innerWidth < 768);
    };

    checkIfMobile();
    window.addEventListener('resize', checkIfMobile);
    return () => window.removeEventListener('resize', checkIfMobile);
  }, []);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  // Add short currency formatter for mobile
  const formatCurrencyShort = (amount: number) => {
    if (amount >= 1000000) {
      return `UGX ${(amount / 1000000).toFixed(1)}M`;
    } else if (amount >= 1000) {
      return `UGX ${(amount / 1000).toFixed(0)}K`;
    }
    return formatCurrency(amount);
  };

  const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
  };

  // Transform transactions into recent activities
  const getTransactionIcon = (type: string) => {
    switch (type) {
      case 'deposit':
        return { icon: ArrowUpRight, color: 'text-green-600' };
      case 'withdrawal':
        return { icon: ArrowDownLeft, color: 'text-red-600' };
      case 'loan_disbursement':
        return { icon: TrendingUp, color: 'text-blue-600' };
      case 'loan_repayment':
        return { icon: Clock, color: 'text-orange-600' };
      case 'share_purchase':
        return { icon: PieChart, color: 'text-purple-600' };
      default:
        return { icon: ArrowUpRight, color: 'text-gray-600' };
    }
  };

  const recentActivities = transactions.slice(0, 5).map(transaction => {
    const { icon, color } = getTransactionIcon(transaction.type);
    return {
      id: transaction.id,
      type: transaction.type,
      description: transaction.description || `${transaction.type.replace('_', ' ')} transaction`,
      amount: transaction.amount,
      date: transaction.transaction_date || transaction.created_at,
      icon,
      iconColor: color,
    };
  });

  return (
    <>
      {/* Mobile Toolbar */}
      <MobileToolbar 
        title="Home" 
        user={user}
        showNotifications={true}
        onNotificationClick={() => {
          // Handle notification click
          console.log('Notifications clicked');
        }}
      />

      <div className="p-4 md:p-6 space-y-6 animate-fade-in">
        {/* Desktop Header */}
        <div className="hidden md:block">
          <h1 className="font-heading text-2xl md:text-3xl font-bold">
            {getGreeting()}, {user?.name?.split(' ')[0]}!
          </h1>
        </div>

        {/* Quick Stats - Hidden on mobile */}
        {!isMobile && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm text-muted-foreground font-medium">Total Savings</p>
                      <p className="text-2xl font-bold font-heading">{formatCurrency(totalSavings)}</p>
                    </div>
                    <div className="h-12 w-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                      <TrendingUp className="h-6 w-6 text-green-600" />
                    </div>
                  </div>
                  <div className="mt-4 flex items-center gap-2">
                    <Badge variant="secondary" className="bg-green-100 text-green-700 dark:bg-green-900/20">
                      +12% this month
                    </Badge>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm text-muted-foreground font-medium">Active Loans</p>
                      <p className="text-2xl font-bold font-heading">{formatCurrency(totalLoans)}</p>
                    </div>
                    <div className="h-12 w-12 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                      <Clock className="h-6 w-6 text-orange-600" />
                    </div>
                  </div>
                  {nextPayment && (
                      <div className="mt-4">
                        <p className="text-xs text-muted-foreground">
                          Next payment: {new Date(nextPayment).toLocaleDateString()} - {formatCurrency(nextPaymentAmount)}
                        </p>
                      </div>
                  )}
                </CardContent>
              </Card>

              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm text-muted-foreground font-medium">Share Capital</p>
                      <p className="text-2xl font-bold font-heading">{formatCurrency(totalShares)}</p>
                    </div>
                    <div className="h-12 w-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                      <PieChart className="h-6 w-6 text-blue-600" />
                    </div>
                  </div>
                  {sharesAccount && (
                      <div className="mt-4">
                        <p className="text-xs text-muted-foreground">
                          {sharesAccount.total_shares} shares @ {formatCurrency(sharesAccount.share_value)} each
                        </p>
                      </div>
                  )}
                </CardContent>
              </Card>

              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm text-muted-foreground font-medium">Member Status</p>
                      <div className="mt-2">
                        <Badge
                            variant={user?.status === 'active' ? 'default' : 'secondary'}
                            className="text-sm"
                        >
                          {user?.status === 'active' ? 'Active Member' : 'Pending Approval'}
                        </Badge>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="text-sm text-muted-foreground">Member #</p>
                      <p className="font-mono font-bold">{(user as any)?.member_number || 'Pending'}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
        )}

        {/* Wallet Card - Show prominently if exists */}
        {walletAccount && (
          <div className="animate-fade-in">
            <WalletCard
              accountId={walletAccount.id}
              onTopup={() => setWalletTopupOpen(true)}
              onWithdraw={() => setWalletWithdrawOpen(true)}
              compact={true}
            />
          </div>
        )}

        {/* Main Content */}
        <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <div className="xl:col-span-2 space-y-6">
            <OverviewChart />
            <QuickActions />
          </div>

          <div className="space-y-6">
            {/* Recent Activity */}
            <Card>
              <CardHeader>
                <CardTitle className="font-heading">Recent Activity</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {recentActivities.map((activity) => (
                      <div key={activity.id} className="flex items-center gap-3 p-3 rounded-lg hover:bg-accent/50 transition-colors">
                        <div className={`p-2 rounded-lg bg-accent/50 ${activity.iconColor}`}>
                          <activity.icon className="w-4 h-4" />
                        </div>
                        <div className="flex-1 min-w-0">
                          <p className="font-medium text-sm">{activity.description}</p>
                          <p className="text-xs text-muted-foreground">
                            {new Date(activity.date).toLocaleDateString('en-US', {
                              month: 'short',
                              day: 'numeric',
                              hour: '2-digit',
                              minute: '2-digit',
                            })}
                          </p>
                        </div>
                        <div className="text-right">
                          <p className={`font-bold text-sm ${activity.amount > 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {activity.amount > 0 ? '+' : ''}{formatCurrency(Math.abs(activity.amount))}
                          </p>
                        </div>
                      </div>
                  ))}
                </div>
              </CardContent>
            </Card>

            {/* Savings Goal Progress */}
            <Card>
              <CardHeader>
                <CardTitle>
                  {activeGoal ? activeGoal.title : 'Savings Goal'}
                </CardTitle>
              </CardHeader>
              <CardContent>
                {activeGoal ? (
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                      <span className="font-medium">{activeGoal.title}</span>
                      <span className="font-bold">{formatCurrency(activeGoal.target_amount)}</span>
                  </div>
                  <div className="w-full bg-accent rounded-full h-2">
                    <div
                        className="bg-primary h-2 rounded-full transition-all duration-500"
                        style={{ 
                          width: `${Math.min((activeGoal.current_amount / activeGoal.target_amount) * 100, 100)}%` 
                        }}
                    />
                  </div>
                  <div className="flex items-center justify-between text-sm text-muted-foreground">
                      <span>
                        {Math.round((activeGoal.current_amount / activeGoal.target_amount) * 100)}% achieved
                      </span>
                      <span>{formatCurrency(activeGoal.current_amount)} saved</span>
                    </div>
                    <div className="text-center">
                      <p className="text-sm font-medium text-primary">
                        {activeGoal.current_amount >= activeGoal.target_amount 
                          ? '🎉 Goal achieved! Congratulations!' 
                          : `Keep it up! ${formatCurrency(activeGoal.target_amount - activeGoal.current_amount)} to go!`
                        }
                      </p>
                    </div>
                    {activeGoal.target_date && (
                      <div className="text-center text-xs text-muted-foreground">
                        Target date: {new Date(activeGoal.target_date).toLocaleDateString()}
                      </div>
                    )}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <PieChart className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                    <h3 className="text-lg font-medium text-muted-foreground mb-2">
                      No Savings Goal Set
                    </h3>
                    <p className="text-sm text-muted-foreground mb-4">
                      Set a savings goal to track your progress and stay motivated.
                    </p>
                    <Button size="sm" variant="outline">
                      Set Goal
                    </Button>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Wallet Modals */}
        {walletAccount && (
          <>
            <WalletTopupForm
              isOpen={walletTopupOpen}
              onClose={() => setWalletTopupOpen(false)}
              walletAccountId={walletAccount.id}
              memberId={user?.id || 0}
            />
            <WalletWithdrawalForm
              isOpen={walletWithdrawOpen}
              onClose={() => setWalletWithdrawOpen(false)}
              walletAccountId={walletAccount.id}
              memberId={user?.id || 0}
              currentBalance={walletBalance?.balance || walletAccount.balance}
            />
          </>
        )}
      </div>
    </>
  );
}
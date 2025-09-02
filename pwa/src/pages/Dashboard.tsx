import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { fetchSavingsAccounts } from '@/store/savingsSlice';
import { fetchLoans } from '@/store/loansSlice';
import { fetchShares } from '@/store/sharesSlice';
import { OverviewChart } from '@/components/dashboard/OverviewChart';
import { QuickActions } from '@/components/dashboard/QuickActions';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { ArrowUpRight, ArrowDownLeft, TrendingUp, Clock, PieChart } from 'lucide-react';

export function Dashboard() {
  const dispatch = useDispatch<AppDispatch>();
  const { user } = useSelector((state: RootState) => state.auth);
  const { accounts } = useSelector((state: RootState) => state.savings);
  const { loans } = useSelector((state: RootState) => state.loans);
  const { account: sharesAccount } = useSelector((state: RootState) => state.shares);

  useEffect(() => {
    dispatch(fetchSavingsAccounts());
    dispatch(fetchLoans());
    dispatch(fetchShares());
  }, [dispatch]);

  const totalSavings = accounts.reduce((sum, account) => sum + account.balance, 0);
  const totalLoans = loans.reduce((sum, loan) => sum + loan.outstanding_balance, 0);
  const totalShares = sharesAccount?.total_value || 0;
  const nextPayment = loans.find(loan => loan.status === 'active')?.next_payment_date;
  const nextPaymentAmount = loans.find(loan => loan.status === 'active')?.next_payment_amount || 0;

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
  };

  // Mock recent activities
  const recentActivities = [
    {
      id: 1,
      type: 'deposit',
      description: 'Salary deposit',
      amount: 75000,
      date: '2024-01-15T10:30:00',
      icon: ArrowUpRight,
      iconColor: 'text-green-600',
    },
    {
      id: 2,
      type: 'withdrawal',
      description: 'ATM withdrawal',
      amount: -5000,
      date: '2024-01-14T14:20:00',
      icon: ArrowDownLeft,
      iconColor: 'text-red-600',
    },
    {
      id: 3,
      type: 'loan_repayment',
      description: 'Loan repayment',
      amount: -15000,
      date: '2024-01-13T09:15:00',
      icon: Clock,
      iconColor: 'text-blue-600',
    },
  ];

  return (
    <div className="p-4 md:p-6 space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="font-heading text-2xl md:text-3xl font-bold">
            {getGreeting()}, {user?.name?.split(' ')[0]}! 👋
          </h1>
          <p className="text-muted-foreground mt-1">
            Welcome back to your SACCO dashboard
          </p>
        </div>
        <Avatar className="h-12 w-12">
          <AvatarImage src="" />
          <AvatarFallback className="bg-primary text-primary-foreground text-lg font-bold">
            {user?.name?.charAt(0).toUpperCase()}
          </AvatarFallback>
        </Avatar>
      </div>

      {/* Quick Stats */}
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
                <p className="font-mono font-bold">{user?.member_number || 'Pending'}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

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

          {/* Savings Goal Progress (Mock) */}
          <Card>
            <CardHeader>
              <CardTitle className="font-heading">Savings Goal</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <span className="font-medium">Monthly Target</span>
                  <span className="font-bold">{formatCurrency(100000)}</span>
                </div>
                <div className="w-full bg-accent rounded-full h-2">
                  <div 
                    className="bg-primary h-2 rounded-full transition-all duration-500" 
                    style={{ width: '70%' }}
                  />
                </div>
                <div className="flex items-center justify-between text-sm text-muted-foreground">
                  <span>70% achieved</span>
                  <span>{formatCurrency(70000)} saved</span>
                </div>
                <div className="text-center">
                  <p className="text-sm font-medium text-primary">🎯 Great progress! Keep it up!</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
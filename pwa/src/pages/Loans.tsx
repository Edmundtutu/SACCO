import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { fetchLoans, fetchLoanProducts } from '@/store/loansSlice';
import { fetchSavingsAccounts } from '@/store/savingsSlice';
import { findWalletAccount } from '@/utils/accountHelpers';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { LoanApplicationForm } from '@/components/loans/LoanApplicationForm';
import { LoanApplicationStatus } from '@/components/loans/LoanApplicationStatus';
import { LoanTracker } from '@/components/loans/LoanTracker';
import { LoanRepaymentForm } from '@/components/loans/LoanRepaymentForm';
import { RepaymentSchedule } from '@/components/loans/RepaymentSchedule';
import { TransactionHistory } from '@/components/transactions/TransactionHistory';
import { DashboardPage } from '@/components/layout/DashboardPage';
import { WalletLoanPaymentForm } from '@/components/wallet/WalletLoanPaymentForm';
import {
  Plus,
  CreditCard,
  TrendingUp,
  Clock,
  CheckCircle,
  AlertCircle,
  FileText,
  Calculator,
  TrendingDown,
  Wallet,
  ChevronRight
} from 'lucide-react';

export default function Loans() {
  const dispatch = useDispatch<AppDispatch>();
  const { user } = useSelector((state: RootState) => state.auth);
  const { loans = [], products: loanProducts = [], loading } = useSelector((state: RootState) => state.loans);
  const { accounts } = useSelector((state: RootState) => state.savings);

  const [applicationModalOpen, setApplicationModalOpen] = useState(false);
  const [repaymentModalOpen, setRepaymentModalOpen] = useState(false);
  const [walletPaymentModalOpen, setWalletPaymentModalOpen] = useState(false);
  const [selectedLoan, setSelectedLoan] = useState<any>(null);

  // Find wallet account using standardized helper
  const walletAccount = findWalletAccount(accounts);

  useEffect(() => {
    dispatch(fetchLoans());
    dispatch(fetchLoanProducts());
    dispatch(fetchSavingsAccounts());
  }, [dispatch]);

  // Helper to calculate loan repayment progress
  const calculateProgress = (loan: any) => {
    const paid = loan.principal_amount - loan.outstanding_balance;
    return Math.round((paid / loan.principal_amount) * 100);
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'active':
        return <Badge className="bg-green-100 text-green-800">Active</Badge>;
      case 'pending':
        return <Badge variant="secondary" className="bg-yellow-100 text-yellow-800">Pending</Badge>;
      case 'approved':
        return <Badge className="bg-blue-100 text-blue-800">Approved</Badge>;
      case 'rejected':
        return <Badge variant="destructive">Rejected</Badge>;
      case 'completed':
        return <Badge className="bg-gray-100 text-gray-800">Completed</Badge>;
      default:
        return <Badge variant="outline">{status}</Badge>;
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'active':
        return <CheckCircle className="w-5 h-5 text-green-600" />;
      case 'pending':
        return <Clock className="w-5 h-5 text-yellow-600" />;
      case 'approved':
        return <CheckCircle className="w-5 h-5 text-blue-600" />;
      case 'rejected':
        return <AlertCircle className="w-5 h-5 text-red-600" />;
      case 'completed':
        return <CheckCircle className="w-5 h-5 text-gray-600" />;
      default:
        return <Clock className="w-5 h-5 text-gray-600" />;
    }
  };

  // Include loans that are disbursed, active, or approved as "active" loans
  const activeLoans = loans.filter(loan =>
    ['active', 'disbursed'].includes(loan.status)
  );
  const nonActiveLoans = loans.filter(loan =>
    ['approved', 'completed', 'defaulted', 'written_off'].includes(loan.status)
  );
  const pendingLoans = loans.filter(loan =>
    ['pending', 'under_review'].includes(loan.status)
  );
  const totalOutstanding = activeLoans.reduce((sum, loan) => sum + loan.outstanding_balance, 0);
  const totalNonActiveOutstanding = nonActiveLoans.reduce((sum, loan) => sum + loan.outstanding_balance, 0);
  const totalPrincipal = loans.reduce((sum, loan) => sum + loan.principal_amount, 0);
  const totalActivePlusNonActiveLoans = activeLoans.length + nonActiveLoans.length;

  const toolbarActions = (
    <>
      <Button
        onClick={() => setApplicationModalOpen(true)}
        className="bg-primary hover:bg-primary/90"
      >
        <Plus className="w-4 h-4 mr-2" />
        Apply for Loan
      </Button>
      {activeLoans.length > 0 && (
        <>
          <Button
            variant="outline"
            onClick={() => setRepaymentModalOpen(true)}
          >
            <CreditCard className="w-4 h-4 mr-2" />
            Make Payment
          </Button>
          {walletAccount && (
            <Button
              onClick={() => {
                setSelectedLoan(activeLoans[0]);
                setWalletPaymentModalOpen(true);
              }}
              className="bg-primary/90 hover:bg-primary"
            >
              <CreditCard className="w-4 h-4 mr-2" />
              Pay with Wallet
            </Button>
          )}
        </>
      )}
    </>
  );

  return (
    <DashboardPage 
      title="Loans" 
      subtitle="Manage your loans and applications"
      toolbarActions={toolbarActions}
    >
      {/* Mobile Hero Section with Glass Card */}
      <div className="md:hidden relative -mx-4 md:mx-0 -mt-4 md:mt-0 bg-gradient-to-br from-primary via-primary to-secondary px-4 pt-6 pb-10 mb-3">
        {/* Decorative circles */}
        <div className="absolute top-0 right-0 w-64 h-64 bg-background/10 rounded-full blur-3xl"></div>
        <div className="absolute bottom-0 left-0 w-48 h-48 bg-secondary/20 rounded-full blur-2xl"></div>
        
        <div className="relative z-10">
          {/* Main Balance Card - Mobile Optimized */}
              <div className="bg-background/95 backdrop-blur-lg rounded-3xl p-6 shadow-2xl border border-border/20">
              <div className="flex items-start justify-between mb-4">
                <div>
                  <p className="text-sm text-muted-foreground mb-1">Total Outstanding</p>
                  <h2 className="text-3xl font-bold text-foreground">
                    {formatCurrency(totalOutstanding)}
                  </h2>
                </div>
                <div className="bg-destructive p-3 rounded-2xl shadow-lg">
                  <TrendingDown className="w-6 h-6 text-destructive-foreground" />
                </div>
              </div>            {/* Mini Stats Row */}
            <div className="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-border">
              <div className="bg-muted rounded-2xl p-3">
                <div className="flex items-center gap-2 mb-1">
                  <div className="w-8 h-8 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                    <CheckCircle className="w-4 h-4 text-green-600 dark:text-green-400" />
                  </div>
                  <span className="text-xs text-muted-foreground font-medium">Active</span>
                </div>
                <p className="text-xl font-bold text-foreground">{activeLoans.length}</p>
              </div>
              
              <div className="bg-muted rounded-2xl p-3">
                <div className="flex items-center gap-2 mb-1">
                  <div className="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                    <TrendingUp className="w-4 h-4 text-blue-600 dark:text-blue-400" />
                  </div>
                  <span className="text-xs text-muted-foreground font-medium">Borrowed</span>
                </div>
                <p className="text-lg font-bold text-foreground truncate">{formatCurrency(totalPrincipal)}</p>
              </div>
            </div>

            {/* Quick Action Button */}
            <Button 
              onClick={() => setApplicationModalOpen(true)}
              className="w-full mt-4 rounded-2xl"
              size="lg"
            >
              <Plus className="w-5 h-5 mr-2" />
              Apply for New Loan
            </Button>
          </div>
        </div>
      </div>

      <div className="space-y-6">
        {/* Summary Cards - Desktop Only */}
        <div className="hidden md:grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card className="rounded-2xl border-border">
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-4">
                <div className="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-xl flex items-center justify-center">
                  <CheckCircle className="h-6 w-6 text-green-600 dark:text-green-400" />
                </div>
                <span className="text-2xl font-bold text-foreground">{activeLoans.length}</span>
              </div>
              <p className="text-sm text-muted-foreground font-medium">Active Loans</p>
              <div className="mt-2">
                <p className="text-xs text-muted-foreground">
                  Outstanding: {formatCurrency(totalOutstanding)}
                </p>
              </div>
            </CardContent>
          </Card>

          {/* If Non active loans exist show their stat card */}
          {nonActiveLoans.length > 0 && (
            <Card className="rounded-2xl border-border">
              <CardContent className="p-6">
                <div className="flex items-center justify-between mb-4">
                  <div className="w-12 h-12 bg-muted rounded-xl flex items-center justify-center">
                    <CheckCircle className="h-6 w-6 text-muted-foreground" />
                  </div>
                  <span className="text-2xl font-bold text-foreground">{nonActiveLoans.length}</span>
                </div>
                <p className="text-sm text-muted-foreground font-medium">Non Active</p>
                <div className="mt-2">
                  <p className="text-xs text-muted-foreground">
                    Outstanding: {formatCurrency(totalNonActiveOutstanding)}
                  </p>
                </div>
              </CardContent>
            </Card>
          )}
          <Card className="rounded-2xl border-border">
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-4">
                <div className="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/20 rounded-xl flex items-center justify-center">
                  <Clock className="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <span className="text-2xl font-bold text-foreground">{pendingLoans.length}</span>
              </div>
              <p className="text-sm text-muted-foreground font-medium">Pending Applications</p>
              <div className="mt-2">
                <p className="text-xs text-muted-foreground">
                  Under review
                </p>
              </div>
            </CardContent>
          </Card>

          <Card className="rounded-2xl border-border">
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-4">
                <div className="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                  <TrendingUp className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                </div>
                <span className="text-2xl font-bold text-foreground">{formatCurrency(totalPrincipal)}</span>
              </div>
              <p className="text-sm text-muted-foreground font-medium">Total Borrowed</p>
              <div className="mt-2">
                <p className="text-xs text-muted-foreground">
                  All time
                </p>
              </div>
            </CardContent>
          </Card>

          <Card className="rounded-2xl border-border">
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-4">
                <div className="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-xl flex items-center justify-center">
                  <FileText className="h-6 w-6 text-purple-600 dark:text-purple-400" />
                </div>
                <span className="text-2xl font-bold text-green-600 dark:text-green-400">Good</span>
              </div>
              <p className="text-sm text-muted-foreground font-medium">Credit Score</p>
              <div className="mt-2">
                <p className="text-xs text-muted-foreground">
                  Based on payment history
                </p>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Main Content */}
        <Tabs defaultValue="my-loans" className="space-y-4">
          {/* Modern Tab Navigation */}
          <div className="bg-background rounded-2xl shadow-lg p-2 border border-border overflow-x-auto">
            <TabsList className="inline-flex h-auto bg-transparent gap-2 min-w-max md:min-w-0 w-full md:w-auto">
              <TabsTrigger 
                value="my-loans" 
                className="flex items-center gap-2 rounded-xl px-4 md:px-6 py-3 data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md transition-all whitespace-nowrap"
              >
                <CreditCard className="w-4 h-4" />
                <span className="hidden sm:inline">My Loans</span>
                <span className="sm:hidden">Loans</span>
              </TabsTrigger>
              <TabsTrigger 
                value="applications" 
                className="flex items-center gap-2 rounded-xl px-4 md:px-6 py-3 data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md transition-all whitespace-nowrap"
              >
                <FileText className="w-4 h-4" />
                <span className="hidden sm:inline">Applications</span>
                <span className="sm:hidden">Apply</span>
              </TabsTrigger>
              <TabsTrigger 
                value="repayment" 
                className="flex items-center gap-2 rounded-xl px-4 md:px-6 py-3 data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md transition-all whitespace-nowrap"
              >
                <Calculator className="w-4 h-4" />
                <span className="hidden sm:inline">Repayment</span>
                <span className="sm:hidden">Pay</span>
              </TabsTrigger>
              <TabsTrigger 
                value="transactions" 
                className="flex items-center gap-2 rounded-xl px-4 md:px-6 py-3 data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md transition-all whitespace-nowrap"
              >
                <TrendingUp className="w-4 h-4" />
                <span className="hidden sm:inline">Transactions</span>
                <span className="sm:hidden">History</span>
              </TabsTrigger>
              <TabsTrigger 
                value="products" 
                className="flex items-center gap-2 rounded-xl px-4 md:px-6 py-3 data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md transition-all whitespace-nowrap"
              >
                <Plus className="w-4 h-4" />
                <span className="hidden sm:inline">Products</span>
                <span className="sm:hidden">New</span>
              </TabsTrigger>
            </TabsList>
          </div>

          <TabsContent value="my-loans" className="space-y-4">
            {totalActivePlusNonActiveLoans > 0 ? (
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {loans.map((loan) => {
                  const progress = calculateProgress(loan);
                  const isActiveLoan = activeLoans.includes(loan);
                  
                  return (
                    <Card key={loan.id} className="rounded-3xl border-border overflow-hidden hover:shadow-md transition-all">
                      {/* Loan Header with Progress */}
                      <div className="bg-muted p-4 border-b border-border">
                        <div className="flex items-center justify-between mb-3">
                          <div className="flex items-center gap-3">
                            <div className="w-10 h-10 bg-primary rounded-xl flex items-center justify-center shadow-sm">
                              <CreditCard className="w-5 h-5 text-primary-foreground" />
                            </div>
                            <div>
                              <div className="flex items-center gap-2">
                                {getStatusIcon(loan.status)}
                                <p className="font-bold text-foreground text-sm md:text-base">{loan.loan_number}</p>
                              </div>
                              {getStatusBadge(loan.status)}
                            </div>
                          </div>
                          <button 
                            onClick={() => setSelectedLoan(loan)}
                            className="w-8 h-8 bg-background rounded-lg flex items-center justify-center hover:bg-muted transition-colors"
                          >
                            <ChevronRight className="w-4 h-4 text-muted-foreground" />
                          </button>
                        </div>

                        {/* Progress Bar */}
                        <div>
                          <div className="flex justify-between text-xs text-muted-foreground mb-1">
                            <span>Repayment Progress</span>
                            <span className="font-semibold">{progress}%</span>
                          </div>
                          <div className="h-2 bg-muted rounded-full overflow-hidden">
                            <div
                              className="h-full bg-green-600 dark:bg-green-500 rounded-full transition-all duration-500"
                              style={{ width: `${progress}%` }}
                            ></div>
                          </div>
                        </div>
                      </div>

                      {/* Loan Details Grid */}
                      <CardContent className="p-4 space-y-4">
                        <div className="grid grid-cols-2 gap-3 text-sm">
                          <div className="space-y-1">
                            <p className="text-xs text-muted-foreground font-medium">Principal Amount</p>
                            <p className="font-bold text-foreground">{formatCurrency(loan.principal_amount)}</p>
                          </div>
                          <div className="space-y-1">
                            <p className="text-xs text-muted-foreground font-medium">Outstanding</p>
                            <p className="font-bold text-destructive">{formatCurrency(loan.outstanding_balance)}</p>
                          </div>
                          <div className="space-y-1">
                            <p className="text-xs text-muted-foreground font-medium">Interest Rate</p>
                            <p className="font-bold text-foreground">{loan.interest_rate}% p.a.</p>
                          </div>
                          {isActiveLoan && (
                            <div className="space-y-1">
                              <p className="text-xs text-muted-foreground font-medium">Next Payment</p>
                              <p className="font-bold text-blue-600 dark:text-blue-400">{formatCurrency((loan as any).next_payment_amount || 0)}</p>
                            </div>
                          )}
                        </div>

                        {/* Action Buttons */}
                        {isActiveLoan && (
                          <div className="flex gap-2 pt-2">
                            <Button
                              size="sm"
                              className="flex-1 rounded-2xl"
                              onClick={() => {
                                setSelectedLoan(loan);
                                setRepaymentModalOpen(true);
                              }}
                            >
                              Make Payment
                            </Button>
                            <Button
                              size="sm"
                              variant="outline"
                              className="px-6 rounded-2xl"
                              onClick={() => setSelectedLoan(loan)}
                            >
                              Details
                            </Button>
                          </div>
                        )}
                      </CardContent>
                    </Card>
                  );
                })}
              </div>
            ) : (
              <Card className="rounded-3xl border-border">
                <CardContent className="text-center py-12">
                  <div className="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <CreditCard className="w-8 h-8 text-primary-foreground" />
                  </div>
                  <h3 className="text-lg font-medium text-muted-foreground mb-2">
                    No active loans
                  </h3>
                  <p className="text-sm text-muted-foreground mb-4">
                    You don't have any active loans at the moment.
                  </p>
                  <Button 
                    onClick={() => setApplicationModalOpen(true)}
                    className="rounded-2xl"
                  >
                    Apply for a Loan
                  </Button>
                </CardContent>
              </Card>
            )}
          </TabsContent>

          <TabsContent value="applications" className="space-y-4">
            <LoanApplicationStatus loan={pendingLoans.length > 0 ? pendingLoans[0] : null} />
          </TabsContent>

          <TabsContent value="repayment" className="space-y-4">
            {activeLoans.length > 0 ? (
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <LoanTracker loan={activeLoans[0]} repaymentProgress={75} />
                <RepaymentSchedule loan={activeLoans[0]} />
              </div>
            ) : (
              <Card>
                <CardContent className="text-center py-12">
                  <Calculator className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                  <h3 className="text-lg font-medium text-muted-foreground mb-2">
                    No active loans to repay
                  </h3>
                  <p className="text-sm text-muted-foreground">
                    You don't have any active loans that require repayment.
                  </p>
                </CardContent>
              </Card>
            )}
          </TabsContent>

          <TabsContent value="transactions" className="space-y-4">
            <TransactionHistory memberId={user?.id || 0} context="loans" />
          </TabsContent>

          <TabsContent value="products" className="space-y-4">
            <Card className="rounded-3xl border-border">
              <CardHeader className="px-4 md:px-6">
                <CardTitle className="text-lg md:text-xl">Available Loan Products</CardTitle>
              </CardHeader>
              <CardContent className="px-4 md:px-6">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  {loanProducts.map((product) => (
                    <Card key={product.id} className="rounded-3xl border-border overflow-hidden hover:shadow-md transition-all">
                      {/* Product Header */}
                      <div className="bg-primary p-4">
                        <h3 className="text-xl font-bold text-primary-foreground mb-1">{product.name}</h3>
                        <p className="text-primary-foreground/90 text-sm">{product.description}</p>
                      </div>
                      
                      {/* Product Details */}
                      <CardContent className="p-4 space-y-3">
                        <div className="flex items-center justify-between py-2 border-b border-border">
                          <span className="text-sm text-muted-foreground">Interest Rate</span>
                          <span className="text-lg font-bold text-green-600 dark:text-green-400">{product.interest_rate}% p.a.</span>
                        </div>
                        
                        <div className="grid grid-cols-2 gap-3">
                          <div className="bg-muted rounded-xl p-3">
                            <p className="text-xs text-muted-foreground mb-1">Min Amount</p>
                            <p className="text-sm font-bold text-foreground truncate">{formatCurrency(product.minimum_amount)}</p>
                          </div>
                          <div className="bg-muted rounded-xl p-3">
                            <p className="text-xs text-muted-foreground mb-1">Max Amount</p>
                            <p className="text-sm font-bold text-foreground truncate">{formatCurrency(product.maximum_amount)}</p>
                          </div>
                        </div>
                        
                        <div className="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3 flex items-center justify-between">
                          <span className="text-sm text-muted-foreground">Maximum Period</span>
                          <span className="text-sm font-bold text-blue-600 dark:text-blue-400">{product.maximum_period_months} months</span>
                        </div>
                        
                        <Button
                          className="w-full mt-2 rounded-2xl"
                          size="sm"
                          onClick={() => setApplicationModalOpen(true)}
                        >
                          Apply Now
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
        <LoanApplicationForm
          isOpen={applicationModalOpen}
          onClose={() => setApplicationModalOpen(false)}
        />

        <LoanRepaymentForm
          isOpen={repaymentModalOpen}
          onClose={() => setRepaymentModalOpen(false)}
          loan={selectedLoan}
        />

        {/* Wallet Payment Modal */}
        {walletAccount && selectedLoan && (
          <WalletLoanPaymentForm
            isOpen={walletPaymentModalOpen}
            onClose={() => {
              setWalletPaymentModalOpen(false);
              setSelectedLoan(null);
            }}
            walletAccountId={walletAccount.id}
            memberId={user?.id || 0}
            loanId={selectedLoan.id}
            loanNumber={selectedLoan.loan_number}
            outstandingBalance={selectedLoan.outstanding_balance}
            onSuccess={() => {
              dispatch(fetchLoans());
            }}
          />
        )}
      </div>
    </DashboardPage>
  );
}
import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { fetchLoans, fetchLoanProducts } from '@/store/loansSlice';
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
import { MobileToolbar } from '@/components/layout/MobileToolbar';
import { 
  Plus, 
  CreditCard, 
  TrendingUp, 
  Clock,
  CheckCircle,
  AlertCircle,
  FileText,
  Calculator
} from 'lucide-react';

export default function Loans() {
  const dispatch = useDispatch<AppDispatch>();
  const { user } = useSelector((state: RootState) => state.auth);
  const { loans = [], products: loanProducts = [], loading } = useSelector((state: RootState) => state.loans);
  
  const [applicationModalOpen, setApplicationModalOpen] = useState(false);
  const [repaymentModalOpen, setRepaymentModalOpen] = useState(false);
  const [selectedLoan, setSelectedLoan] = useState<any>(null);

  useEffect(() => {
    dispatch(fetchLoans());
    dispatch(fetchLoanProducts());
  }, [dispatch]);

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
    ['active', 'disbursed', 'approved'].includes(loan.status)
  );
  const pendingLoans = loans.filter(loan => 
    ['pending', 'under_review'].includes(loan.status)
  );
  const totalOutstanding = activeLoans.reduce((sum, loan) => sum + loan.outstanding_balance, 0);
  const totalPrincipal = loans.reduce((sum, loan) => sum + loan.principal_amount, 0);

  return (
    <>
      {/* Mobile Toolbar */}
      <MobileToolbar 
        title="Loans" 
        user={user}
        showNotifications={true}
      />

      <div className="p-4 md:p-6 space-y-6">
        {/* Desktop Header */}
        <div className="hidden md:block">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="font-heading text-2xl md:text-3xl font-bold">Loans</h1>
              <p className="text-muted-foreground">Manage your loans and applications</p>
            </div>
            <div className="flex gap-2">
          <Button 
            onClick={() => setApplicationModalOpen(true)}
            className="bg-primary hover:bg-primary/90"
          >
            <Plus className="w-4 h-4 mr-2" />
          Apply for Loan
        </Button>
          {activeLoans.length > 0 && (
            <Button 
              variant="outline"
              onClick={() => setRepaymentModalOpen(true)}
            >
              <CreditCard className="w-4 h-4 mr-2" />
              Make Payment
            </Button>
          )}
            </div>
          </div>
        </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Active Loans</p>
                <p className="text-2xl font-bold font-heading">{activeLoans.length}</p>
              </div>
              <div className="h-12 w-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                <CheckCircle className="h-6 w-6 text-green-600" />
              </div>
            </div>
            <div className="mt-4">
              <p className="text-xs text-muted-foreground">
                Outstanding: {formatCurrency(totalOutstanding)}
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Pending Applications</p>
                <p className="text-2xl font-bold font-heading">{pendingLoans.length}</p>
              </div>
              <div className="h-12 w-12 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg flex items-center justify-center">
                <Clock className="h-6 w-6 text-yellow-600" />
              </div>
            </div>
            <div className="mt-4">
              <p className="text-xs text-muted-foreground">
                Under review
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Total Borrowed</p>
                <p className="text-2xl font-bold font-heading">{formatCurrency(totalPrincipal)}</p>
              </div>
              <div className="h-12 w-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                <TrendingUp className="h-6 w-6 text-blue-600" />
              </div>
            </div>
            <div className="mt-4">
              <p className="text-xs text-muted-foreground">
                All time
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Credit Score</p>
                <p className="text-2xl font-bold font-heading">Good</p>
              </div>
              <div className="h-12 w-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                <FileText className="h-6 w-6 text-purple-600" />
              </div>
            </div>
            <div className="mt-4">
              <p className="text-xs text-muted-foreground">
                Based on payment history
              </p>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Main Content */}
      <Tabs defaultValue="my-loans" className="space-y-4">
        <TabsList className="grid w-full grid-cols-5">
          <TabsTrigger value="my-loans" className="flex items-center gap-2">
            <CreditCard className="w-4 h-4" />
            My Loans
          </TabsTrigger>
          <TabsTrigger value="applications" className="flex items-center gap-2">
            <FileText className="w-4 h-4" />
            Applications
          </TabsTrigger>
          <TabsTrigger value="repayment" className="flex items-center gap-2">
            <Calculator className="w-4 h-4" />
            Repayment
          </TabsTrigger>
          <TabsTrigger value="transactions" className="flex items-center gap-2">
            <TrendingUp className="w-4 h-4" />
            Transactions
          </TabsTrigger>
          <TabsTrigger value="products" className="flex items-center gap-2">
            <Plus className="w-4 h-4" />
            Products
          </TabsTrigger>
        </TabsList>

        <TabsContent value="my-loans" className="space-y-4">
          {activeLoans.length > 0 ? (
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              {activeLoans.map((loan) => (
                <Card key={loan.id} className="hover:shadow-md transition-shadow">
              <CardHeader>
                    <div className="flex items-center justify-between">
                <CardTitle className="flex items-center gap-2">
                        {getStatusIcon(loan.status)}
                        {loan.loan_number}
                </CardTitle>
                      {getStatusBadge(loan.status)}
                    </div>
              </CardHeader>
              <CardContent className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                        <p className="text-sm text-muted-foreground">Principal Amount</p>
                        <p className="font-semibold">{formatCurrency(loan.principal_amount)}</p>
                      </div>
                        <div>
                        <p className="text-sm text-muted-foreground">Outstanding Balance</p>
                        <p className="font-semibold text-red-600">{formatCurrency(loan.outstanding_balance)}</p>
                        </div>
                        <div>
                        <p className="text-sm text-muted-foreground">Interest Rate</p>
                        <p className="font-semibold">{loan.interest_rate}% p.a.</p>
                        </div>
                        <div>
                        <p className="text-sm text-muted-foreground">Next Payment</p>
                        <p className="font-semibold">{formatCurrency((loan as any).next_payment_amount || 0)}</p>
                      </div>
                    </div>
                    <div className="flex gap-2">
                      <Button 
                        size="sm" 
                        className="flex-1"
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
                        onClick={() => setSelectedLoan(loan)}
                      >
                        View Details
                      </Button>
                    </div>
              </CardContent>
            </Card>
              ))}
          </div>
          ) : (
            <Card>
              <CardContent className="text-center py-12">
                <CreditCard className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                <h3 className="text-lg font-medium text-muted-foreground mb-2">
                  No active loans
                </h3>
                <p className="text-sm text-muted-foreground mb-4">
                  You don't have any active loans at the moment.
                </p>
                <Button onClick={() => setApplicationModalOpen(true)}>
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
          <Card>
            <CardHeader>
              <CardTitle>Available Loan Products</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {loanProducts.map((product) => (
                  <Card key={product.id} className="hover:shadow-md transition-shadow">
                    <CardContent className="p-4">
                      <h3 className="font-semibold text-lg mb-2">{product.name}</h3>
                      <p className="text-sm text-muted-foreground mb-3">{product.description}</p>
                      <div className="space-y-2">
                        <div className="flex justify-between">
                          <span className="text-sm">Interest Rate:</span>
                          <span className="font-semibold text-green-600">{product.interest_rate}% p.a.</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-sm">Min Amount:</span>
                          <span className="font-semibold">{formatCurrency(product.minimum_amount)}</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-sm">Max Amount:</span>
                          <span className="font-semibold">{formatCurrency(product.maximum_amount)}</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-sm">Max Period:</span>
                          <span className="font-semibold">{product.maximum_period_months} months</span>
                        </div>
                      </div>
                      <Button 
                        className="w-full mt-4" 
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
      </div>
    </>
  );
}
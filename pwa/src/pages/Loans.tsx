import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState } from '@/store';
import { fetchLoans, fetchLoanProducts } from '@/store/loansSlice';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Progress } from '@/components/ui/progress';
import { LoanTracker } from '@/components/loans/LoanTracker';
import { LoanProducts } from '@/components/loans/LoanProducts';
import { LoanApplication } from '@/components/loans/LoanApplication';
import { RepaymentSchedule } from '@/components/loans/RepaymentSchedule';
import { Calculator, FileText, CreditCard } from 'lucide-react';

export default function Loans() {
  const dispatch = useDispatch();
  const { loans, products, loading } = useSelector((state: RootState) => state.loans);
  const [selectedLoanId, setSelectedLoanId] = useState<number | null>(null);
  const [showApplication, setShowApplication] = useState(false);
  const [selectedProductId, setSelectedProductId] = useState<number | null>(null);

  useEffect(() => {
    dispatch(fetchLoans() as any);
    dispatch(fetchLoanProducts() as any);
  }, [dispatch]);

  const activeLoan = loans.find(loan => loan.status === 'active');
  const totalOutstanding = loans.reduce((sum, loan) => sum + loan.outstanding_balance, 0);
  const totalPrincipal = loans.reduce((sum, loan) => sum + loan.principal_amount, 0);
  const repaymentProgress = totalPrincipal > 0 ? ((totalPrincipal - totalOutstanding) / totalPrincipal) * 100 : 0;

  return (
    <div className="p-4 space-y-6 max-w-6xl mx-auto">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-heading font-bold text-foreground">Loans</h1>
          <p className="text-muted-foreground">Manage your loans and applications</p>
        </div>
        <Button onClick={() => setShowApplication(true)} className="gap-2">
          <FileText className="w-4 h-4" />
          Apply for Loan
        </Button>
      </div>

      {/* Loan Repayment Tracker */}
      {activeLoan && (
        <LoanTracker 
          loan={activeLoan}
          repaymentProgress={repaymentProgress}
        />
      )}

      <Tabs defaultValue="overview" className="space-y-6">
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="products">Products</TabsTrigger>
          <TabsTrigger value="schedule">Schedule</TabsTrigger>
          <TabsTrigger value="calculator">Calculator</TabsTrigger>
        </TabsList>

        <TabsContent value="overview">
          <div className="grid gap-6 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <CreditCard className="w-5 h-5" />
                  My Loans
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {loans.length === 0 ? (
                  <p className="text-muted-foregrounde py-8 text-center">No active loans</p>
                ) : (
                  loans.map((loan) => (
                    <div key={loan.id} className="p-4 border rounded-lg space-y-3">
                      <div className="flex justify-between items-start">
                        <div>
                          <h3 className="font-medium">{loan.product_name}</h3>
                          <p className="text-sm text-muted-foreground">
                            Applied: {new Date(loan.created_at).toLocaleDateString()}
                          </p>
                        </div>
                        <Badge variant={loan.status === 'active' ? 'default' : 
                                      loan.status === 'paid' ? 'secondary' : 
                                      loan.status === 'overdue' ? 'destructive' : 'outline'}>
                          {loan.status}
                        </Badge>
                      </div>
                      
                      <div className="grid grid-cols-2 gap-4 text-sm">
                        <div>
                          <p className="text-muted-foreground">Principal</p>
                          <p className="font-medium">KES {loan.principal_amount.toLocaleString()}</p>
                        </div>
                        <div>
                          <p className="text-muted-foreground">Outstanding</p>
                          <p className="font-medium">KES {loan.outstanding_balance.toLocaleString()}</p>
                        </div>
                        <div>
                          <p className="text-muted-foreground">Monthly Payment</p>
                          <p className="font-medium">KES {loan.monthly_payment.toLocaleString()}</p>
                        </div>
                        <div>
                          <p className="text-muted-foreground">Next Payment</p>
                          <p className="font-medium">{new Date(loan.next_payment_date).toLocaleDateString()}</p>
                        </div>
                      </div>

                      {loan.status === 'active' && (
                        <div className="space-y-2">
                          <div className="flex justify-between text-sm">
                            <span>Repayment Progress</span>
                            <span>{Math.round(((loan.principal_amount - loan.outstanding_balance) / loan.principal_amount) * 100)}%</span>
                          </div>
                          <Progress value={((loan.principal_amount - loan.outstanding_balance) / loan.principal_amount) * 100} />
                        </div>
                      )}
                    </div>
                  ))
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Quick Stats</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-2 gap-4 text-center">
                  <div className="p-4 bg-muted/50 rounded-lg">
                    <p className="text-2xl font-bold text-primary">
                      {loans.filter(l => l.status === 'active').length}
                    </p>
                    <p className="text-sm text-muted-foreground">Active Loans</p>
                  </div>
                  <div className="p-4 bg-muted/50 rounded-lg">
                    <p className="text-2xl font-bold text-success">
                      KES {totalOutstanding.toLocaleString()}
                    </p>
                    <p className="text-sm text-muted-foreground">Outstanding</p>
                  </div>
                </div>
                
                {activeLoan && (
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span>Next Payment Due</span>
                      <span className="font-medium">{new Date(activeLoan.next_payment_date).toLocaleDateString()}</span>
                    </div>
                    <Button className="w-full" variant="outline">
                      Make Payment
                    </Button>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="products">
          <LoanProducts 
            products={products}
            onApply={(productId) => {
              setSelectedProductId(productId);
              setShowApplication(true);
            }}
          />
        </TabsContent>

        <TabsContent value="schedule">
          {activeLoan ? (
            <RepaymentSchedule loan={activeLoan} />
          ) : (
            <Card>
              <CardContent className="flex items-center justify-center h-32">
                <p className="text-muted-foreground">No active loans to show schedule</p>
              </CardContent>
            </Card>
          )}
        </TabsContent>

        <TabsContent value="calculator">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Calculator className="w-5 h-5" />
                Loan Calculator
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">Loan calculator coming soon...</p>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {/* Loan Application Modal */}
      {showApplication && (
        <LoanApplication 
          products={products}
          selectedProductId={selectedProductId}
          onClose={() => {
            setShowApplication(false);
            setSelectedProductId(null);
          }}
        />
      )}
    </div>
  );
}
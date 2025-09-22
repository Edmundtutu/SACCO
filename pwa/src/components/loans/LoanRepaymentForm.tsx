import { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { useToast } from '@/hooks/use-toast';
import { RootState } from '@/store';
import { repayLoan } from '@/store/transactionsSlice';
import { Calculator, CreditCard, AlertTriangle, CheckCircle } from 'lucide-react';
import type { Loan } from '@/types/api';

interface LoanRepaymentFormProps {
  isOpen: boolean;
  onClose: () => void;
  loan?: Loan | null;
}

export function LoanRepaymentForm({ isOpen, onClose, loan }: LoanRepaymentFormProps) {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { loading } = useSelector((state: RootState) => state.transactions);
  const { user } = useSelector((state: RootState) => state.auth);
  
  const [formData, setFormData] = useState({
    amount: '',
    payment_method: 'cash',
    payment_reference: '',
    notes: '',
  });

  const [calculatedDetails, setCalculatedDetails] = useState({
    principalAmount: 0,
    interestAmount: 0,
    penaltyAmount: 0,
    totalAmount: 0,
    remainingBalance: 0,
  });

  useEffect(() => {
    if (loan && formData.amount) {
      calculatePaymentBreakdown();
    }
  }, [loan, formData.amount]);

  const calculatePaymentBreakdown = () => {
    if (!loan || !formData.amount) return;

    const paymentAmount = parseFloat(formData.amount);
    const outstandingBalance = loan.outstanding_balance;
    const monthlyInterestRate = loan.interest_rate / 100 / 12;
    
    // Calculate interest for current month
    const interestAmount = outstandingBalance * monthlyInterestRate;
    
    // Calculate principal (remaining amount after interest)
    const principalAmount = Math.max(0, paymentAmount - interestAmount);
    
    // Calculate penalty if any (simplified - you might want to implement more complex penalty logic)
    const penaltyAmount = 0; // Implement penalty calculation based on your business rules
    
    const remainingBalance = Math.max(0, outstandingBalance - principalAmount);
    
    setCalculatedDetails({
      principalAmount,
      interestAmount,
      penaltyAmount,
      totalAmount: paymentAmount,
      remainingBalance,
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!loan) {
      toast({
        title: "Error",
        description: "No loan selected",
        variant: "destructive",
      });
      return;
    }

    const amount = parseFloat(formData.amount);
    if (!amount || amount <= 0) {
      toast({
        title: "Error",
        description: "Please enter a valid payment amount",
        variant: "destructive",
      });
      return;
    }

    if (amount > loan.outstanding_balance) {
      toast({
        title: "Error",
        description: "Payment amount cannot exceed outstanding balance",
        variant: "destructive",
      });
      return;
    }

    try {
      const result = await dispatch(repayLoan({
        loan_id: loan.id,
        amount,
        payment_method: formData.payment_method,
        notes: formData.notes || `Loan repayment for ${loan.loan_number}`,
      }) as any);

      if (repayLoan.fulfilled.match(result)) {
        toast({
          title: "Success",
          description: `Payment of UGX ${amount.toLocaleString()} processed successfully`,
        });
        setFormData({ amount: '', payment_method: 'cash', payment_reference: '', notes: '' });
        onClose();
      }
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to process loan repayment",
        variant: "destructive",
      });
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const getMinimumPayment = () => {
    if (!loan) return 0;
    return loan.next_payment_amount || (loan.outstanding_balance * 0.1); // 10% of outstanding or next payment amount
  };

  const getRecommendedPayment = () => {
    if (!loan) return 0;
    return loan.next_payment_amount || (loan.outstanding_balance * 0.2); // 20% of outstanding or next payment amount
  };

  if (!loan) {
    return (
      <Dialog open={isOpen} onOpenChange={onClose}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <CreditCard className="w-5 h-5 text-primary" />
              Make Loan Payment
            </DialogTitle>
          </DialogHeader>
          <div className="text-center py-8">
            <AlertTriangle className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
            <h3 className="text-lg font-medium text-muted-foreground mb-2">
              No loan selected
            </h3>
            <p className="text-sm text-muted-foreground mb-4">
              Please select a loan to make a payment.
            </p>
            <Button onClick={onClose}>Close</Button>
          </div>
        </DialogContent>
      </Dialog>
    );
  }

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <CreditCard className="w-5 h-5 text-primary" />
            Make Loan Payment
          </DialogTitle>
        </DialogHeader>

        {/* Loan Information */}
        <div className="bg-muted/50 p-4 rounded-lg mb-4">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <p className="text-sm text-muted-foreground">Loan Number</p>
              <p className="font-semibold">{loan.loan_number}</p>
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
              <p className="text-sm text-muted-foreground">Next Payment Due</p>
              <p className="font-semibold">
                {loan.next_payment_date ? new Date(loan.next_payment_date).toLocaleDateString() : 'N/A'}
              </p>
            </div>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Payment Form */}
            <div className="space-y-4">
              <div>
                <Label htmlFor="amount">Payment Amount (UGX) *</Label>
                <Input
                  id="amount"
                  type="number"
                  min={getMinimumPayment()}
                  max={loan.outstanding_balance}
                  step="1000"
                  value={formData.amount}
                  onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
                  placeholder="Enter payment amount"
                  required
                  disabled={loading}
                />
                <div className="mt-2 flex gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setFormData({ ...formData, amount: getMinimumPayment().toString() })}
                  >
                    Min: {formatCurrency(getMinimumPayment())}
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setFormData({ ...formData, amount: getRecommendedPayment().toString() })}
                  >
                    Recommended: {formatCurrency(getRecommendedPayment())}
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setFormData({ ...formData, amount: loan.outstanding_balance.toString() })}
                  >
                    Full Balance
                  </Button>
                </div>
              </div>

              <div>
                <Label htmlFor="payment_method">Payment Method *</Label>
                <Select 
                  value={formData.payment_method} 
                  onValueChange={(value) => setFormData({ ...formData, payment_method: value })}
                  disabled={loading}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select payment method" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="cash">Cash</SelectItem>
                    <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                    <SelectItem value="mobile_money">Mobile Money</SelectItem>
                    <SelectItem value="cheque">Cheque</SelectItem>
                    <SelectItem value="salary_deduction">Salary Deduction</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <Label htmlFor="payment_reference">Payment Reference (Optional)</Label>
                <Input
                  id="payment_reference"
                  type="text"
                  value={formData.payment_reference}
                  onChange={(e) => setFormData({ ...formData, payment_reference: e.target.value })}
                  placeholder="Enter payment reference/transaction ID"
                  disabled={loading}
                />
              </div>

              <div>
                <Label htmlFor="notes">Notes (Optional)</Label>
                <Textarea
                  id="notes"
                  value={formData.notes}
                  onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                  placeholder="Enter any additional notes"
                  rows={3}
                  disabled={loading}
                />
              </div>
            </div>

            {/* Payment Breakdown */}
            {formData.amount && calculatedDetails.totalAmount > 0 && (
              <div className="space-y-4">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-lg">
                      <Calculator className="w-5 h-5" />
                      Payment Breakdown
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    <div className="flex justify-between">
                      <span className="text-muted-foreground">Payment Amount:</span>
                      <span className="font-medium">{formatCurrency(calculatedDetails.totalAmount)}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-muted-foreground">Principal Payment:</span>
                      <span className="font-medium text-green-600">{formatCurrency(calculatedDetails.principalAmount)}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-muted-foreground">Interest Payment:</span>
                      <span className="font-medium text-blue-600">{formatCurrency(calculatedDetails.interestAmount)}</span>
                    </div>
                    {calculatedDetails.penaltyAmount > 0 && (
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Penalty:</span>
                        <span className="font-medium text-red-600">{formatCurrency(calculatedDetails.penaltyAmount)}</span>
                      </div>
                    )}
                    <div className="border-t pt-3">
                      <div className="flex justify-between">
                        <span className="font-medium">Remaining Balance:</span>
                        <span className="font-bold text-primary">{formatCurrency(calculatedDetails.remainingBalance)}</span>
                      </div>
                    </div>
                  </CardContent>
                </Card>

                {calculatedDetails.remainingBalance === 0 && (
                  <Alert>
                    <CheckCircle className="h-4 w-4" />
                    <AlertDescription>
                      This payment will fully settle your loan balance. Congratulations!
                    </AlertDescription>
                  </Alert>
                )}
              </div>
            )}
          </div>

          {/* Submit Button */}
          <div className="flex gap-3 pt-4">
            <Button 
              type="submit" 
              disabled={loading || !formData.amount || parseFloat(formData.amount) <= 0} 
              className="flex-1"
            >
              {loading ? 'Processing...' : 'Make Payment'}
            </Button>
            <Button type="button" variant="outline" onClick={onClose} disabled={loading}>
              Cancel
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
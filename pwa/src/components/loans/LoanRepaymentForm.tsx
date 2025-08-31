import { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import { useToast } from '@/hooks/use-toast';
import { RootState } from '@/store';
import { repayLoan } from '@/store/loansSlice';
import { CreditCard, DollarSign } from 'lucide-react';
import type { Loan } from '@/types/api';

interface LoanRepaymentFormProps {
  isOpen: boolean;
  onClose: () => void;
  loan?: Loan;
}

export function LoanRepaymentForm({ isOpen, onClose, loan }: LoanRepaymentFormProps) {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { loading } = useSelector((state: RootState) => state.loans);
  
  const [formData, setFormData] = useState({
    amount: '',
    payment_method: 'bank_transfer',
    reference: '',
  });

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
        description: "Please enter a valid amount",
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
        loanId: loan.id,
        repaymentData: {
          amount,
          payment_method: formData.payment_method,
          reference: formData.reference,
        },
      }) as any);

      if (repayLoan.fulfilled.match(result)) {
        toast({
          title: "Success",
          description: `Payment of KES ${amount.toLocaleString()} processed successfully`,
        });
        setFormData({ amount: '', payment_method: 'bank_transfer', reference: '' });
        onClose();
      }
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to process payment",
        variant: "destructive",
      });
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', {
      style: 'currency',
      currency: 'KES',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const suggestedAmounts = loan ? [
    { label: 'Monthly Payment', amount: loan.monthly_payment },
    { label: 'Next Payment', amount: loan.next_payment_amount },
    { label: 'Full Balance', amount: loan.outstanding_balance },
  ] : [];

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <DollarSign className="w-5 h-5 text-green-600" />
            Make Loan Payment
          </DialogTitle>
        </DialogHeader>

        {loan && (
          <div className="bg-muted/50 p-4 rounded-lg mb-4">
            <div className="flex items-center gap-2 mb-2">
              <CreditCard className="w-4 h-4" />
              <span className="font-medium">{loan.loan_number}</span>
            </div>
            <p className="text-sm text-muted-foreground">
              {loan.product_name}
            </p>
            <div className="grid grid-cols-2 gap-4 mt-3">
              <div>
                <p className="text-xs text-muted-foreground">Outstanding Balance</p>
                <p className="font-semibold">{formatCurrency(loan.outstanding_balance)}</p>
              </div>
              <div>
                <p className="text-xs text-muted-foreground">Monthly Payment</p>
                <p className="font-semibold">{formatCurrency(loan.monthly_payment)}</p>
              </div>
            </div>
            {loan.next_payment_date && (
              <div className="mt-2">
                <p className="text-xs text-muted-foreground">Next Payment Due</p>
                <p className="font-semibold">
                  {new Date(loan.next_payment_date).toLocaleDateString()} - {formatCurrency(loan.next_payment_amount)}
                </p>
              </div>
            )}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <Label htmlFor="amount">Payment Amount (KES) *</Label>
            <Input
              id="amount"
              type="number"
              min="1"
              step="0.01"
              max={loan?.outstanding_balance}
              value={formData.amount}
              onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
              placeholder="Enter payment amount"
              required
              disabled={loading}
            />
            
            {suggestedAmounts.length > 0 && (
              <div className="flex gap-2 mt-2">
                {suggestedAmounts.map((suggestion) => (
                  <Button
                    key={suggestion.label}
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setFormData({ ...formData, amount: suggestion.amount.toString() })}
                    disabled={loading}
                  >
                    {suggestion.label}
                  </Button>
                ))}
              </div>
            )}
          </div>

          <div>
            <Label htmlFor="payment_method">Payment Method</Label>
            <Select 
              value={formData.payment_method} 
              onValueChange={(value) => setFormData({ ...formData, payment_method: value })}
              disabled={loading}
            >
              <SelectTrigger>
                <SelectValue placeholder="Select payment method" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                <SelectItem value="mobile_money">Mobile Money</SelectItem>
                <SelectItem value="cash">Cash</SelectItem>
                <SelectItem value="cheque">Cheque</SelectItem>
                <SelectItem value="deduction">Salary Deduction</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label htmlFor="reference">Reference/Transaction ID (Optional)</Label>
            <Input
              id="reference"
              type="text"
              value={formData.reference}
              onChange={(e) => setFormData({ ...formData, reference: e.target.value })}
              placeholder="Enter payment reference"
              disabled={loading}
            />
          </div>

          <div className="flex gap-3 pt-4">
            <Button type="submit" disabled={loading} className="flex-1">
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
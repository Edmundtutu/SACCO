import { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { repayLoanFromWallet, resetTransactionSuccess, fetchWalletBalance } from '@/store/walletSlice';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle, CheckCircle2, Wallet, CreditCard } from 'lucide-react';
import { Badge } from '@/components/ui/badge';

interface WalletLoanPaymentFormProps {
  isOpen: boolean;
  onClose: () => void;
  walletAccountId: number;
  memberId: number;
  loanId: number;
  loanNumber: string;
  outstandingBalance: number;
  onSuccess?: () => void;
}

export function WalletLoanPaymentForm({ 
  isOpen, 
  onClose, 
  walletAccountId, 
  memberId, 
  loanId,
  loanNumber,
  outstandingBalance,
  onSuccess 
}: WalletLoanPaymentFormProps) {
  const dispatch = useDispatch<AppDispatch>();
  const { balance, loading, error, lastTransactionSuccess } = useSelector((state: RootState) => state.wallet);

  const [amount, setAmount] = useState('');
  const [description, setDescription] = useState('');

  useEffect(() => {
    if (isOpen && walletAccountId) {
      dispatch(fetchWalletBalance(walletAccountId));
    }
  }, [isOpen, walletAccountId, dispatch]);

  useEffect(() => {
    if (lastTransactionSuccess) {
      setTimeout(() => {
        handleClose();
        dispatch(resetTransactionSuccess());
        if (onSuccess) onSuccess();
      }, 2000);
    }
  }, [lastTransactionSuccess]);

  const handleClose = () => {
    setAmount('');
    setDescription('');
    onClose();
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!amount || parseFloat(amount) <= 0) {
      return;
    }

    if (!balance || parseFloat(amount) > balance.balance) {
      return;
    }

    await dispatch(repayLoanFromWallet({
      member_id: memberId,
      account_id: walletAccountId,
      loan_id: loanId,
      amount: parseFloat(amount),
      description: description || `Loan repayment for ${loanNumber}`,
    }));
  };

  const formatCurrency = (value: number | string) => {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return isNaN(num) ? '0' : num.toLocaleString();
  };

  const walletBalance = balance?.balance || 0;
  const maxPayment = Math.min(walletBalance, outstandingBalance);
  const isAmountValid = amount && parseFloat(amount) >= 500 && parseFloat(amount) <= maxPayment;

  const handlePayFull = () => {
    setAmount(maxPayment.toString());
  };

  return (
    <Dialog open={isOpen} onOpenChange={handleClose}>
      <DialogContent className="sm:max-w-[550px]">
        <DialogHeader>
          <div className="flex items-center gap-3 mb-2">
            <div className="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <Wallet className="h-5 w-5 text-blue-600" />
            </div>
            <div>
              <DialogTitle>Pay Loan with Wallet</DialogTitle>
              <DialogDescription>Make a loan payment from your wallet balance</DialogDescription>
            </div>
          </div>
        </DialogHeader>

        {lastTransactionSuccess && (
          <Alert className="bg-green-50 border-green-200">
            <CheckCircle2 className="h-4 w-4 text-green-600" />
            <AlertDescription className="text-green-800">
              Payment successful! Your loan balance has been updated.
            </AlertDescription>
          </Alert>
        )}

        {error && (
          <Alert variant="destructive">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        )}

        {/* Loan Details */}
        <div className="bg-muted/50 p-4 rounded-lg space-y-3">
          <div className="flex justify-between items-center">
            <span className="text-sm text-muted-foreground">Loan Number</span>
            <span className="font-mono font-medium">{loanNumber}</span>
          </div>
          <div className="flex justify-between items-center">
            <span className="text-sm text-muted-foreground">Outstanding Balance</span>
            <span className="font-bold text-red-600">UGX {formatCurrency(outstandingBalance)}</span>
          </div>
          <div className="border-t pt-3">
            <div className="flex justify-between items-center">
              <span className="text-sm text-muted-foreground">Wallet Balance</span>
              <span className="font-bold text-green-600">UGX {formatCurrency(walletBalance)}</span>
            </div>
            {walletBalance < outstandingBalance && (
              <p className="text-xs text-orange-600 mt-1">
                Wallet balance insufficient for full payment
              </p>
            )}
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <Label htmlFor="amount">Payment Amount (UGX)</Label>
              <Button 
                type="button" 
                variant="link" 
                size="sm" 
                onClick={handlePayFull}
                disabled={loading || lastTransactionSuccess || maxPayment <= 0}
                className="h-auto p-0"
              >
                Pay Maximum
              </Button>
            </div>
            <Input
              id="amount"
              type="number"
              placeholder="Enter amount"
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              min="500"
              max={maxPayment}
              step="100"
              required
              disabled={loading || lastTransactionSuccess}
            />
            {amount && parseFloat(amount) >= 500 && (
              <p className="text-sm text-muted-foreground">
                UGX {formatCurrency(amount)}
              </p>
            )}
            {amount && parseFloat(amount) > walletBalance && (
              <p className="text-sm text-red-600">Amount exceeds wallet balance</p>
            )}
            {amount && parseFloat(amount) > outstandingBalance && (
              <p className="text-sm text-orange-600">Amount exceeds loan balance</p>
            )}
            <p className="text-xs text-muted-foreground">
              Maximum: UGX {formatCurrency(maxPayment)}
            </p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="description">Description (Optional)</Label>
            <Textarea
              id="description"
              placeholder="Enter payment note"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              rows={2}
              disabled={loading || lastTransactionSuccess}
            />
          </div>

          {walletBalance <= 0 && (
            <Alert>
              <AlertCircle className="h-4 w-4" />
              <AlertDescription>
                Your wallet balance is zero. Please top up your wallet before making a payment.
              </AlertDescription>
            </Alert>
          )}

          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              onClick={handleClose}
              disabled={loading || lastTransactionSuccess}
            >
              Cancel
            </Button>
            <Button 
              type="submit" 
              disabled={loading || lastTransactionSuccess || !isAmountValid || walletBalance <= 0}
              className="bg-blue-600 hover:bg-blue-700"
            >
              <CreditCard className="w-4 h-4 mr-2" />
              {loading ? 'Processing...' : 'Pay Loan'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}

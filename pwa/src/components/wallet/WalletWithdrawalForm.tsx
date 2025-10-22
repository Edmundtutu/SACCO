import { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { withdrawFromWallet, resetTransactionSuccess } from '@/store/walletSlice';
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
import { AlertCircle, CheckCircle2, ArrowDownLeft } from 'lucide-react';

interface WalletWithdrawalFormProps {
  isOpen: boolean;
  onClose: () => void;
  walletAccountId: number;
  memberId: number;
  currentBalance: number;
}

export function WalletWithdrawalForm({ isOpen, onClose, walletAccountId, memberId, currentBalance }: WalletWithdrawalFormProps) {
  const dispatch = useDispatch<AppDispatch>();
  const { loading, error, lastTransactionSuccess } = useSelector((state: RootState) => state.wallet);

  const [amount, setAmount] = useState('');
  const [description, setDescription] = useState('');

  useEffect(() => {
    if (lastTransactionSuccess) {
      setTimeout(() => {
        handleClose();
        dispatch(resetTransactionSuccess());
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

    if (!amount || parseFloat(amount) <= 0 || parseFloat(amount) > currentBalance) {
      return;
    }

    await dispatch(withdrawFromWallet({
      member_id: memberId,
      account_id: walletAccountId,
      amount: parseFloat(amount),
      description: description || 'Cash withdrawal from wallet',
    }));
  };

  const formatCurrency = (value: number | string) => {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return isNaN(num) ? '0' : num.toLocaleString();
  };

  const isAmountValid = amount && parseFloat(amount) >= 500 && parseFloat(amount) <= currentBalance;

  return (
    <Dialog open={isOpen} onOpenChange={handleClose}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <div className="flex items-center gap-3 mb-2">
            <div className="h-10 w-10 bg-orange-100 rounded-lg flex items-center justify-center">
              <ArrowDownLeft className="h-5 w-5 text-orange-600" />
            </div>
            <div>
              <DialogTitle>Withdraw from Wallet</DialogTitle>
              <DialogDescription>Cash out from your wallet balance</DialogDescription>
            </div>
          </div>
        </DialogHeader>

        {lastTransactionSuccess && (
          <Alert className="bg-green-50 border-green-200">
            <CheckCircle2 className="h-4 w-4 text-green-600" />
            <AlertDescription className="text-green-800">
              Withdrawal successful! Please collect your cash.
            </AlertDescription>
          </Alert>
        )}

        {error && (
          <Alert variant="destructive">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        )}

        <div className="bg-muted/50 p-3 rounded-lg mb-4">
          <p className="text-sm text-muted-foreground">Available Balance</p>
          <p className="text-2xl font-bold text-primary">UGX {formatCurrency(currentBalance)}</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="amount">Amount (UGX)</Label>
            <Input
              id="amount"
              type="number"
              placeholder="Enter amount"
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              min="500"
              max={currentBalance}
              step="100"
              required
              disabled={loading || lastTransactionSuccess}
            />
            {amount && parseFloat(amount) >= 500 && (
              <p className="text-sm text-muted-foreground">
                UGX {formatCurrency(amount)}
              </p>
            )}
            {amount && parseFloat(amount) > currentBalance && (
              <p className="text-sm text-red-600">Insufficient balance</p>
            )}
            <p className="text-xs text-muted-foreground">Minimum withdrawal: UGX 500</p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="description">Description (Optional)</Label>
            <Textarea
              id="description"
              placeholder="Enter transaction description"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              rows={2}
              disabled={loading || lastTransactionSuccess}
            />
          </div>

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
              disabled={loading || lastTransactionSuccess || !isAmountValid}
              className="bg-orange-600 hover:bg-orange-700"
            >
              {loading ? 'Processing...' : 'Withdraw Cash'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}

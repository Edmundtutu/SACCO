import { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { topupWallet, resetTransactionSuccess } from '@/store/walletSlice';
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
import { AlertCircle, CheckCircle2, Wallet } from 'lucide-react';

interface WalletTopupFormProps {
  isOpen: boolean;
  onClose: () => void;
  walletAccountId: number;
  memberId: number;
}

export function WalletTopupForm({ isOpen, onClose, walletAccountId, memberId }: WalletTopupFormProps) {
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

    if (!amount || parseFloat(amount) <= 0) {
      return;
    }

    await dispatch(topupWallet({
      member_id: memberId,
      account_id: walletAccountId,
      amount: parseFloat(amount),
      description: description || 'Cash top-up to wallet',
    }));
  };

  const formatCurrency = (value: string) => {
    const num = parseFloat(value);
    return isNaN(num) ? '0' : num.toLocaleString();
  };

  return (
    <Dialog open={isOpen} onOpenChange={handleClose}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <div className="flex items-center gap-3 mb-2">
            <div className="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
              <Wallet className="h-5 w-5 text-green-600" />
            </div>
            <div>
              <DialogTitle>Top-up Wallet</DialogTitle>
              <DialogDescription>Add cash to your wallet balance</DialogDescription>
            </div>
          </div>
        </DialogHeader>

        {lastTransactionSuccess && (
          <Alert className="bg-green-50 border-green-200">
            <CheckCircle2 className="h-4 w-4 text-green-600" />
            <AlertDescription className="text-green-800">
              Wallet topped up successfully! Balance updated.
            </AlertDescription>
          </Alert>
        )}

        {error && (
          <Alert variant="destructive">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        )}

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
              step="100"
              required
              disabled={loading || lastTransactionSuccess}
            />
            {amount && parseFloat(amount) >= 500 && (
              <p className="text-sm text-muted-foreground">
                UGX {formatCurrency(amount)}
              </p>
            )}
            <p className="text-xs text-muted-foreground">Minimum top-up: UGX 500</p>
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
              disabled={loading || lastTransactionSuccess || !amount || parseFloat(amount) < 500}
              className="bg-green-600 hover:bg-green-700"
            >
              {loading ? 'Processing...' : 'Top-up Wallet'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}

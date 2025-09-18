import { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/hooks/use-toast';
import { RootState } from '@/store';
import { makeDeposit } from '@/store/transactionsSlice';
import { ArrowUpRight, CreditCard } from 'lucide-react';
import type { SavingsAccount } from '@/types/api';

interface DepositFormProps {
  isOpen: boolean;
  onClose: () => void;
  account?: SavingsAccount;
}

export function DepositForm({ isOpen, onClose, account }: DepositFormProps) {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { loading } = useSelector((state: RootState) => state.transactions);
  const { user } = useSelector((state: RootState) => state.auth);
  
  const [formData, setFormData] = useState({
    amount: '',
    payment_method: 'cash',
    payment_reference: '',
    description: '',
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!account) {
      toast({
        title: "Error",
        description: "No account selected",
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

    try {
      const result = await dispatch(makeDeposit({
        member_id: user?.id || 0,
        account_id: account.id,
        amount,
        description: formData.description || `Deposit to ${account.account_number}`,
        payment_reference: formData.payment_reference,
        metadata: {
          payment_method: formData.payment_method,
        },
      }) as any);

      if (makeDeposit.fulfilled.match(result)) {
        toast({
          title: "Success",
          description: `Deposit of KES ${amount.toLocaleString()} successful`,
        });
        setFormData({ amount: '', payment_method: 'cash', payment_reference: '', description: '' });
        onClose();
      }
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to make deposit",
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

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <ArrowUpRight className="w-5 h-5 text-green-600" />
            Make Deposit
          </DialogTitle>
        </DialogHeader>

        {account && (
          <div className="bg-muted/50 p-4 rounded-lg mb-4">
            <div className="flex items-center gap-2 mb-2">
              <CreditCard className="w-4 h-4" />
              <span className="font-medium">{account.account_number}</span>
            </div>
            <p className="text-sm text-muted-foreground">
              {account.savings_product.name}
            </p>
            <p className="text-lg font-semibold">
              Current Balance: {formatCurrency(account.balance)}
            </p>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <Label htmlFor="amount">Amount (KES) *</Label>
            <Input
              id="amount"
              type="number"
              min="1"
              step="0.01"
              value={formData.amount}
              onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
              placeholder="Enter deposit amount"
              required
              disabled={loading}
            />
            {account?.savings_product.minimum_deposit && (
              <p className="text-xs text-muted-foreground mt-1">
                Minimum deposit: {formatCurrency(account.savings_product.minimum_deposit)}
              </p>
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
                <SelectItem value="cash">Cash</SelectItem>
                <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                <SelectItem value="mobile_money">Mobile Money</SelectItem>
                <SelectItem value="cheque">Cheque</SelectItem>
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
            <Label htmlFor="description">Description (Optional)</Label>
            <Textarea
              id="description"
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              placeholder="Enter description for this deposit"
              rows={3}
              disabled={loading}
            />
          </div>

          <div className="flex gap-3 pt-4">
            <Button type="submit" disabled={loading} className="flex-1">
              {loading ? 'Processing...' : 'Make Deposit'}
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
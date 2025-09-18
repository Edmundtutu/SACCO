import { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { purchaseShares } from '@/store/transactionsSlice';
import { useToast } from '@/hooks/use-toast';
import { RootState } from '@/store';
import { ShoppingCart, Calculator, TrendingUp } from 'lucide-react';

interface SharesPurchaseProps {
  currentShares: number;
  shareValue: number;
}

export function SharesPurchase({ currentShares, shareValue }: SharesPurchaseProps) {
  const dispatch = useDispatch<AppDispatch>();
  const { toast } = useToast();
  const { loading } = useSelector((state: RootState) => state.transactions);
  const { user } = useSelector((state: RootState) => state.auth);
  
  const [amount, setAmount] = useState('');
  const [shares, setShares] = useState('');
  const [paymentMethod, setPaymentMethod] = useState('bank_transfer');
  const [calculationMode, setCalculationMode] = useState<'amount' | 'shares'>('amount');

  const handleAmountChange = (value: string) => {
    setAmount(value);
    if (value && shareValue > 0) {
      const calculatedShares = Math.floor(parseFloat(value) / shareValue);
      setShares(calculatedShares.toString());
    } else {
      setShares('');
    }
    setCalculationMode('amount');
  };

  const handleSharesChange = (value: string) => {
    setShares(value);
    if (value && shareValue > 0) {
      const calculatedAmount = parseFloat(value) * shareValue;
      setAmount(calculatedAmount.toString());
    } else {
      setAmount('');
    }
    setCalculationMode('shares');
  };

  const handlePurchase = async () => {
    if (!amount || !shares) {
      toast({
        title: "Error",
        description: "Please enter amount or number of shares",
        variant: "destructive",
      });
      return;
    }

    try {
      const result = await dispatch(purchaseShares({
        member_id: user?.id || 0,
        amount: parseFloat(amount),
        description: `Purchase of ${shares} shares at ${shareValue} per share`,
      }) as any);
      
      if (purchaseShares.fulfilled.match(result)) {
        toast({
          title: "Success",
          description: `Successfully purchased ${shares} shares`,
        });
        
        setAmount('');
        setShares('');
      }
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to purchase shares",
        variant: "destructive",
      });
    }
  };

  const totalValue = amount ? parseFloat(amount) : 0;
  const totalShares = shares ? parseInt(shares) : 0;
  const newTotalShares = currentShares + totalShares;

  return (
    <div className="grid gap-6 md:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <ShoppingCart className="w-5 h-5 text-primary" />
            Buy Shares
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="p-4 bg-muted/50 rounded-lg">
            <div className="text-center">
              <p className="text-sm text-muted-foreground">Current Share Price</p>
              <p className="text-2xl font-bold text-primary">
                UGX {shareValue.toLocaleString()}
              </p>
              <p className="text-xs text-muted-foreground">per share</p>
            </div>
          </div>

          <div className="space-y-4">
            <div>
              <Label htmlFor="amount">Investment Amount (UGX)</Label>
              <Input
                id="amount"
                type="number"
                placeholder="0"
                value={amount}
                onChange={(e) => handleAmountChange(e.target.value)}
                className={calculationMode === 'amount' ? 'border-primary' : ''}
              />
            </div>

            <div className="text-center">
              <span className="text-sm text-muted-foreground">OR</span>
            </div>

            <div>
              <Label htmlFor="shares">Number of Shares</Label>
              <Input
                id="shares"
                type="number"
                placeholder="0"
                value={shares}
                onChange={(e) => handleSharesChange(e.target.value)}
                className={calculationMode === 'shares' ? 'border-primary' : ''}
                disabled={loading}
              />
            </div>

            <div>
              <Label htmlFor="payment_method">Payment Method</Label>
              <Select 
                value={paymentMethod} 
                onValueChange={setPaymentMethod}
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
          </div>

          <Button 
            onClick={handlePurchase}
            disabled={loading || !amount || !shares}
            className="w-full"
            size="lg"
          >
            {loading ? 'Processing...' : `Buy ${shares || 0} Shares`}
          </Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calculator className="w-5 h-5" />
            Purchase Summary
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-3">
            <div className="flex justify-between">
              <span className="text-muted-foreground">Current Shares:</span>
              <span className="font-medium">{currentShares.toLocaleString()}</span>
            </div>
            
            <div className="flex justify-between">
              <span className="text-muted-foreground">Shares to Buy:</span>
              <span className="font-medium text-primary">+{totalShares.toLocaleString()}</span>
            </div>
            
            <div className="flex justify-between border-t pt-3">
              <span className="font-medium">New Total:</span>
              <span className="font-bold text-success">{newTotalShares.toLocaleString()}</span>
            </div>
          </div>

          <div className="p-4 bg-primary/10 rounded-lg">
            <div className="text-center">
              <p className="text-sm text-muted-foreground">Investment Amount</p>
              <p className="text-xl font-bold text-primary">
                UGX {totalValue.toLocaleString()}
              </p>
            </div>
          </div>

          <div className="space-y-2">
            <h4 className="font-medium text-sm">Benefits of Shareholding:</h4>
            <ul className="text-xs text-muted-foreground space-y-1">
              <li className="flex items-center gap-2">
                <TrendingUp className="w-3 h-3 text-success" />
                Earn annual dividends
              </li>
              <li className="flex items-center gap-2">
                <TrendingUp className="w-3 h-3 text-success" />
                Voting rights in AGM
              </li>
              <li className="flex items-center gap-2">
                <TrendingUp className="w-3 h-3 text-success" />
                Share in SACCO growth
              </li>
            </ul>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
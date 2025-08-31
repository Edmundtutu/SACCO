import { useState } from 'react';
import { useDispatch } from 'react-redux';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { purchaseShares } from '@/store/sharesSlice';
import { useToast } from '@/hooks/use-toast';
import { ShoppingCart, Calculator, TrendingUp } from 'lucide-react';

interface SharesPurchaseProps {
  currentShares: number;
  shareValue: number;
}

export function SharesPurchase({ currentShares, shareValue }: SharesPurchaseProps) {
  const dispatch = useDispatch();
  const { toast } = useToast();
  
  const [amount, setAmount] = useState('');
  const [shares, setShares] = useState('');
  const [calculationMode, setCalculationMode] = useState<'amount' | 'shares'>('amount');
  const [loading, setLoading] = useState(false);

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

    setLoading(true);
    
    try {
      await dispatch(purchaseShares({
        amount: parseFloat(amount),
        shares: parseInt(shares),
      }) as any);
      
      toast({
        title: "Success",
        description: `Successfully purchased ${shares} shares`,
      });
      
      setAmount('');
      setShares('');
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to purchase shares",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
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
                KES {shareValue.toLocaleString()}
              </p>
              <p className="text-xs text-muted-foreground">per share</p>
            </div>
          </div>

          <div className="space-y-4">
            <div>
              <Label htmlFor="amount">Investment Amount (KES)</Label>
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
              />
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
                KES {totalValue.toLocaleString()}
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
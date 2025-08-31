import { useState } from 'react';
import { useDispatch } from 'react-redux';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import { applyForLoan } from '@/store/loansSlice';
import { useToast } from '@/hooks/use-toast';
import { Calculator } from 'lucide-react';

interface LoanProduct {
  id: number;
  name: string;
  description: string;
  max_amount: number;
  interest_rate: number;
  max_term_months: number;
  requirements: string[];
}

interface LoanApplicationProps {
  products: LoanProduct[];
  selectedProductId: number | null;
  onClose: () => void;
}

export function LoanApplication({ products, selectedProductId, onClose }: LoanApplicationProps) {
  const dispatch = useDispatch();
  const { toast } = useToast();
  
  const [formData, setFormData] = useState({
    product_id: selectedProductId || '',
    amount: '',
    term_months: '',
    purpose: '',
  });
  
  const [loading, setLoading] = useState(false);

  const selectedProduct = products.find(p => p.id === Number(formData.product_id));
  
  const calculateMonthlyPayment = () => {
    if (!selectedProduct || !formData.amount || !formData.term_months) return 0;
    
    const principal = parseFloat(formData.amount);
    const monthlyRate = selectedProduct.interest_rate / 100 / 12;
    const months = parseInt(formData.term_months);
    
    if (monthlyRate === 0) return principal / months;
    
    const monthlyPayment = principal * 
      (monthlyRate * Math.pow(1 + monthlyRate, months)) /
      (Math.pow(1 + monthlyRate, months) - 1);
    
    return monthlyPayment;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.product_id || !formData.amount || !formData.term_months || !formData.purpose) {
      toast({
        title: "Error",
        description: "Please fill in all required fields",
        variant: "destructive",
      });
      return;
    }

    setLoading(true);
    
    try {
      await dispatch(applyForLoan({
        product_id: parseInt(formData.product_id),
        amount: parseFloat(formData.amount),
        term_months: parseInt(formData.term_months),
        purpose: formData.purpose,
      }) as any);
      
      toast({
        title: "Success",
        description: "Loan application submitted successfully",
      });
      
      onClose();
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to submit loan application",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={true} onOpenChange={onClose}>
      <DialogContent className="max-w-2xl max-h-[80vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Apply for Loan</DialogTitle>
        </DialogHeader>
        
        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <Label htmlFor="product">Loan Product *</Label>
            <Select 
              value={formData.product_id.toString()} 
              onValueChange={(value) => setFormData({ ...formData, product_id: value })}
            >
              <SelectTrigger>
                <SelectValue placeholder="Select loan product" />
              </SelectTrigger>
              <SelectContent>
                {products.map((product) => (
                  <SelectItem key={product.id} value={product.id.toString()}>
                    {product.name} - {product.interest_rate}% p.a.
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="amount">Loan Amount (KES) *</Label>
              <Input
                id="amount"
                type="number"
                placeholder="0"
                value={formData.amount}
                onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
                max={selectedProduct?.max_amount}
              />
              {selectedProduct && (
                <p className="text-xs text-muted-foreground mt-1">
                  Max: KES {selectedProduct.max_amount.toLocaleString()}
                </p>
              )}
            </div>
            
            <div>
              <Label htmlFor="term">Repayment Period (Months) *</Label>
              <Input
                id="term"
                type="number"
                placeholder="12"
                value={formData.term_months}
                onChange={(e) => setFormData({ ...formData, term_months: e.target.value })}
                max={selectedProduct?.max_term_months}
              />
              {selectedProduct && (
                <p className="text-xs text-muted-foreground mt-1">
                  Max: {selectedProduct.max_term_months} months
                </p>
              )}
            </div>
          </div>

          <div>
            <Label htmlFor="purpose">Purpose of Loan *</Label>
            <Textarea
              id="purpose"
              placeholder="Describe how you plan to use this loan..."
              value={formData.purpose}
              onChange={(e) => setFormData({ ...formData, purpose: e.target.value })}
              rows={3}
            />
          </div>

          {/* Payment Calculator */}
          {formData.amount && formData.term_months && selectedProduct && (
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center gap-2 mb-4">
                  <Calculator className="w-5 h-5 text-primary" />
                  <h3 className="font-medium">Payment Calculation</h3>
                </div>
                
                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <p className="text-muted-foreground">Loan Amount:</p>
                    <p className="font-medium">KES {parseFloat(formData.amount).toLocaleString()}</p>
                  </div>
                  <div>
                    <p className="text-muted-foreground">Interest Rate:</p>
                    <p className="font-medium">{selectedProduct.interest_rate}% p.a.</p>
                  </div>
                  <div>
                    <p className="text-muted-foreground">Repayment Period:</p>
                    <p className="font-medium">{formData.term_months} months</p>
                  </div>
                  <div>
                    <p className="text-muted-foreground">Monthly Payment:</p>
                    <p className="font-bold text-primary">
                      KES {calculateMonthlyPayment().toLocaleString()}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}

          <div className="flex gap-3">
            <Button type="button" variant="outline" onClick={onClose} className="flex-1">
              Cancel
            </Button>
            <Button type="submit" disabled={loading} className="flex-1">
              {loading ? 'Submitting...' : 'Submit Application'}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
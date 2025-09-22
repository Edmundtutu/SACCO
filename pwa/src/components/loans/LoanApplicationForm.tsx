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
import { applyForLoan } from '@/store/loansSlice';
import { Calculator, FileText, AlertTriangle, CheckCircle } from 'lucide-react';
import type { LoanProduct } from '@/types/api';

interface LoanApplicationFormProps {
  isOpen: boolean;
  onClose: () => void;
}

export function LoanApplicationForm({ isOpen, onClose }: LoanApplicationFormProps) {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { loading } = useSelector((state: RootState) => state.loans);
  const { user } = useSelector((state: RootState) => (state.auth as any));
  const { products: loanProducts } = useSelector((state: RootState) => state.loans);
  
  const [formData, setFormData] = useState({
    loan_product_id: '',
    principal_amount: '',
    repayment_period_months: '',
    purpose: '',
    employment_status: '',
    monthly_income: '',
    employer_name: '',
    employer_phone: '',
    guarantor1_name: '',
    guarantor1_phone: '',
    guarantor1_relationship: '',
    guarantor1_employment: '',
    guarantor2_name: '',
    guarantor2_phone: '',
    guarantor2_relationship: '',
    guarantor2_employment: '',
    additional_info: '',
  });

  const [selectedProduct, setSelectedProduct] = useState<LoanProduct | null>(null);
  const [calculatedDetails, setCalculatedDetails] = useState({
    monthlyPayment: 0,
    totalInterest: 0,
    totalAmount: 0,
    interestRate: 0,
  });

  // Calculate loan details when product or amount changes
  useEffect(() => {
    if (selectedProduct && formData.principal_amount && formData.repayment_period_months) {
      const principal = parseFloat(formData.principal_amount);
      const months = parseInt(formData.repayment_period_months);
      const rate = selectedProduct.interest_rate / 100 / 12; // Monthly rate
      
      if (rate > 0) {
        const monthlyPayment = (principal * rate * Math.pow(1 + rate, months)) / (Math.pow(1 + rate, months) - 1);
        const totalAmount = monthlyPayment * months;
        const totalInterest = totalAmount - principal;
        
        setCalculatedDetails({
          monthlyPayment,
          totalInterest,
          totalAmount,
          interestRate: selectedProduct.interest_rate,
        });
      }
    }
  }, [selectedProduct, formData.principal_amount, formData.repayment_period_months]);

  const handleProductChange = (productId: string) => {
    const product = loanProducts.find(p => p.id.toString() === productId);
    setSelectedProduct(product || null);
    setFormData({ ...formData, loan_product_id: productId });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!selectedProduct) {
      toast({
        title: "Error",
        description: "Please select a loan product",
        variant: "destructive",
      });
      return;
    }

    const principalAmount = parseFloat(formData.principal_amount);
    if (!principalAmount || principalAmount < selectedProduct.minimum_amount || principalAmount > selectedProduct.maximum_amount) {
      toast({
        title: "Error",
        description: `Loan amount must be between UGX ${selectedProduct.minimum_amount.toLocaleString()} and UGX ${selectedProduct.maximum_amount.toLocaleString()}`,
        variant: "destructive",
      });
      return;
    }

    try {
      const result = await dispatch(applyForLoan({
        loan_product_id: parseInt(formData.loan_product_id),
        principal_amount: principalAmount,
        repayment_period_months: parseInt(formData.repayment_period_months),
        purpose: formData.purpose,
        collateral_description: formData.additional_info,
      }) as any);

      if (applyForLoan.fulfilled.match(result)) {
        toast({
          title: "Success",
          description: "Loan application submitted successfully. You will be notified of the status.",
        });
        onClose();
        // Reset form
        setFormData({
          loan_product_id: '',
          principal_amount: '',
          repayment_period_months: '',
          purpose: '',
          employment_status: '',
          monthly_income: '',
          employer_name: '',
          employer_phone: '',
          guarantor1_name: '',
          guarantor1_phone: '',
          guarantor1_relationship: '',
          guarantor1_employment: '',
          guarantor2_name: '',
          guarantor2_phone: '',
          guarantor2_relationship: '',
          guarantor2_employment: '',
          additional_info: '',
        });
        setSelectedProduct(null);
      }
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to submit loan application",
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

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <FileText className="w-5 h-5 text-primary" />
            Apply for Loan
          </DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Left Column */}
            <div className="space-y-4">
              {/* Loan Product Selection */}
              <div>
                <Label htmlFor="loan_product_id">Loan Product *</Label>
                <Select 
                  value={formData.loan_product_id} 
                  onValueChange={handleProductChange}
                  disabled={loading}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select loan product" />
                  </SelectTrigger>
                  <SelectContent>
                    {loanProducts.map((product) => (
                      <SelectItem key={product.id} value={product.id.toString()}>
                        {product.name} - {product.interest_rate}% p.a.
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {selectedProduct && (
                  <div className="mt-2 p-3 bg-muted/50 rounded-lg">
                    <div className="text-sm space-y-1">
                      <p><strong>Interest Rate:</strong> {selectedProduct.interest_rate}% per annum</p>
                      <p><strong>Min Amount:</strong> {formatCurrency(selectedProduct.minimum_amount)}</p>
                      <p><strong>Max Amount:</strong> {formatCurrency(selectedProduct.maximum_amount)}</p>
                      <p><strong>Max Period:</strong> {selectedProduct.maximum_period_months} months</p>
                    </div>
                  </div>
                )}
              </div>

              {/* Loan Amount */}
              <div>
                <Label htmlFor="principal_amount">Loan Amount (UGX) *</Label>
                <Input
                  id="principal_amount"
                  type="number"
                  min={selectedProduct?.minimum_amount || 0}
                  max={selectedProduct?.maximum_amount || 10000000}
                  step="1000"
                  value={formData.principal_amount}
                  onChange={(e) => setFormData({ ...formData, principal_amount: e.target.value })}
                  placeholder="Enter loan amount"
                  required
                  disabled={loading}
                />
              </div>

              {/* Repayment Period */}
              <div>
                <Label htmlFor="repayment_period_months">Repayment Period (Months) *</Label>
                <Input
                  id="repayment_period_months"
                  type="number"
                  min="1"
                  max={selectedProduct?.maximum_period_months || 60}
                  value={formData.repayment_period_months}
                  onChange={(e) => setFormData({ ...formData, repayment_period_months: e.target.value })}
                  placeholder="Enter repayment period"
                  required
                  disabled={loading}
                />
              </div>

              {/* Purpose */}
              <div>
                <Label htmlFor="purpose">Loan Purpose *</Label>
                <Select 
                  value={formData.purpose} 
                  onValueChange={(value) => setFormData({ ...formData, purpose: value })}
                  disabled={loading}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select loan purpose" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="business">Business Investment</SelectItem>
                    <SelectItem value="education">Education</SelectItem>
                    <SelectItem value="medical">Medical Expenses</SelectItem>
                    <SelectItem value="home_improvement">Home Improvement</SelectItem>
                    <SelectItem value="agriculture">Agriculture</SelectItem>
                    <SelectItem value="vehicle">Vehicle Purchase</SelectItem>
                    <SelectItem value="other">Other</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Employment Information */}
              <div>
                <Label htmlFor="employment_status">Employment Status *</Label>
                <Select 
                  value={formData.employment_status} 
                  onValueChange={(value) => setFormData({ ...formData, employment_status: value })}
                  disabled={loading}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select employment status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="employed">Employed</SelectItem>
                    <SelectItem value="self_employed">Self Employed</SelectItem>
                    <SelectItem value="business_owner">Business Owner</SelectItem>
                    <SelectItem value="retired">Retired</SelectItem>
                    <SelectItem value="unemployed">Unemployed</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <Label htmlFor="monthly_income">Monthly Income (UGX) *</Label>
                <Input
                  id="monthly_income"
                  type="number"
                  min="0"
                  step="1000"
                  value={formData.monthly_income}
                  onChange={(e) => setFormData({ ...formData, monthly_income: e.target.value })}
                  placeholder="Enter monthly income"
                  required
                  disabled={loading}
                />
              </div>

              <div>
                <Label htmlFor="employer_name">Employer/Business Name</Label>
                <Input
                  id="employer_name"
                  type="text"
                  value={formData.employer_name}
                  onChange={(e) => setFormData({ ...formData, employer_name: e.target.value })}
                  placeholder="Enter employer or business name"
                  disabled={loading}
                />
              </div>

              <div>
                <Label htmlFor="employer_phone">Employer Phone</Label>
                <Input
                  id="employer_phone"
                  type="tel"
                  value={formData.employer_phone}
                  onChange={(e) => setFormData({ ...formData, employer_phone: e.target.value })}
                  placeholder="Enter employer phone number"
                  disabled={loading}
                />
              </div>
            </div>

            {/* Right Column */}
            <div className="space-y-4">
              {/* Loan Calculation */}
              {calculatedDetails.monthlyPayment > 0 && (
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-lg">
                      <Calculator className="w-5 h-5" />
                      Loan Calculation
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    <div className="flex justify-between">
                      <span>Principal Amount:</span>
                      <span className="font-medium">{formatCurrency(parseFloat(formData.principal_amount))}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Interest Rate:</span>
                      <span className="font-medium">{calculatedDetails.interestRate}% p.a.</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Repayment Period:</span>
                      <span className="font-medium">{formData.repayment_period_months} months</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Monthly Payment:</span>
                      <span className="font-bold text-primary">{formatCurrency(calculatedDetails.monthlyPayment)}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Total Interest:</span>
                      <span className="font-medium">{formatCurrency(calculatedDetails.totalInterest)}</span>
                    </div>
                    <div className="flex justify-between border-t pt-2">
                      <span>Total Amount:</span>
                      <span className="font-bold">{formatCurrency(calculatedDetails.totalAmount)}</span>
                    </div>
                  </CardContent>
                </Card>
              )}

              {/* Guarantor 1 */}
              <div className="space-y-3">
                <h4 className="font-medium">Guarantor 1 *</h4>
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <Label htmlFor="guarantor1_name">Full Name</Label>
                    <Input
                      id="guarantor1_name"
                      type="text"
                      value={formData.guarantor1_name}
                      onChange={(e) => setFormData({ ...formData, guarantor1_name: e.target.value })}
                      placeholder="Guarantor name"
                      required
                      disabled={loading}
                    />
                  </div>
                  <div>
                    <Label htmlFor="guarantor1_phone">Phone Number</Label>
                    <Input
                      id="guarantor1_phone"
                      type="tel"
                      value={formData.guarantor1_phone}
                      onChange={(e) => setFormData({ ...formData, guarantor1_phone: e.target.value })}
                      placeholder="Phone number"
                      required
                      disabled={loading}
                    />
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <Label htmlFor="guarantor1_relationship">Relationship</Label>
                    <Select 
                      value={formData.guarantor1_relationship} 
                      onValueChange={(value) => setFormData({ ...formData, guarantor1_relationship: value })}
                      disabled={loading}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select relationship" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="spouse">Spouse</SelectItem>
                        <SelectItem value="parent">Parent</SelectItem>
                        <SelectItem value="sibling">Sibling</SelectItem>
                        <SelectItem value="friend">Friend</SelectItem>
                        <SelectItem value="colleague">Colleague</SelectItem>
                        <SelectItem value="other">Other</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div>
                    <Label htmlFor="guarantor1_employment">Employment</Label>
                    <Input
                      id="guarantor1_employment"
                      type="text"
                      value={formData.guarantor1_employment}
                      onChange={(e) => setFormData({ ...formData, guarantor1_employment: e.target.value })}
                      placeholder="Employment status"
                      required
                      disabled={loading}
                    />
                  </div>
                </div>
              </div>

              {/* Guarantor 2 */}
              <div className="space-y-3">
                <h4 className="font-medium">Guarantor 2 *</h4>
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <Label htmlFor="guarantor2_name">Full Name</Label>
                    <Input
                      id="guarantor2_name"
                      type="text"
                      value={formData.guarantor2_name}
                      onChange={(e) => setFormData({ ...formData, guarantor2_name: e.target.value })}
                      placeholder="Guarantor name"
                      required
                      disabled={loading}
                    />
                  </div>
                  <div>
                    <Label htmlFor="guarantor2_phone">Phone Number</Label>
                    <Input
                      id="guarantor2_phone"
                      type="tel"
                      value={formData.guarantor2_phone}
                      onChange={(e) => setFormData({ ...formData, guarantor2_phone: e.target.value })}
                      placeholder="Phone number"
                      required
                      disabled={loading}
                    />
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <Label htmlFor="guarantor2_relationship">Relationship</Label>
                    <Select 
                      value={formData.guarantor2_relationship} 
                      onValueChange={(value) => setFormData({ ...formData, guarantor2_relationship: value })}
                      disabled={loading}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select relationship" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="spouse">Spouse</SelectItem>
                        <SelectItem value="parent">Parent</SelectItem>
                        <SelectItem value="sibling">Sibling</SelectItem>
                        <SelectItem value="friend">Friend</SelectItem>
                        <SelectItem value="colleague">Colleague</SelectItem>
                        <SelectItem value="other">Other</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div>
                    <Label htmlFor="guarantor2_employment">Employment</Label>
                    <Input
                      id="guarantor2_employment"
                      type="text"
                      value={formData.guarantor2_employment}
                      onChange={(e) => setFormData({ ...formData, guarantor2_employment: e.target.value })}
                      placeholder="Employment status"
                      required
                      disabled={loading}
                    />
                  </div>
                </div>
              </div>

              {/* Additional Information */}
              <div>
                <Label htmlFor="additional_info">Additional Information</Label>
                <Textarea
                  id="additional_info"
                  value={formData.additional_info}
                  onChange={(e) => setFormData({ ...formData, additional_info: e.target.value })}
                  placeholder="Any additional information that might help with your application"
                  rows={4}
                  disabled={loading}
                />
              </div>
            </div>
          </div>

          {/* Terms and Conditions */}
          <Alert>
            <CheckCircle className="h-4 w-4" />
            <AlertDescription>
              By submitting this application, you agree to the loan terms and conditions. 
              Your application will be reviewed and you will be notified of the decision within 3-5 business days.
            </AlertDescription>
          </Alert>

          {/* Submit Button */}
          <div className="flex gap-3 pt-4">
            <Button type="submit" disabled={loading} className="flex-1">
              {loading ? 'Submitting...' : 'Submit Loan Application'}
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

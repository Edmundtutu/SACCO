import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { FileText, TrendingUp, Clock, CheckCircle } from 'lucide-react';

interface LoanProduct {
  id: number;
  name: string;
  description: string;
  maximum_amount: number;
  interest_rate: number;
  maximum_period_months: number;
  eligibility_criteria?: string[];
  require_collateral: boolean;
  required_guarantors: number;
}

interface LoanProductsProps {
  products: LoanProduct[];
  onApply: (productId: number) => void;
}

export function LoanProducts({ products, onApply }: LoanProductsProps) {
  return (
    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      {products.map((product) => (
        <Card key={product.id} className="hover:shadow-lg transition-shadow">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FileText className="w-5 h-5 text-primary" />
              {product.name}
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <p className="text-sm text-muted-foreground">
              {product.description}
            </p>

            <div className="grid grid-cols-2 gap-4">
              <div className="text-center p-3 bg-muted/50 rounded-lg">
                <TrendingUp className="w-4 h-4 text-success mx-auto mb-1" />
                <p className="text-xs text-muted-foreground">Max Amount</p>
                <p className="font-bold text-success">
                  UGX {product.maximum_amount.toLocaleString()}
                </p>
              </div>
              
              <div className="text-center p-3 bg-muted/50 rounded-lg">
                <Clock className="w-4 h-4 text-primary mx-auto mb-1" />
                <p className="text-xs text-muted-foreground">Max Term</p>
                <p className="font-bold text-primary">
                  {product.maximum_period_months} months
                </p>
              </div>
            </div>

            <div className="text-center p-3 bg-accent/10 rounded-lg">
              <p className="text-xs text-muted-foreground">Interest Rate</p>
              <p className="text-xl font-bold text-accent">
                {product.interest_rate}% p.a.
              </p>
            </div>

            <div className="space-y-2">
              <div className="flex items-center gap-2 text-xs">
                <span className="text-muted-foreground">Guarantors Required:</span>
                <Badge variant="outline">{product.required_guarantors}</Badge>
              </div>
              
              {product.require_collateral && (
                <div className="flex items-center gap-2 text-xs">
                  <span className="text-muted-foreground">Collateral:</span>
                  <Badge variant="destructive">Required</Badge>
                </div>
              )}
              
              {product.eligibility_criteria && product.eligibility_criteria.length > 0 && (
                <>
                  <h4 className="text-sm font-medium">Eligibility Criteria:</h4>
                  <div className="space-y-1">
                    {product.eligibility_criteria.map((criteria, index) => (
                      <div key={index} className="flex items-start gap-2 text-xs">
                        <CheckCircle className="w-3 h-3 text-success mt-0.5 flex-shrink-0" />
                        <span className="text-muted-foreground">{criteria}</span>
                      </div>
                    ))}
                  </div>
                </>
              )}
            </div>

            <Button 
              className="w-full" 
              onClick={() => onApply(product.id)}
            >
              Apply Now
            </Button>
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { FileText, TrendingUp, Clock, CheckCircle } from 'lucide-react';

interface LoanProduct {
  id: number;
  name: string;
  description: string;
  max_amount: number;
  interest_rate: number;
  max_term_months: number;
  requirements: string[];
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
                  KES {product.max_amount.toLocaleString()}
                </p>
              </div>
              
              <div className="text-center p-3 bg-muted/50 rounded-lg">
                <Clock className="w-4 h-4 text-primary mx-auto mb-1" />
                <p className="text-xs text-muted-foreground">Max Term</p>
                <p className="font-bold text-primary">
                  {product.max_term_months} months
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
              <h4 className="text-sm font-medium">Requirements:</h4>
              <div className="space-y-1">
                {product.requirements.map((req, index) => (
                  <div key={index} className="flex items-start gap-2 text-xs">
                    <CheckCircle className="w-3 h-3 text-success mt-0.5 flex-shrink-0" />
                    <span className="text-muted-foreground">{req}</span>
                  </div>
                ))}
              </div>
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
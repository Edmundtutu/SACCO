import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { TrendingDown, Calendar, DollarSign } from 'lucide-react';

interface Loan {
  id: number;
  product_name: string;
  principal_amount: number;
  outstanding_balance: number;
  interest_rate: number;
  monthly_payment: number;
  next_payment_date: string;
  status: 'active' | 'paid' | 'overdue' | 'pending';
  created_at: string;
}

interface LoanTrackerProps {
  loan: Loan;
  repaymentProgress: number;
}

export function LoanTracker({ loan, repaymentProgress }: LoanTrackerProps) {
  const amountPaid = loan.principal_amount - loan.outstanding_balance;
  const isOverdue = new Date(loan.next_payment_date) < new Date() && loan.status === 'active';
  
  return (
    <Card className="bg-gradient-to-r from-blue-50 to-purple-50 border-primary/20">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <TrendingDown className="w-5 h-5 text-primary" />
          Loan Repayment Progress
          <Badge variant={isOverdue ? 'destructive' : 'default'}>
            {Math.round(repaymentProgress)}% repaid 📉
          </Badge>
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="text-center p-4 bg-background/50 rounded-lg">
            <DollarSign className="w-6 h-6 text-success mx-auto mb-2" />
            <p className="text-sm text-muted-foreground">Amount Paid</p>
            <p className="text-xl font-bold text-success">
              UGX {amountPaid.toLocaleString()}
            </p>
          </div>
          
          <div className="text-center p-4 bg-background/50 rounded-lg">
            <TrendingDown className="w-6 h-6 text-destructive mx-auto mb-2" />
            <p className="text-sm text-muted-foreground">Outstanding</p>
            <p className="text-xl font-bold text-destructive">
              UGX {loan.outstanding_balance.toLocaleString()}
            </p>
          </div>
          
          <div className="text-center p-4 bg-background/50 rounded-lg">
            <Calendar className="w-6 h-6 text-primary mx-auto mb-2" />
            <p className="text-sm text-muted-foreground">Next Payment</p>
            <p className="font-bold text-primary">
              {new Date(loan.next_payment_date).toLocaleDateString()}
            </p>
            <p className="text-sm font-medium">
              UGX {loan.monthly_payment.toLocaleString()}
            </p>
          </div>
        </div>

        <div className="space-y-3">
          <div className="flex justify-between items-center">
            <span className="text-sm font-medium">{loan.product_name}</span>
            <span className="text-sm text-muted-foreground">
              {Math.round(repaymentProgress)}% complete
            </span>
          </div>
          
          <Progress value={repaymentProgress} className="h-3" />
          
          <div className="flex justify-between text-xs text-muted-foreground">
            <span>Principal: UGX {loan.principal_amount.toLocaleString()}</span>
            <span>Interest: {loan.interest_rate}% p.a.</span>
          </div>
        </div>

        <div className="flex gap-3">
          <Button className="flex-1" variant={isOverdue ? 'destructive' : 'default'}>
            {isOverdue ? 'Pay Overdue Amount' : 'Make Payment'}
          </Button>
          <Button variant="outline" className="flex-1">
            View Schedule
          </Button>
        </div>

        {isOverdue && (
          <div className="p-3 bg-destructive/10 rounded-lg border border-destructive/20">
            <p className="text-sm text-destructive font-medium">
              ⚠️ Payment overdue! Please make payment to avoid penalties.
            </p>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
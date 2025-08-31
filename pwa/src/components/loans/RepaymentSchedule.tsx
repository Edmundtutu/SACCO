import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, DollarSign, TrendingDown } from 'lucide-react';

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

interface RepaymentScheduleProps {
  loan: Loan;
}

export function RepaymentSchedule({ loan }: RepaymentScheduleProps) {
  // Calculate payment schedule
  const generatePaymentSchedule = () => {
    const schedule = [];
    const monthlyRate = loan.interest_rate / 100 / 12;
    let balance = loan.outstanding_balance;
    const monthlyPayment = loan.monthly_payment;
    
    let currentDate = new Date(loan.next_payment_date);
    
    while (balance > 0 && schedule.length < 12) { // Show next 12 payments
      const interestPayment = balance * monthlyRate;
      const principalPayment = Math.min(monthlyPayment - interestPayment, balance);
      balance -= principalPayment;
      
      schedule.push({
        date: new Date(currentDate),
        payment: monthlyPayment,
        principal: principalPayment,
        interest: interestPayment,
        balance: balance,
        isPaid: currentDate < new Date(), // This would come from actual payment data
      });
      
      // Move to next month
      currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, currentDate.getDate());
    }
    
    return schedule;
  };

  const schedule = generatePaymentSchedule();
  const nextPayment = schedule[0];

  return (
    <div className="space-y-6">
      {/* Next Payment Highlight */}
      {nextPayment && (
        <Card className="border-primary/20 bg-primary/5">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="w-5 h-5 text-primary" />
              Next Payment Due
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div className="text-center">
                <p className="text-sm text-muted-foreground">Due Date</p>
                <p className="text-lg font-bold text-primary">
                  {nextPayment.date.toLocaleDateString()}
                </p>
              </div>
              <div className="text-center">
                <p className="text-sm text-muted-foreground">Amount</p>
                <p className="text-lg font-bold">
                  KES {nextPayment.payment.toLocaleString()}
                </p>
              </div>
              <div className="text-center">
                <p className="text-sm text-muted-foreground">Principal</p>
                <p className="text-lg font-bold text-success">
                  KES {nextPayment.principal.toLocaleString()}
                </p>
              </div>
              <div className="text-center">
                <p className="text-sm text-muted-foreground">Interest</p>
                <p className="text-lg font-bold text-muted-foreground">
                  KES {nextPayment.interest.toLocaleString()}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Payment Schedule Table */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <TrendingDown className="w-5 h-5" />
            Repayment Schedule
          </CardTitle>
          <p className="text-sm text-muted-foreground">
            Showing next 12 payments for {loan.product_name}
          </p>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {schedule.map((payment, index) => (
              <div 
                key={index}
                className={`p-4 border rounded-lg ${
                  payment.isPaid 
                    ? 'bg-success/10 border-success/20' 
                    : index === 0 
                    ? 'bg-primary/10 border-primary/20' 
                    : 'bg-muted/20'
                }`}
              >
                <div className="flex justify-between items-center mb-2">
                  <div className="flex items-center gap-2">
                    <span className="font-medium">
                      Payment #{index + 1}
                    </span>
                    {payment.isPaid && (
                      <Badge variant="default" className="text-xs">
                        Paid
                      </Badge>
                    )}
                    {index === 0 && !payment.isPaid && (
                      <Badge variant="destructive" className="text-xs">
                        Due Soon
                      </Badge>
                    )}
                  </div>
                  <span className="text-sm text-muted-foreground">
                    {payment.date.toLocaleDateString()}
                  </span>
                </div>
                
                <div className="grid grid-cols-4 gap-4 text-sm">
                  <div>
                    <p className="text-muted-foreground">Total Payment</p>
                    <p className="font-bold">KES {payment.payment.toLocaleString()}</p>
                  </div>
                  <div>
                    <p className="text-muted-foreground">Principal</p>
                    <p className="font-medium text-success">
                      KES {payment.principal.toLocaleString()}
                    </p>
                  </div>
                  <div>
                    <p className="text-muted-foreground">Interest</p>
                    <p className="font-medium">KES {payment.interest.toLocaleString()}</p>
                  </div>
                  <div>
                    <p className="text-muted-foreground">Remaining Balance</p>
                    <p className="font-medium">KES {payment.balance.toLocaleString()}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
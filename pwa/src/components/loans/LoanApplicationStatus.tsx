import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Button } from '@/components/ui/button';
import { 
  Clock, 
  CheckCircle, 
  XCircle, 
  FileText, 
  DollarSign, 
  Calendar,
  User,
  Shield
} from 'lucide-react';
import type { Loan } from '@/types/api';

interface LoanApplicationStatusProps {
  loan: Loan;
}

export function LoanApplicationStatus({ loan }: LoanApplicationStatusProps) {
  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'pending':
        return <Clock className="w-5 h-5 text-yellow-600" />;
      case 'approved':
        return <CheckCircle className="w-5 h-5 text-green-600" />;
      case 'disbursed':
        return <DollarSign className="w-5 h-5 text-blue-600" />;
      case 'active':
        return <CheckCircle className="w-5 h-5 text-green-600" />;
      case 'completed':
        return <CheckCircle className="w-5 h-5 text-gray-600" />;
      case 'rejected':
        return <XCircle className="w-5 h-5 text-red-600" />;
      case 'overdue':
        return <Clock className="w-5 h-5 text-red-600" />;
      default:
        return <FileText className="w-5 h-5 text-gray-600" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending':
        return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      case 'approved':
        return 'bg-green-100 text-green-800 border-green-200';
      case 'disbursed':
        return 'bg-blue-100 text-blue-800 border-blue-200';
      case 'active':
        return 'bg-green-100 text-green-800 border-green-200';
      case 'completed':
        return 'bg-gray-100 text-gray-800 border-gray-200';
      case 'rejected':
        return 'bg-red-100 text-red-800 border-red-200';
      case 'overdue':
        return 'bg-red-100 text-red-800 border-red-200';
      default:
        return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  const getProgressPercentage = (status: string) => {
    switch (status) {
      case 'pending':
        return 25;
      case 'approved':
        return 50;
      case 'disbursed':
        return 75;
      case 'active':
        return 90;
      case 'completed':
        return 100;
      case 'rejected':
        return 0;
      default:
        return 0;
    }
  };

  const getStatusSteps = (status: string) => {
    const steps = [
      { id: 'pending', label: 'Application Submitted', completed: true },
      { id: 'approved', label: 'Application Approved', completed: ['approved', 'disbursed', 'active', 'completed'].includes(status) },
      { id: 'disbursed', label: 'Loan Disbursed', completed: ['disbursed', 'active', 'completed'].includes(status) },
      { id: 'active', label: 'Repayment Active', completed: ['active', 'completed'].includes(status) },
      { id: 'completed', label: 'Loan Completed', completed: status === 'completed' },
    ];

    if (status === 'rejected') {
      return [
        { id: 'pending', label: 'Application Submitted', completed: true },
        { id: 'rejected', label: 'Application Rejected', completed: true },
      ];
    }

    return steps;
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', {
      style: 'currency',
      currency: 'KES',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-KE', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  // Handle null loan case
  if (!loan) {
    return (
      <div className="space-y-6">
        <Card>
          <CardContent className="p-6 text-center">
            <div className="text-muted-foreground">
              <p>No loan application found.</p>
              <p className="text-sm mt-2">Apply for a loan to see your application status here.</p>
            </div>
          </CardContent>
        </Card>
      </div>
    );
  }

  const statusSteps = getStatusSteps(loan.status);
  const progressPercentage = getProgressPercentage(loan.status);

  return (
    <div className="space-y-6">
      {/* Loan Status Overview */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            {getStatusIcon(loan.status)}
            Loan Application Status
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-lg font-semibold">{loan.loan_product?.name || 'Loan'}</h3>
              <p className="text-sm text-muted-foreground">
                Application #{loan.loan_number}
              </p>
            </div>
            <Badge className={getStatusColor(loan.status)}>
              {loan.status.charAt(0).toUpperCase() + loan.status.slice(1)}
            </Badge>
          </div>

          <div className="space-y-2">
            <div className="flex justify-between text-sm">
              <span>Application Progress</span>
              <span>{progressPercentage}%</span>
            </div>
            <Progress value={progressPercentage} className="h-2" />
          </div>
        </CardContent>
      </Card>

      {/* Application Timeline */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calendar className="w-5 h-5" />
            Application Timeline
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {statusSteps.map((step, index) => (
              <div key={step.id} className="flex items-start gap-3">
                <div className={`w-8 h-8 rounded-full flex items-center justify-center ${
                  step.completed 
                    ? 'bg-green-100 text-green-600' 
                    : 'bg-gray-100 text-gray-400'
                }`}>
                  {step.completed ? (
                    <CheckCircle className="w-4 h-4" />
                  ) : (
                    <div className="w-4 h-4 rounded-full bg-gray-300" />
                  )}
                </div>
                <div className="flex-1">
                  <p className={`font-medium ${
                    step.completed ? 'text-gray-900' : 'text-gray-500'
                  }`}>
                    {step.label}
                  </p>
                  {step.id === 'pending' && (
                    <p className="text-sm text-muted-foreground">
                      Submitted on {formatDate(loan.application_date)}
                    </p>
                  )}
                  {step.id === 'approved' && loan.approval_date && (
                    <p className="text-sm text-muted-foreground">
                      Approved on {formatDate(loan.approval_date)}
                    </p>
                  )}
                  {step.id === 'disbursed' && loan.disbursement_date && (
                    <p className="text-sm text-muted-foreground">
                      Disbursed on {formatDate(loan.disbursement_date)}
                    </p>
                  )}
                  {step.id === 'rejected' && loan.rejection_reason && (
                    <p className="text-sm text-red-600">
                      Reason: {loan.rejection_reason}
                    </p>
                  )}
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Loan Details */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <FileText className="w-5 h-5" />
            Loan Details
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-3">
              <div>
                <p className="text-sm text-muted-foreground">Principal Amount</p>
                <p className="font-semibold">{formatCurrency(loan.principal_amount)}</p>
              </div>
              
              <div>
                <p className="text-sm text-muted-foreground">Interest Rate</p>
                <p className="font-semibold">{loan.interest_rate}% p.a.</p>
              </div>
              
              <div>
                <p className="text-sm text-muted-foreground">Repayment Period</p>
                <p className="font-semibold">{loan.repayment_period_months} months</p>
              </div>
              
              <div>
                <p className="text-sm text-muted-foreground">Monthly Payment</p>
                <p className="font-semibold">{formatCurrency(loan.monthly_payment)}</p>
              </div>
            </div>
            
            <div className="space-y-3">
              <div>
                <p className="text-sm text-muted-foreground">Outstanding Balance</p>
                <p className="font-semibold text-red-600">{formatCurrency(loan.outstanding_balance)}</p>
              </div>
              
              <div>
                <p className="text-sm text-muted-foreground">Total Paid</p>
                <p className="font-semibold text-green-600">{formatCurrency(loan.total_paid)}</p>
              </div>
              
              {loan.first_payment_date && (
                <div>
                  <p className="text-sm text-muted-foreground">First Payment Date</p>
                  <p className="font-semibold">{formatDate(loan.first_payment_date)}</p>
                </div>
              )}
              
              {loan.maturity_date && (
                <div>
                  <p className="text-sm text-muted-foreground">Maturity Date</p>
                  <p className="font-semibold">{formatDate(loan.maturity_date)}</p>
                </div>
              )}
            </div>
          </div>
          
          <div className="mt-4 pt-4 border-t">
            <p className="text-sm text-muted-foreground">Loan Purpose</p>
            <p className="font-medium">{loan.purpose}</p>
          </div>
        </CardContent>
      </Card>

      {/* Collateral Information */}
      {loan.collateral_description && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Shield className="w-5 h-5" />
              Collateral Information
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              <div>
                <p className="text-sm text-muted-foreground">Collateral Description</p>
                <p className="font-medium">{loan.collateral_description}</p>
              </div>
              {loan.collateral_value && (
                <div>
                  <p className="text-sm text-muted-foreground">Collateral Value</p>
                  <p className="font-semibold">{formatCurrency(loan.collateral_value)}</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Guarantors Information */}
      {loan.guarantors && loan.guarantors.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <User className="w-5 h-5" />
              Guarantors ({loan.guarantors.length})
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {loan.guarantors.map((guarantor) => (
                <div key={guarantor.id} className="p-3 border rounded-lg">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium">{guarantor.guarantor?.name || 'Unknown'}</p>
                      <p className="text-sm text-muted-foreground">
                        Guarantee Amount: {formatCurrency(guarantor.guarantee_amount)}
                      </p>
                    </div>
                    <Badge variant={
                      guarantor.status === 'accepted' ? 'default' :
                      guarantor.status === 'rejected' ? 'destructive' : 'secondary'
                    }>
                      {guarantor.status}
                    </Badge>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Action Buttons */}
      {loan.status === 'approved' && (
        <Card>
          <CardContent className="pt-6">
            <div className="text-center space-y-3">
              <p className="text-sm text-muted-foreground">
                Your loan has been approved! It will be disbursed shortly.
              </p>
              <Button variant="outline" disabled>
                <Clock className="w-4 h-4 mr-2" />
                Awaiting Disbursement
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {loan.status === 'rejected' && (
        <Card>
          <CardContent className="pt-6">
            <div className="text-center space-y-3">
              <p className="text-sm text-red-600">
                Your loan application has been rejected.
              </p>
              {loan.rejection_reason && (
                <p className="text-sm text-muted-foreground">
                  Reason: {loan.rejection_reason}
                </p>
              )}
              <Button>
                Apply for Another Loan
              </Button>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}

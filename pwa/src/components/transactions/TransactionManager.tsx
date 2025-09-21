import { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { RootState, AppDispatch } from '@/store';
import { 
  fetchTransactionHistory, 
  fetchTransactionSummary,
  makeDeposit,
  makeWithdrawal,
  purchaseShares,
  repayLoan
} from '@/store/transactionsSlice';
import { fetchSavingsAccounts } from '@/store/savingsSlice';
import { fetchLoans } from '@/store/loansSlice';
import { DepositForm } from '@/components/savings/DepositForm';
import { WithdrawalForm } from '@/components/savings/WithdrawalForm';
import { SharesPurchase } from '@/components/shares/SharesPurchase';
import { LoanRepaymentForm } from '@/components/loans/LoanRepaymentForm';
import { 
  ArrowUpRight, 
  ArrowDownRight, 
  TrendingUp, 
  CreditCard,
  Filter,
  Download,
  RefreshCw
} from 'lucide-react';
import type { Transaction, SavingsAccount, Loan } from '@/types/api';

interface TransactionManagerProps {
  className?: string;
}

export function TransactionManager({ className }: TransactionManagerProps) {
  const dispatch = useDispatch<AppDispatch>();
  const { toast } = useToast();
  
  const { 
    transactions, 
    transactionSummary, 
    loading 
  } = useSelector((state: RootState) => state.transactions);
  
  const { accounts } = useSelector((state: RootState) => state.savings);
  const { loans } = useSelector((state: RootState) => state.loans);
  const { user } = useSelector((state: RootState) => state.auth);

  const [selectedAccount, setSelectedAccount] = useState<SavingsAccount | null>(null);
  const [selectedLoan, setSelectedLoan] = useState<Loan | null>(null);
  const [showDepositForm, setShowDepositForm] = useState(false);
  const [showWithdrawalForm, setShowWithdrawalForm] = useState(false);
  const [showSharesForm, setShowSharesForm] = useState(false);
  const [showRepaymentForm, setShowRepaymentForm] = useState(false);
  const [filters, setFilters] = useState({
    type: '',
    status: '',
    startDate: '',
    endDate: '',
  });

  useEffect(() => {
    if (user?.id) {
      dispatch(fetchTransactionHistory({ 
        member_id: user.id,
        per_page: 50 
      }));
      dispatch(fetchTransactionSummary({ 
        member_id: user.id 
      }));
      dispatch(fetchSavingsAccounts());
      dispatch(fetchLoans());
    }
  }, [dispatch, user?.id]);

  const handleFilterChange = (key: string, value: string) => {
    setFilters(prev => ({ ...prev, [key]: value }));
  };

  const applyFilters = () => {
    if (user?.id) {
      dispatch(fetchTransactionHistory({
        member_id: user.id,
        type: filters.type || undefined,
        status: filters.status || undefined,
        start_date: filters.startDate || undefined,
        end_date: filters.endDate || undefined,
        per_page: 50
      }));
    }
  };

  const clearFilters = () => {
    setFilters({
      type: '',
      status: '',
      startDate: '',
      endDate: '',
    });
    if (user?.id) {
      dispatch(fetchTransactionHistory({ 
        member_id: user.id,
        per_page: 50 
      }));
    }
  };

  const exportTransactions = () => {
    // TODO: Implement CSV/PDF export
    toast({
      title: "Export Feature",
      description: "Transaction export feature coming soon!",
    });
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
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getTransactionIcon = (type: string) => {
    switch (type) {
      case 'deposit':
        return <ArrowUpRight className="w-4 h-4 text-green-600" />;
      case 'withdrawal':
        return <ArrowDownRight className="w-4 h-4 text-red-600" />;
      case 'share_purchase':
        return <TrendingUp className="w-4 h-4 text-blue-600" />;
      case 'loan_disbursement':
        return <CreditCard className="w-4 h-4 text-purple-600" />;
      case 'loan_repayment':
        return <ArrowDownRight className="w-4 h-4 text-orange-600" />;
      default:
        return <CreditCard className="w-4 h-4 text-gray-600" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      case 'failed':
        return 'bg-red-100 text-red-800';
      case 'reversed':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const filteredTransactions = transactions.filter(transaction => {
    if (filters.type && transaction.type !== filters.type) return false;
    if (filters.status && transaction.status !== filters.status) return false;
    if (filters.startDate && new Date(transaction.transaction_date) < new Date(filters.startDate)) return false;
    if (filters.endDate && new Date(transaction.transaction_date) > new Date(filters.endDate)) return false;
    return true;
  });

  return (
    <div className={`space-y-6 ${className}`}>
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold">Transaction Management</h2>
          <p className="text-muted-foreground">Manage your financial transactions</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={exportTransactions}>
            <Download className="w-4 h-4 mr-2" />
            Export
          </Button>
          <Button variant="outline" onClick={() => window.location.reload()}>
            <RefreshCw className="w-4 h-4 mr-2" />
            Refresh
          </Button>
        </div>
      </div>

      {/* Summary Cards */}
      {transactionSummary && (
        <div className="grid gap-4 md:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Transactions</CardTitle>
              <CreditCard className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{transactionSummary.total_transactions}</div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Deposits</CardTitle>
              <ArrowUpRight className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {formatCurrency(transactionSummary.total_deposits)}
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Withdrawals</CardTitle>
              <ArrowDownRight className="h-4 w-4 text-red-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">
                {formatCurrency(transactionSummary.total_withdrawals)}
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Net Cash Flow</CardTitle>
              <TrendingUp className="h-4 w-4 text-blue-600" />
            </CardHeader>
            <CardContent>
              <div className={`text-2xl font-bold ${
                transactionSummary.net_cash_flow >= 0 ? 'text-green-600' : 'text-red-600'
              }`}>
                {formatCurrency(transactionSummary.net_cash_flow)}
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      <Tabs defaultValue="transactions" className="space-y-6">
        <TabsList className="grid w-full grid-cols-2">
          <TabsTrigger value="transactions">Transaction History</TabsTrigger>
          <TabsTrigger value="actions">Quick Actions</TabsTrigger>
        </TabsList>

        <TabsContent value="transactions" className="space-y-4">
          {/* Filters */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Filter className="w-4 h-4" />
                Filters
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 md:grid-cols-5">
                <Select value={filters.type} onValueChange={(value) => handleFilterChange('type', value)}>
                  <SelectTrigger>
                    <SelectValue placeholder="Transaction Type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">All Types</SelectItem>
                    <SelectItem value="deposit">Deposit</SelectItem>
                    <SelectItem value="withdrawal">Withdrawal</SelectItem>
                    <SelectItem value="share_purchase">Share Purchase</SelectItem>
                    <SelectItem value="loan_disbursement">Loan Disbursement</SelectItem>
                    <SelectItem value="loan_repayment">Loan Repayment</SelectItem>
                  </SelectContent>
                </Select>

                <Select value={filters.status} onValueChange={(value) => handleFilterChange('status', value)}>
                  <SelectTrigger>
                    <SelectValue placeholder="Status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">All Statuses</SelectItem>
                    <SelectItem value="completed">Completed</SelectItem>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="failed">Failed</SelectItem>
                    <SelectItem value="reversed">Reversed</SelectItem>
                  </SelectContent>
                </Select>

                <Input
                  type="date"
                  placeholder="Start Date"
                  value={filters.startDate}
                  onChange={(e) => handleFilterChange('startDate', e.target.value)}
                />

                <Input
                  type="date"
                  placeholder="End Date"
                  value={filters.endDate}
                  onChange={(e) => handleFilterChange('endDate', e.target.value)}
                />

                <div className="flex gap-2">
                  <Button onClick={applyFilters} size="sm">
                    Apply
                  </Button>
                  <Button onClick={clearFilters} variant="outline" size="sm">
                    Clear
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Transaction List */}
          <Card>
            <CardHeader>
              <CardTitle>Transaction History</CardTitle>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="text-center py-8">
                  <RefreshCw className="w-6 h-6 animate-spin mx-auto mb-2" />
                  <p className="text-muted-foreground">Loading transactions...</p>
                </div>
              ) : filteredTransactions.length === 0 ? (
                <div className="text-center py-8">
                  <CreditCard className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground">No transactions found</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {filteredTransactions.map((transaction) => (
                    <div key={transaction.id} className="flex items-center justify-between p-4 border rounded-lg">
                      <div className="flex items-center gap-4">
                        <div className="p-2 bg-muted rounded-lg">
                          {getTransactionIcon(transaction.type)}
                        </div>
                        <div>
                          <div className="font-medium">
                            {transaction.transaction_number}
                          </div>
                          <div className="text-sm text-muted-foreground">
                            {transaction.description}
                          </div>
                          <div className="text-xs text-muted-foreground">
                            {formatDate(transaction.transaction_date)}
                          </div>
                        </div>
                      </div>
                      
                      <div className="text-right">
                        <div className={`font-bold ${
                          transaction.type === 'deposit' || transaction.type === 'loan_disbursement' 
                            ? 'text-green-600' 
                            : 'text-red-600'
                        }`}>
                          {transaction.type === 'deposit' || transaction.type === 'loan_disbursement' ? '+' : '-'}
                          {formatCurrency(transaction.amount)}
                        </div>
                        <Badge className={getStatusColor(transaction.status)}>
                          {transaction.status}
                        </Badge>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="actions" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2">
            {/* Savings Actions */}
            <Card>
              <CardHeader>
                <CardTitle>Savings Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <Button 
                  className="w-full" 
                  onClick={() => setShowDepositForm(true)}
                  disabled={accounts.length === 0}
                >
                  <ArrowUpRight className="w-4 h-4 mr-2" />
                  Make Deposit
                </Button>
                <Button 
                  className="w-full" 
                  variant="outline"
                  onClick={() => setShowWithdrawalForm(true)}
                  disabled={accounts.length === 0}
                >
                  <ArrowDownRight className="w-4 h-4 mr-2" />
                  Make Withdrawal
                </Button>
              </CardContent>
            </Card>

            {/* Investment Actions */}
            <Card>
              <CardHeader>
                <CardTitle>Investment Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <Button 
                  className="w-full" 
                  variant="outline"
                  onClick={() => setShowSharesForm(true)}
                >
                  <TrendingUp className="w-4 h-4 mr-2" />
                  Buy Shares
                </Button>
                <Button 
                  className="w-full" 
                  variant="outline"
                  onClick={() => setShowRepaymentForm(true)}
                  disabled={loans.filter(l => l.status === 'active' || l.status === 'disbursed').length === 0}
                >
                  <CreditCard className="w-4 h-4 mr-2" />
                  Make Loan Payment
                </Button>
              </CardContent>
            </Card>
          </div>
        </TabsContent>
      </Tabs>

      {/* Modals */}
      {showDepositForm && (
        <DepositForm
          isOpen={showDepositForm}
          onClose={() => setShowDepositForm(false)}
          account={selectedAccount || accounts[0]}
        />
      )}

      {showWithdrawalForm && (
        <WithdrawalForm
          isOpen={showWithdrawalForm}
          onClose={() => setShowWithdrawalForm(false)}
          account={selectedAccount || accounts[0]}
        />
      )}

      {showSharesForm && (
        <SharesPurchase
          currentShares={0}
          shareValue={100}
          onClose={() => setShowSharesForm(false)}
        />
      )}

      {showRepaymentForm && selectedLoan && (
        <LoanRepaymentForm
          loan={selectedLoan}
          onClose={() => {
            setShowRepaymentForm(false);
            setSelectedLoan(null);
          }}
        />
      )}
    </div>
  );
}
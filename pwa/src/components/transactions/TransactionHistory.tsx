import { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { useToast } from '@/hooks/use-toast';
import { RootState } from '@/store';
import { fetchTransactionHistory, fetchTransactionSummary } from '@/store/transactionsSlice';
import { 
  ArrowUpRight, 
  ArrowDownLeft, 
  CreditCard, 
  TrendingUp, 
  Calendar,
  Filter,
  Download,
  RefreshCw
} from 'lucide-react';
import type { Transaction } from '@/types/api';

interface TransactionHistoryProps {
  memberId: number;
}

export function TransactionHistory({ memberId }: TransactionHistoryProps) {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { transactions, loading, summary } = useSelector((state: RootState) => state.transactions);
  
  const [filters, setFilters] = useState({
    start_date: '',
    end_date: '',
    type: '',
    page: 1,
    per_page: 20,
  });

  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    loadTransactions();
    loadSummary();
  }, [memberId, filters]);

  const loadTransactions = async () => {
    try {
      await dispatch(fetchTransactionHistory({
        member_id: memberId,
        start_date: filters.start_date || undefined,
        end_date: filters.end_date || undefined,
        type: filters.type as 'deposit' | 'withdrawal' | 'share_purchase' | 'loan_disbursement' | 'loan_repayment' | undefined,
        page: filters.page,
        per_page: filters.per_page,
      }) as any);
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to load transaction history",
        variant: "destructive",
      });
    }
  };

  const loadSummary = async () => {
    try {
      await dispatch(fetchTransactionSummary({
        member_id: memberId,
        start_date: filters.start_date || undefined,
        end_date: filters.end_date || undefined,
      }) as any);
    } catch (error) {
      // Summary loading failure is not critical
    }
  };

  const handleFilterChange = (key: string, value: string) => {
    setFilters(prev => ({ ...prev, [key]: value, page: 1 }));
  };

  const clearFilters = () => {
    setFilters({
      start_date: '',
      end_date: '',
      type: '',
      page: 1,
      per_page: 20,
    });
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getTransactionIcon = (type: string) => {
    switch (type) {
      case 'deposit':
        return <ArrowUpRight className="w-4 h-4 text-green-600" />;
      case 'withdrawal':
        return <ArrowDownLeft className="w-4 h-4 text-red-600" />;
      case 'loan_disbursement':
        return <CreditCard className="w-4 h-4 text-blue-600" />;
      case 'loan_repayment':
        return <TrendingUp className="w-4 h-4 text-purple-600" />;
      case 'share_purchase':
        return <TrendingUp className="w-4 h-4 text-orange-600" />;
      default:
        return <CreditCard className="w-4 h-4 text-gray-600" />;
    }
  };

  const getTransactionColor = (type: string) => {
    switch (type) {
      case 'deposit':
        return 'text-green-600 bg-green-50';
      case 'withdrawal':
        return 'text-red-600 bg-red-50';
      case 'loan_disbursement':
        return 'text-blue-600 bg-blue-50';
      case 'loan_repayment':
        return 'text-purple-600 bg-purple-50';
      case 'share_purchase':
        return 'text-orange-600 bg-orange-50';
      default:
        return 'text-gray-600 bg-gray-50';
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'completed':
        return <Badge variant="default" className="bg-green-100 text-green-800">Completed</Badge>;
      case 'pending':
        return <Badge variant="secondary" className="bg-yellow-100 text-yellow-800">Pending</Badge>;
      case 'rejected':
        return <Badge variant="destructive">Rejected</Badge>;
      case 'reversed':
        return <Badge variant="outline">Reversed</Badge>;
      default:
        return <Badge variant="outline">{status}</Badge>;
    }
  };

  return (
    <div className="space-y-6">
      {/* Summary Cards */}
      {summary && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Total Transactions</p>
                  <p className="text-2xl font-bold">{summary.total_transactions}</p>
                </div>
                <CreditCard className="w-8 h-8 text-muted-foreground" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Total Deposits</p>
                  <p className="text-2xl font-bold text-green-600">
                    {formatCurrency(summary.total_deposits)}
                  </p>
                </div>
                <ArrowUpRight className="w-8 h-8 text-green-600" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Total Withdrawals</p>
                  <p className="text-2xl font-bold text-red-600">
                    {formatCurrency(summary.total_withdrawals)}
                  </p>
                </div>
                <ArrowDownLeft className="w-8 h-8 text-red-600" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Net Cash Flow</p>
                  <p className={`text-2xl font-bold ${summary.net_cash_flow >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                    {formatCurrency(summary.net_cash_flow)}
                  </p>
                </div>
                <TrendingUp className={`w-8 h-8 ${summary.net_cash_flow >= 0 ? 'text-green-600' : 'text-red-600'}`} />
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Filters */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle className="flex items-center gap-2">
              <Calendar className="w-5 h-5" />
              Transaction History
            </CardTitle>
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setShowFilters(!showFilters)}
              >
                <Filter className="w-4 h-4 mr-2" />
                Filters
              </Button>
              <Button
                variant="outline"
                size="sm"
                onClick={loadTransactions}
                disabled={loading}
              >
                <RefreshCw className={`w-4 h-4 mr-2 ${loading ? 'animate-spin' : ''}`} />
                Refresh
              </Button>
            </div>
          </div>
        </CardHeader>

        {showFilters && (
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <Label htmlFor="start_date">From Date</Label>
                <Input
                  id="start_date"
                  type="date"
                  value={filters.start_date}
                  onChange={(e) => handleFilterChange('start_date', e.target.value)}
                />
              </div>
              <div>
                <Label htmlFor="end_date">To Date</Label>
                <Input
                  id="end_date"
                  type="date"
                  value={filters.end_date}
                  onChange={(e) => handleFilterChange('end_date', e.target.value)}
                />
              </div>
              <div>
                <Label htmlFor="type">Transaction Type</Label>
                <Select
                  value={filters.type}
                  onValueChange={(value) => handleFilterChange('type', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="All types" />
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
              </div>
              <div className="flex items-end">
                <Button variant="outline" onClick={clearFilters} className="w-full">
                  Clear Filters
                </Button>
              </div>
            </div>
          </CardContent>
        )}
      </Card>

      {/* Transaction List */}
      <Card>
        <CardContent className="p-0">
          {loading ? (
            <div className="space-y-4 p-6">
              {[...Array(5)].map((_, i) => (
                <div key={i} className="flex items-center space-x-4">
                  <Skeleton className="h-12 w-12 rounded-full" />
                  <div className="space-y-2 flex-1">
                    <Skeleton className="h-4 w-[250px]" />
                    <Skeleton className="h-4 w-[200px]" />
                  </div>
                  <Skeleton className="h-4 w-[100px]" />
                </div>
              ))}
            </div>
          ) : transactions.length > 0 ? (
            <div className="divide-y">
              {transactions.map((transaction) => (
                <div key={transaction.id} className="p-4 hover:bg-muted/50 transition-colors">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                      <div className={`p-2 rounded-full ${getTransactionColor(transaction.type)}`}>
                        {getTransactionIcon(transaction.type)}
                      </div>
                      <div>
                        <div className="flex items-center gap-2">
                          <p className="font-medium">
                            {transaction.transaction_number}
                          </p>
                          {getStatusBadge(transaction.status)}
                        </div>
                        <p className="text-sm text-muted-foreground">
                          {formatDate(transaction.transaction_date)}
                        </p>
                        <p className="text-sm text-muted-foreground">
                          {transaction.description}
                        </p>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className={`font-bold ${
                        transaction.type === 'deposit' || transaction.type === 'loan_disbursement' 
                          ? 'text-green-600' 
                          : 'text-red-600'
                      }`}>
                        {transaction.type === 'deposit' || transaction.type === 'loan_disbursement' ? '+' : '-'}
                        {formatCurrency(transaction.amount)}
                      </p>
                      {transaction.fee_amount > 0 && (
                        <p className="text-xs text-muted-foreground">
                          Fee: {formatCurrency(transaction.fee_amount)}
                        </p>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-12">
              <CreditCard className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
              <h3 className="text-lg font-medium text-muted-foreground mb-2">
                No transactions found
              </h3>
              <p className="text-sm text-muted-foreground">
                {Object.values(filters).some(f => f) 
                  ? 'Try adjusting your filters to see more transactions.'
                  : 'You haven\'t made any transactions yet.'
                }
              </p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Pagination */}
      {transactions.length > 0 && (
        <div className="flex justify-center">
          <div className="flex gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={() => setFilters(prev => ({ ...prev, page: prev.page - 1 }))}
              disabled={filters.page <= 1 || loading}
            >
              Previous
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => setFilters(prev => ({ ...prev, page: prev.page + 1 }))}
              disabled={transactions.length < filters.per_page || loading}
            >
              Next
            </Button>
          </div>
        </div>
      )}
    </div>
  );
}
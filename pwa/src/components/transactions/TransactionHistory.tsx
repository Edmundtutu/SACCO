import { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useToast } from '@/hooks/use-toast';
import { RootState } from '@/store';
import { fetchTransactionHistory, fetchTransactionSummary } from '@/store/transactionsSlice';
import { 
  ArrowUpRight, 
  ArrowDownRight, 
  CreditCard, 
  DollarSign, 
  TrendingUp,
  Eye,
  Calendar,
  Filter,
  Download
} from 'lucide-react';
import type { Transaction } from '@/types/api';

interface TransactionHistoryProps {
  memberId: number;
}

export function TransactionHistory({ memberId }: TransactionHistoryProps) {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { 
    transactions, 
    transactionSummary, 
    loading, 
    pagination 
  } = useSelector((state: RootState) => state.transactions);
  
  const [filters, setFilters] = useState({
    start_date: '',
    end_date: '',
    type: '',
    page: 1,
    per_page: 10,
  });
  
  const [selectedTransaction, setSelectedTransaction] = useState<Transaction | null>(null);
  const [showDetails, setShowDetails] = useState(false);

  useEffect(() => {
    loadTransactions();
    loadSummary();
  }, [filters]);

  const loadTransactions = () => {
    dispatch(fetchTransactionHistory({
      member_id: memberId,
      ...filters,
    }) as any);
  };

  const loadSummary = () => {
    dispatch(fetchTransactionSummary({
      member_id: memberId,
      start_date: filters.start_date,
      end_date: filters.end_date,
    }) as any);
  };

  const handleFilterChange = (key: string, value: string) => {
    setFilters(prev => ({ ...prev, [key]: value, page: 1 }));
  };

  const handlePageChange = (page: number) => {
    setFilters(prev => ({ ...prev, page }));
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
      minute: '2-digit',
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
        return <DollarSign className="w-4 h-4 text-purple-600" />;
      case 'loan_repayment':
        return <CreditCard className="w-4 h-4 text-orange-600" />;
      default:
        return <DollarSign className="w-4 h-4 text-gray-600" />;
    }
  };

  const getStatusBadge = (status: string) => {
    const variants = {
      completed: 'default',
      pending: 'secondary',
      failed: 'destructive',
      reversed: 'outline',
    };
    
    return (
      <Badge variant={variants[status as keyof typeof variants] || 'default'}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  const getAmountColor = (type: string, amount: number) => {
    if (type === 'deposit' || type === 'loan_disbursement') {
      return 'text-green-600';
    }
    if (type === 'withdrawal' || type === 'loan_repayment') {
      return 'text-red-600';
    }
    return 'text-gray-600';
  };

  return (
    <div className="space-y-6">
      {/* Summary Cards */}
      {transactionSummary && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Total Transactions</p>
                  <p className="text-2xl font-bold">{transactionSummary.total_transactions}</p>
                </div>
                <DollarSign className="w-8 h-8 text-muted-foreground" />
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Total Deposits</p>
                  <p className="text-2xl font-bold text-green-600">
                    {formatCurrency(transactionSummary.total_deposits)}
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
                    {formatCurrency(transactionSummary.total_withdrawals)}
                  </p>
                </div>
                <ArrowDownRight className="w-8 h-8 text-red-600" />
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Net Cash Flow</p>
                  <p className={`text-2xl font-bold ${
                    transactionSummary.net_cash_flow >= 0 ? 'text-green-600' : 'text-red-600'
                  }`}>
                    {formatCurrency(transactionSummary.net_cash_flow)}
                  </p>
                </div>
                <TrendingUp className="w-8 h-8 text-muted-foreground" />
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Filters */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Filter className="w-5 h-5" />
            Transaction History
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
              <Label htmlFor="start_date">Start Date</Label>
              <Input
                id="start_date"
                type="date"
                value={filters.start_date}
                onChange={(e) => handleFilterChange('start_date', e.target.value)}
              />
            </div>
            
            <div>
              <Label htmlFor="end_date">End Date</Label>
              <Input
                id="end_date"
                type="date"
                value={filters.end_date}
                onChange={(e) => handleFilterChange('end_date', e.target.value)}
              />
            </div>
            
            <div>
              <Label htmlFor="type">Transaction Type</Label>
              <Select value={filters.type} onValueChange={(value) => handleFilterChange('type', value)}>
                <SelectTrigger>
                  <SelectValue placeholder="All types" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All types</SelectItem>
                  <SelectItem value="deposit">Deposit</SelectItem>
                  <SelectItem value="withdrawal">Withdrawal</SelectItem>
                  <SelectItem value="share_purchase">Share Purchase</SelectItem>
                  <SelectItem value="loan_disbursement">Loan Disbursement</SelectItem>
                  <SelectItem value="loan_repayment">Loan Repayment</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div>
              <Label htmlFor="per_page">Per Page</Label>
              <Select value={filters.per_page.toString()} onValueChange={(value) => handleFilterChange('per_page', value)}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="10">10</SelectItem>
                  <SelectItem value="25">25</SelectItem>
                  <SelectItem value="50">50</SelectItem>
                  <SelectItem value="100">100</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          
          <div className="flex gap-2">
            <Button onClick={loadTransactions} disabled={loading}>
              <Calendar className="w-4 h-4 mr-2" />
              Refresh
            </Button>
            <Button variant="outline" onClick={() => {/* Export functionality */}}>
              <Download className="w-4 h-4 mr-2" />
              Export
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Transaction Table */}
      <Card>
        <CardContent className="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Date</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Amount</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {transactions.map((transaction) => (
                <TableRow key={transaction.id}>
                  <TableCell>{formatDate(transaction.transaction_date)}</TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      {getTransactionIcon(transaction.type)}
                      <span className="capitalize">{transaction.type.replace('_', ' ')}</span>
                    </div>
                  </TableCell>
                  <TableCell className="max-w-xs truncate">
                    {transaction.description}
                  </TableCell>
                  <TableCell className={getAmountColor(transaction.type, transaction.amount)}>
                    {formatCurrency(transaction.amount)}
                  </TableCell>
                  <TableCell>{getStatusBadge(transaction.status)}</TableCell>
                  <TableCell>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => {
                        setSelectedTransaction(transaction);
                        setShowDetails(true);
                      }}
                    >
                      <Eye className="w-4 h-4" />
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
          
          {transactions.length === 0 && !loading && (
            <div className="text-center py-8 text-muted-foreground">
              No transactions found for the selected criteria.
            </div>
          )}
        </CardContent>
      </Card>

      {/* Pagination */}
      {pagination && pagination.last_page > 1 && (
        <div className="flex justify-center gap-2">
          <Button
            variant="outline"
            onClick={() => handlePageChange(pagination.current_page - 1)}
            disabled={pagination.current_page <= 1}
          >
            Previous
          </Button>
          
          <span className="flex items-center px-4">
            Page {pagination.current_page} of {pagination.last_page}
          </span>
          
          <Button
            variant="outline"
            onClick={() => handlePageChange(pagination.current_page + 1)}
            disabled={pagination.current_page >= pagination.last_page}
          >
            Next
          </Button>
        </div>
      )}

      {/* Transaction Details Modal */}
      <Dialog open={showDetails} onOpenChange={setShowDetails}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Transaction Details</DialogTitle>
          </DialogHeader>
          
          {selectedTransaction && (
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label>Transaction Number</Label>
                  <p className="font-mono text-sm">{selectedTransaction.transaction_number}</p>
                </div>
                <div>
                  <Label>Status</Label>
                  <div>{getStatusBadge(selectedTransaction.status)}</div>
                </div>
              </div>
              
              <div>
                <Label>Description</Label>
                <p>{selectedTransaction.description}</p>
              </div>
              
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label>Amount</Label>
                  <p className={`font-semibold ${getAmountColor(selectedTransaction.type, selectedTransaction.amount)}`}>
                    {formatCurrency(selectedTransaction.amount)}
                  </p>
                </div>
                <div>
                  <Label>Net Amount</Label>
                  <p className="font-semibold">{formatCurrency(selectedTransaction.net_amount)}</p>
                </div>
              </div>
              
              {selectedTransaction.balance_before && selectedTransaction.balance_after && (
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label>Balance Before</Label>
                    <p>{formatCurrency(selectedTransaction.balance_before)}</p>
                  </div>
                  <div>
                    <Label>Balance After</Label>
                    <p>{formatCurrency(selectedTransaction.balance_after)}</p>
                  </div>
                </div>
              )}
              
              <div>
                <Label>Transaction Date</Label>
                <p>{formatDate(selectedTransaction.transaction_date)}</p>
              </div>
              
              {selectedTransaction.payment_reference && (
                <div>
                  <Label>Payment Reference</Label>
                  <p className="font-mono text-sm">{selectedTransaction.payment_reference}</p>
                </div>
              )}
              
              {selectedTransaction.processedBy && (
                <div>
                  <Label>Processed By</Label>
                  <p>{selectedTransaction.processedBy.name}</p>
                </div>
              )}
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
}

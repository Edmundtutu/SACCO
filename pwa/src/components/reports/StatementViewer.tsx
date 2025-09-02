import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import { reportsAPI } from '@/api/reports';
import { useApiError } from '@/hooks/useApiError';
import { Download, FileText, Calendar } from 'lucide-react';
import type { MemberStatement } from '@/types/api';

export function StatementViewer() {
  const [statement, setStatement] = useState<MemberStatement | null>(null);
  const [loading, setLoading] = useState(false);
  const [downloading, setDownloading] = useState(false);
  const [dateRange, setDateRange] = useState({
    from_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // Last 30 days
    to_date: new Date().toISOString().split('T')[0],
  });

  const { handleError, handleSuccess } = useApiError();

  useEffect(() => {
    fetchStatement();
  }, []);

  const fetchStatement = async () => {
    setLoading(true);
    try {
      const response = await reportsAPI.getMemberStatement(dateRange);
      if (response.success && response.data) {
        setStatement(response.data);
      }
    } catch (error) {
      handleError(error, 'Failed to fetch statement');
    } finally {
      setLoading(false);
    }
  };

  const handleDownload = async () => {
    setDownloading(true);
    try {
      const blob = await reportsAPI.downloadStatement(dateRange);
      
      // Create download link
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `statement-${dateRange.from_date}-to-${dateRange.to_date}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
      
      handleSuccess('Statement downloaded successfully');
    } catch (error) {
      handleError(error, 'Failed to download statement');
    } finally {
      setDownloading(false);
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', {
      style: 'currency',
      currency: 'KES',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  return (
    <div className="space-y-6">
      {/* Date Range Filter */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calendar className="w-5 h-5" />
            Statement Period
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
              <Label htmlFor="from_date">From Date</Label>
              <Input
                id="from_date"
                type="date"
                value={dateRange.from_date}
                onChange={(e) => setDateRange({ ...dateRange, from_date: e.target.value })}
              />
            </div>
            <div>
              <Label htmlFor="to_date">To Date</Label>
              <Input
                id="to_date"
                type="date"
                value={dateRange.to_date}
                onChange={(e) => setDateRange({ ...dateRange, to_date: e.target.value })}
              />
            </div>
            <div className="flex gap-2">
              <Button onClick={fetchStatement} disabled={loading} className="flex-1">
                {loading ? 'Loading...' : 'Generate Statement'}
              </Button>
              <Button 
                variant="outline" 
                onClick={handleDownload} 
                disabled={downloading || !statement}
              >
                <Download className="w-4 h-4 mr-2" />
                {downloading ? 'Downloading...' : 'Download PDF'}
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Statement Content */}
      {loading ? (
        <Card>
          <CardContent className="p-6">
            <div className="space-y-4">
              <Skeleton className="h-6 w-48" />
              <Skeleton className="h-4 w-full" />
              <Skeleton className="h-4 w-3/4" />
              <Skeleton className="h-32 w-full" />
            </div>
          </CardContent>
        </Card>
      ) : statement ? (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FileText className="w-5 h-5" />
              Account Statement
            </CardTitle>
            <p className="text-sm text-muted-foreground">
              {new Date(statement.period.from).toLocaleDateString()} - {new Date(statement.period.to).toLocaleDateString()}
            </p>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* Summary */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div className="text-center p-4 bg-muted/50 rounded-lg">
                <p className="text-sm text-muted-foreground">Opening Balance</p>
                <p className="text-lg font-bold">{formatCurrency(statement.summary.opening_balance)}</p>
              </div>
              <div className="text-center p-4 bg-green-50 rounded-lg">
                <p className="text-sm text-muted-foreground">Total Deposits</p>
                <p className="text-lg font-bold text-green-600">{formatCurrency(statement.summary.total_deposits)}</p>
              </div>
              <div className="text-center p-4 bg-red-50 rounded-lg">
                <p className="text-sm text-muted-foreground">Total Withdrawals</p>
                <p className="text-lg font-bold text-red-600">{formatCurrency(statement.summary.total_withdrawals)}</p>
              </div>
              <div className="text-center p-4 bg-primary/10 rounded-lg">
                <p className="text-sm text-muted-foreground">Closing Balance</p>
                <p className="text-lg font-bold text-primary">{formatCurrency(statement.summary.closing_balance)}</p>
              </div>
            </div>

            {/* Transactions */}
            <div>
              <h3 className="font-medium mb-4">Transaction History</h3>
              <div className="space-y-2">
                {statement.transactions.map((transaction) => (
                  <div key={transaction.id} className="flex justify-between items-center p-3 bg-muted/30 rounded-lg">
                    <div>
                      <p className="font-medium">{transaction.description}</p>
                      <p className="text-sm text-muted-foreground">
                        {new Date(transaction.transaction_date).toLocaleDateString()} • {transaction.type}
                      </p>
                    </div>
                    <div className="text-right">
                      <p className={`font-bold ${transaction.type === 'deposit' ? 'text-green-600' : 'text-red-600'}`}>
                        {transaction.type === 'deposit' ? '+' : '-'}{formatCurrency(Math.abs(transaction.amount))}
                      </p>
                      <p className="text-sm text-muted-foreground">
                        Balance: {formatCurrency(transaction.balance_after)}
                      </p>
                    </div>
                  </div>
                ))}
                
                {statement.transactions.length === 0 && (
                  <p className="text-center text-muted-foreground py-8">
                    No transactions found for this period
                  </p>
                )}
              </div>
            </div>
          </CardContent>
        </Card>
      ) : (
        <Card>
          <CardContent className="flex flex-col items-center justify-center py-12">
            <FileText className="w-12 h-12 text-muted-foreground mb-4" />
            <h3 className="text-lg font-medium mb-2">No Statement Generated</h3>
            <p className="text-muted-foreground text-center">
              Select a date range and click "Generate Statement" to view your account statement.
            </p>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Award, TrendingUp, Calendar } from 'lucide-react';

interface Dividend {
  id: number;
  year: number;
  rate: number;
  amount: number;
  paid_date: string;
}

interface DividendHistoryProps {
  dividends: Dividend[];
  loading: boolean;
}

export function DividendHistory({ dividends, loading }: DividendHistoryProps) {
  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Dividend History</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {[1, 2, 3].map((i) => (
            <div key={i} className="flex items-center space-x-4">
              <Skeleton className="w-12 h-12 rounded-full" />
              <div className="flex-1 space-y-2">
                <Skeleton className="h-4 w-32" />
                <Skeleton className="h-3 w-24" />
              </div>
              <Skeleton className="h-6 w-20" />
            </div>
          ))}
        </CardContent>
      </Card>
    );
  }

  const totalDividends = dividends.reduce((sum, dividend) => sum + dividend.amount, 0);
  const averageRate = dividends.length > 0 
    ? dividends.reduce((sum, dividend) => sum + dividend.rate, 0) / dividends.length 
    : 0;

  return (
    <div className="space-y-6">
      {/* Summary Cards */}
      <div className="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Dividends</CardTitle>
            <Award className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-success">
              KES {totalDividends.toLocaleString()}
            </div>
            <p className="text-xs text-muted-foreground">
              All time earnings
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Average Rate</CardTitle>
            <TrendingUp className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-primary">
              {averageRate.toFixed(1)}%
            </div>
            <p className="text-xs text-muted-foreground">
              Historical average
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Payments</CardTitle>
            <Calendar className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-accent">
              {dividends.length}
            </div>
            <p className="text-xs text-muted-foreground">
              Years of dividends
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Dividend History */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Award className="w-5 h-5" />
            Dividend History
          </CardTitle>
        </CardHeader>
        <CardContent>
          {dividends.length === 0 ? (
            <div className="text-center py-8">
              <Award className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
              <h3 className="text-lg font-medium mb-2">No Dividends Yet</h3>
              <p className="text-muted-foreground">
                You haven't received any dividends yet. Dividends are typically paid annually based on SACCO performance.
              </p>
            </div>
          ) : (
            <div className="space-y-4">
              {dividends
                .sort((a, b) => b.year - a.year) // Sort by year descending
                .map((dividend) => (
                  <div 
                    key={dividend.id}
                    className="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                  >
                    <div className="flex items-center gap-4">
                      <div className="p-3 bg-success/10 rounded-full">
                        <Award className="w-6 h-6 text-success" />
                      </div>
                      <div>
                        <h3 className="font-medium">
                          {dividend.year} Dividend Payment
                        </h3>
                        <p className="text-sm text-muted-foreground">
                          Paid on {new Date(dividend.paid_date).toLocaleDateString()}
                        </p>
                        <Badge variant="outline" className="mt-1">
                          {dividend.rate}% dividend rate
                        </Badge>
                      </div>
                    </div>

                    <div className="text-right">
                      <p className="text-xl font-bold text-success">
                        KES {dividend.amount.toLocaleString()}
                      </p>
                      <p className="text-sm text-muted-foreground">
                        Amount received
                      </p>
                    </div>
                  </div>
                ))
              }
            </div>
          )}
        </CardContent>
      </Card>

      {/* Dividend Information */}
      <Card>
        <CardHeader>
          <CardTitle>About Dividends</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          <div className="p-4 bg-muted/50 rounded-lg">
            <h4 className="font-medium mb-2">How dividends work:</h4>
            <ul className="text-sm text-muted-foreground space-y-1">
              <li>• Dividends are paid annually based on SACCO performance</li>
              <li>• The rate depends on surplus income and member shareholding</li>
              <li>• Payments are typically made after the Annual General Meeting</li>
              <li>• Your dividend amount is calculated based on your share ownership</li>
            </ul>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
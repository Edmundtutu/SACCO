import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { fetchShares, fetchDividends } from '@/store/sharesSlice';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { SharesPurchase } from '@/components/shares/SharesPurchase';
import { DividendHistory } from '@/components/shares/DividendHistory';
import { SharesCertificate } from '@/components/shares/SharesCertificate';
import { TrendingUp, DollarSign, Award, FileText } from 'lucide-react';

export default function Shares() {
  const dispatch = useDispatch<AppDispatch>();
  const { account, dividends, loading } = useSelector((state: RootState) => state.shares);

  useEffect(() => {
    dispatch(fetchShares());
    dispatch(fetchDividends());
  }, [dispatch]);

  const currentValue = account?.total_value || 0;
  const totalDividends = account?.dividends_earned || 0;
  const shareCount = account?.total_shares || 0;
  const shareValue = account?.share_value || 0;

  return (
    <div className="p-4 space-y-6 max-w-6xl mx-auto">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-heading font-bold text-foreground">Shares</h1>
          <p className="text-muted-foreground">Manage your shares and view dividends</p>
        </div>
      </div>

      {/* Shares Overview */}
      <div className="grid gap-6 md:grid-cols-3">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Shares</CardTitle>
            <TrendingUp className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-primary">{shareCount.toLocaleString()}</div>
            <p className="text-xs text-muted-foreground">
              @ UGX {shareValue.toLocaleString()} per share
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Portfolio Value</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-success">UGX {currentValue.toLocaleString()}</div>
            <p className="text-xs text-muted-foreground">
              Current market value
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Dividends</CardTitle>
            <Award className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-accent">UGX {totalDividends.toLocaleString()}</div>
            <p className="text-xs text-muted-foreground">
              {account?.last_dividend_date ? 
                `Last: ${new Date(account.last_dividend_date).toLocaleDateString()}` : 
                'No dividends yet'
              }
            </p>
          </CardContent>
        </Card>
      </div>

      <Tabs defaultValue="overview" className="space-y-6">
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="buy">Buy Shares</TabsTrigger>
          <TabsTrigger value="dividends">Dividends</TabsTrigger>
          <TabsTrigger value="certificates">Certificates</TabsTrigger>
        </TabsList>

        <TabsContent value="overview">
          <div className="grid gap-6 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Shares Portfolio</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {account ? (
                  <div className="space-y-4">
                    <div className="p-4 bg-muted/50 rounded-lg">
                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <p className="text-sm text-muted-foreground">Shares Owned</p>
                          <p className="text-xl font-bold">{account.total_shares.toLocaleString()}</p>
                        </div>
                        <div>
                          <p className="text-sm text-muted-foreground">Share Value</p>
                          <p className="text-xl font-bold">UGX {account.share_value.toLocaleString()}</p>
                        </div>
                      </div>
                    </div>
                    
                    <div className="p-4 border rounded-lg">
                      <h4 className="font-medium mb-2">Investment Summary</h4>
                      <div className="space-y-2 text-sm">
                        <div className="flex justify-between">
                          <span>Current Value:</span>
                          <span className="font-medium">UGX {account.total_value.toLocaleString()}</span>
                        </div>
                        <div className="flex justify-between">
                          <span>Dividends Earned:</span>
                          <span className="font-medium text-success">UGX {account.dividends_earned.toLocaleString()}</span>
                        </div>
                        <div className="flex justify-between border-t pt-2">
                          <span className="font-medium">Total Return:</span>
                          <span className="font-bold text-primary">
                            UGX {(account.total_value + account.dividends_earned).toLocaleString()}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <p className="text-muted-foreground mb-4">No shares owned yet</p>
                    <Button>Buy Your First Shares</Button>
                  </div>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Recent Dividends</CardTitle>
              </CardHeader>
              <CardContent>
                {dividends.length > 0 ? (
                  <div className="space-y-3">
                    {dividends.slice(0, 3).map((dividend) => (
                      <div key={dividend.id} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                        <div>
                          <p className="font-medium">{dividend.year}</p>
                          <p className="text-sm text-muted-foreground">{dividend.rate}% rate</p>
                        </div>
                        <div className="text-right">
                          <p className="font-bold text-success">UGX {dividend.amount.toLocaleString()}</p>
                          <p className="text-xs text-muted-foreground">
                            {new Date(dividend.paid_date).toLocaleDateString()}
                          </p>
                        </div>
                      </div>
                    ))}
                    
                    {dividends.length > 3 && (
                      <Button variant="outline" className="w-full" size="sm">
                        View All Dividends
                      </Button>
                    )}
                  </div>
                ) : (
                  <p className="text-muted-foreground text-center py-4">No dividends yet</p>
                )}
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="buy">
          <SharesPurchase currentShares={shareCount} shareValue={shareValue} />
        </TabsContent>

        <TabsContent value="dividends">
          <DividendHistory dividends={dividends} loading={loading} />
        </TabsContent>

        <TabsContent value="certificates">
          <SharesCertificate account={account} />
        </TabsContent>
      </Tabs>
    </div>
  );
}
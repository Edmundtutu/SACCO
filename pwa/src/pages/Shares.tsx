import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '@/store';
import { fetchShares } from '@/store/sharesSlice';
import { getShareAccount } from '@/utils/accountHelpers';
import type { ShareAccount } from '@/types/api';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { SharesPurchase } from '@/components/shares/SharesPurchase';
import { SharesCertificate } from '@/components/shares/SharesCertificate';
import { DividendHistory } from '@/components/shares/DividendHistory';
import { TransactionHistory } from '@/components/transactions/TransactionHistory';
import { DashboardPage } from '@/components/layout/DashboardPage';
import { 
  TrendingUp, 
  PieChart, 
  FileText,
  History,
  Award,
  DollarSign
} from 'lucide-react';

export default function Shares() {
  const dispatch = useDispatch<AppDispatch>();
  const { user } = useSelector((state: RootState) => state.auth);
  const { account: sharesAccountWrapper = null, loading } = useSelector((state: RootState) => state.shares);
  
  // Extract ShareAccount from polymorphic wrapper
  const sharesAccount: ShareAccount | null = sharesAccountWrapper ? getShareAccount(sharesAccountWrapper) : null;
  
  const [purchaseModalOpen, setPurchaseModalOpen] = useState(false);

  useEffect(() => {
    dispatch(fetchShares());
  }, [dispatch]);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const totalShares = sharesAccount?.share_units || 0;
  const shareValue = sharesAccount?.share_price || 1000;
  const totalValue = sharesAccount?.total_share_value || 0;
  const dividendsEarned = sharesAccount?.dividends_earned || 0;

  const toolbarActions = (
    <Button 
      onClick={() => setPurchaseModalOpen(true)}
      className="bg-primary hover:bg-primary/90"
    >
      <TrendingUp className="w-4 h-4 mr-2" />
      Buy Shares
    </Button>
  );

  return (
    <DashboardPage 
      title="Shares" 
      subtitle="Manage your share capital and dividends"
      toolbarActions={toolbarActions}
    >
      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Total Shares</p>
                <p className="text-2xl font-bold font-heading">{totalShares.toLocaleString()}</p>
              </div>
              <div className="h-12 w-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                <PieChart className="h-6 w-6 text-blue-600" />
              </div>
            </div>
            <div className="mt-4">
              <p className="text-xs text-muted-foreground">
                @ {formatCurrency(shareValue)} per share
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Total Value</p>
                <p className="text-2xl font-bold font-heading">{formatCurrency(totalValue)}</p>
              </div>
              <div className="h-12 w-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                <DollarSign className="h-6 w-6 text-green-600" />
              </div>
            </div>
            <div className="mt-4">
              <p className="text-xs text-muted-foreground">
                Current market value
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Dividends Earned</p>
                <p className="text-2xl font-bold font-heading">{formatCurrency(dividendsEarned)}</p>
              </div>
              <div className="h-12 w-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                <Award className="h-6 w-6 text-purple-600" />
              </div>
            </div>
            <div className="mt-4">
              <p className="text-xs text-muted-foreground">
                This year
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground font-medium">Share Price</p>
                <p className="text-2xl font-bold font-heading">{formatCurrency(shareValue)}</p>
              </div>
              <div className="h-12 w-12 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                <TrendingUp className="h-6 w-6 text-orange-600" />
              </div>
            </div>
            <div className="mt-4">
              <p className="text-xs text-muted-foreground">
                Current price
              </p>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Main Content */}
      <Tabs defaultValue="overview" className="space-y-4">
        <TabsList className="grid w-full grid-cols-5">
          <TabsTrigger value="overview" className="flex items-center gap-2">
            <PieChart className="w-4 h-4" />
            Overview
          </TabsTrigger>
          <TabsTrigger value="purchase" className="flex items-center gap-2">
            <TrendingUp className="w-4 h-4" />
            Buy Shares
          </TabsTrigger>
          <TabsTrigger value="certificate" className="flex items-center gap-2">
            <FileText className="w-4 h-4" />
            Certificate
          </TabsTrigger>
          <TabsTrigger value="dividends" className="flex items-center gap-2">
            <Award className="w-4 h-4" />
            Dividends
          </TabsTrigger>
          <TabsTrigger value="transactions" className="flex items-center gap-2">
            <History className="w-4 h-4" />
            Transactions
          </TabsTrigger>
        </TabsList>

        <TabsContent value="overview" className="space-y-4">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Share Portfolio */}
            <Card>
              <CardHeader>
                <CardTitle>Share Portfolio</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center justify-between">
                  <span className="text-muted-foreground">Total Shares Owned</span>
                  <span className="font-semibold">{totalShares.toLocaleString()} shares</span>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-muted-foreground">Share Price</span>
                  <span className="font-semibold">{formatCurrency(shareValue)}</span>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-muted-foreground">Total Value</span>
                  <span className="font-semibold text-primary">{formatCurrency(totalValue)}</span>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-muted-foreground">Dividends Earned</span>
                  <span className="font-semibold text-green-600">{formatCurrency(dividendsEarned)}</span>
                </div>
                
                <div className="pt-4 border-t">
                  <div className="flex gap-2">
                    <Button 
                      size="sm" 
                      className="flex-1"
                      onClick={() => setPurchaseModalOpen(true)}
                    >
                      Buy More Shares
                    </Button>
                    <Button size="sm" variant="outline">
                      View Certificate
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Benefits */}
            <Card>
              <CardHeader>
                <CardTitle>Shareholder Benefits</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="flex items-start gap-3">
                    <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                      <Award className="w-4 h-4 text-green-600" />
                    </div>
                    <div>
                      <h4 className="font-medium">Annual Dividends</h4>
                      <p className="text-sm text-muted-foreground">
                        Earn dividends based on SACCO performance and your shareholding
                      </p>
                    </div>
                  </div>
                  
                  <div className="flex items-start gap-3">
                    <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                      <FileText className="w-4 h-4 text-blue-600" />
                    </div>
                    <div>
                      <h4 className="font-medium">Voting Rights</h4>
                      <p className="text-sm text-muted-foreground">
                        Participate in Annual General Meetings and vote on important decisions
                      </p>
                    </div>
                  </div>
                  
                  <div className="flex items-start gap-3">
                    <div className="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                      <TrendingUp className="w-4 h-4 text-purple-600" />
                    </div>
                    <div>
                      <h4 className="font-medium">Capital Growth</h4>
                      <p className="text-sm text-muted-foreground">
                        Benefit from SACCO growth and potential share value appreciation
                      </p>
                    </div>
                  </div>
                  
                  <div className="flex items-start gap-3">
                    <div className="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                      <DollarSign className="w-4 h-4 text-orange-600" />
                    </div>
                    <div>
                      <h4 className="font-medium">Loan Eligibility</h4>
                      <p className="text-sm text-muted-foreground">
                        Higher shareholding may improve loan eligibility and terms
                      </p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="purchase" className="space-y-4">
          <SharesPurchase 
            currentShares={totalShares}
            shareValue={shareValue}
          />
        </TabsContent>

        <TabsContent value="certificate" className="space-y-4">
          <SharesCertificate account={sharesAccount} certificates={[]} />
        </TabsContent>

        <TabsContent value="dividends" className="space-y-4">
          <DividendHistory dividends={[]} loading={false} />
        </TabsContent>

        <TabsContent value="transactions" className="space-y-4">
          <TransactionHistory memberId={user?.id || 0} context="shares" />
        </TabsContent>
      </Tabs>

      {/* Purchase Modal */}
      {purchaseModalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="p-6">
              <div className="flex items-center justify-between mb-4">
                <h2 className="text-xl font-semibold">Buy Shares</h2>
                <Button 
                  variant="outline" 
                  size="sm"
                  onClick={() => setPurchaseModalOpen(false)}
                >
                  ×
                </Button>
              </div>
              <SharesPurchase 
                currentShares={totalShares}
                shareValue={shareValue}
              />
            </div>
          </div>
        </div>
      )}
    </DashboardPage>
  );
}
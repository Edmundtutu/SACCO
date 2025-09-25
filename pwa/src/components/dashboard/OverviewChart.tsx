import { PieChart, Pie, Cell, ResponsiveContainer, Legend, Tooltip, type TooltipProps } from 'recharts';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useSelector } from 'react-redux';
import { RootState } from '@/store';
import { User } from 'lucide-react';

export function OverviewChart() {
  const { accounts: savingsAccounts } = useSelector((state: RootState) => state.savings);
  const { loans } = useSelector((state: RootState) => state.loans);
  const { account: sharesAccount } = useSelector((state: RootState) => state.shares);

  const totalSavings = savingsAccounts.reduce((sum, account) => sum + account.balance, 0);
  const totalLoans = loans.reduce((sum, loan) => sum + loan.outstanding_balance, 0);
  const totalShares = sharesAccount?.total_value || 0;

  const data = [
    {
      name: 'Savings',
      value: totalSavings,
      color: 'hsl(var(--chart-savings))',
    },
    {
      name: 'Outstanding Loans',
      value: totalLoans,
      color: 'hsl(var(--chart-loans))',
    },
    {
      name: 'Share Value',
      value: totalShares,
      color: 'hsl(var(--chart-shares))',
    },
  ].filter(item => item.value > 0);

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-UG', {
      style: 'currency',
      currency: 'UGX',
      minimumFractionDigits: 0,
    }).format(value);
  };

  const CustomTooltip = ({ active, payload }: TooltipProps<number, string>) => {
    if (active && payload && payload.length) {
      const data = payload[0];
      return (
        <div className="bg-card border border-border rounded-lg p-3 shadow-lg">
          <p className="font-medium">{String(data.name)}</p>
          <p className="text-primary font-bold">{formatCurrency(Number(data.value))}</p>
        </div>
      );
    }
    return null;
  };

  if (data.length === 0) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="font-heading">Portfolio Overview</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-center h-64 text-muted-foreground">
            <p>No financial data available</p>
          </div>
        </CardContent>
      </Card>
    );
  }

  // Calculate dynamic positioning based on available data and screen size
  const getPositions = (dataItems, isMobile = false) => {
    const positions = [];
    const itemCount = dataItems.length;
    const offset = isMobile ? 90 : 120; // Reduced offset for mobile
    const verticalOffset = isMobile ? 100 : 140;
    
    if (itemCount === 1) {
      positions.push({ angle: 0, x: 0, y: -verticalOffset }); // Top center
    } else if (itemCount === 2) {
      positions.push({ angle: -90, x: -offset, y: 0 }); // Left
      positions.push({ angle: 90, x: offset, y: 0 });   // Right
    } else if (itemCount === 3) {
      positions.push({ angle: -120, x: -offset, y: -50 }); // Top left
      positions.push({ angle: 120, x: offset, y: -50 });   // Top right  
      positions.push({ angle: 0, x: 0, y: verticalOffset }); // Bottom
    }
    
    return positions;
  };

  const isMobile = window.innerWidth < 640; // sm breakpoint
  const positions = getPositions(data, isMobile);

  return (
    <Card className="w-full">
      <CardContent className="p-3 sm:p-6">
        <div className="relative flex items-center justify-center w-full overflow-hidden">
          {/* Circular Progress Chart */}
          <div className="relative w-64 h-64 sm:w-80 sm:h-80 max-w-full">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={data}
                  cx="50%"
                  cy="50%"
                  innerRadius={isMobile ? 60 : 80}
                  outerRadius={isMobile ? 90 : 120}
                  paddingAngle={data.length > 1 ? 2 : 0}
                  dataKey="value"
                  startAngle={90}
                  endAngle={450}
                >
                  {data.map((entry, index) => (
                    <Cell 
                      key={`cell-${index}`} 
                      fill={entry.color}
                      stroke="white"
                      strokeWidth={isMobile ? 1.5 : 2}
                    />
                  ))}
                </Pie>
                <Tooltip content={<CustomTooltip />} />
              </PieChart>
            </ResponsiveContainer>
            
            {/* Central Avatar */}
            <div className="absolute inset-0 flex items-center justify-center">
              <div className={`${isMobile ? 'w-14 h-14' : 'w-20 h-20'} rounded-full bg-gradient-to-br from-primary/80 to-primary shadow-lg flex items-center justify-center`}>
                <User className={`${isMobile ? 'w-7 h-7' : 'w-10 h-10'} text-white`} />
              </div>
            </div>

            {/* Dynamic Positioned Labels */}
            {data.map((item, index) => {
              const position = positions[index];
              const isLeft = position.x < 0;
              const isRight = position.x > 0;
              const isCenter = position.x === 0;
              
              return (
                <div
                  key={item.name}
                  className="absolute"
                  style={{
                    left: '50%',
                    top: '50%',
                    transform: `translate(calc(-50% + ${position.x}px), calc(-50% + ${position.y}px))`
                  }}
                >
                  <div className={`${isLeft ? 'text-right' : isRight ? 'text-left' : 'text-center'} max-w-24 sm:max-w-none`}>
                    <div className={`flex items-center gap-1 sm:gap-2 mb-1 ${
                      isLeft ? 'justify-end' : isRight ? 'justify-start' : 'justify-center'
                    }`}>
                      {!isRight && (
                        <>
                          <span className={`font-bold text-xs sm:text-sm text-foreground ${isMobile ? 'leading-tight' : ''}`}>
                            {item.name === 'Outstanding Loans' ? 'Loans' : item.name}
                          </span>
                          <div 
                            className={`${isMobile ? 'w-3 h-3' : 'w-4 h-4'} rounded-full flex-shrink-0`}
                            style={{ backgroundColor: item.color }}
                          />
                        </>
                      )}
                      {isRight && (
                        <>
                          <div 
                            className={`${isMobile ? 'w-3 h-3' : 'w-4 h-4'} rounded-full flex-shrink-0`}
                            style={{ backgroundColor: item.color }}
                          />
                          <span className={`font-bold text-xs sm:text-sm text-foreground ${isMobile ? 'leading-tight' : ''}`}>
                            {item.name === 'Outstanding Loans' ? 'Loans' : item.name}
                          </span>
                        </>
                      )}
                    </div>
                    <p className={`font-bold text-sm sm:text-lg ${isMobile ? 'leading-tight' : ''} ${
                      item.name === 'Outstanding Loans' ? 'text-destructive' : 
                      item.name === 'Savings' ? 'text-primary' : 'text-green-600'
                    }`}>
                      {isMobile 
                        ? formatCurrency(item.value).replace('UGX', '').replace(/,/g, 'K').slice(0, -3) 
                        : formatCurrency(item.value)
                      }
                    </p>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Total Portfolio Value */}
        <div className="mt-8 sm:mt-16 pt-4 sm:pt-6 border-t border-border">
          <div className="text-center">
            <p className="text-xs sm:text-sm text-muted-foreground mb-1 sm:mb-2">Total Portfolio Value</p>
            <p className="text-xl sm:text-3xl font-bold text-foreground">
              {formatCurrency(data.reduce((sum, item) => sum + (item.value || 0), 0) || 0)}
            </p>
          </div>
        </div>

        {/* Quick Stats Grid */}
        <div className="grid grid-cols-3 gap-2 sm:gap-4 mt-4 sm:mt-6">
          <div className="text-center p-2 sm:p-3 bg-accent/30 rounded-lg border">
            <p className="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wide mb-1">Assets</p>
            <p className="font-semibold text-green-600 text-xs sm:text-base">
              {isMobile 
                ? formatCurrency(totalSavings + totalShares).replace('UGX', '').replace(/,/g, 'K').slice(0, -3)
                : formatCurrency(totalSavings + totalShares)
              }
            </p>
          </div>
          <div className="text-center p-2 sm:p-3 bg-accent/30 rounded-lg border">
            <p className="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wide mb-1">Liabilities</p>
            <p className="font-semibold text-destructive text-xs sm:text-base">
              {isMobile 
                ? formatCurrency(totalLoans).replace('UGX', '').replace(/,/g, 'K').slice(0, -3) 
                : formatCurrency(totalLoans)
              }
            </p>
          </div>
          <div className="text-center p-2 sm:p-3 bg-accent/30 rounded-lg border">
            <p className="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wide mb-1">Net</p>
            <p className="font-semibold text-primary text-xs sm:text-base">
              {isMobile 
                ? formatCurrency((totalSavings + totalShares) - totalLoans).replace('UGX', '').replace(/,/g, 'K').slice(0, -3) 
                : formatCurrency((totalSavings + totalShares) - totalLoans)
              }
            </p>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
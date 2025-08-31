import { PieChart, Pie, Cell, ResponsiveContainer, Legend, Tooltip } from 'recharts';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useSelector } from 'react-redux';
import { RootState } from '@/store';

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
    return new Intl.NumberFormat('en-KE', {
      style: 'currency',
      currency: 'KES',
      minimumFractionDigits: 0,
    }).format(value);
  };

  const CustomTooltip = ({ active, payload }: any) => {
    if (active && payload && payload.length) {
      const data = payload[0];
      return (
        <div className="bg-card border border-border rounded-lg p-3 shadow-lg">
          <p className="font-medium">{data.name}</p>
          <p className="text-primary font-bold">{formatCurrency(data.value)}</p>
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

  return (
    <Card>
      <CardHeader>
        <CardTitle className="font-heading">Portfolio Overview</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="h-64">
          <ResponsiveContainer width="100%" height="100%">
            <PieChart>
              <Pie
                data={data}
                cx="50%"
                cy="50%"
                innerRadius={60}
                outerRadius={100}
                paddingAngle={5}
                dataKey="value"
              >
                {data.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip content={<CustomTooltip />} />
              <Legend 
                verticalAlign="bottom" 
                height={36}
                formatter={(value: string, entry: any) => (
                  <span style={{ color: entry.color }} className="font-medium">
                    {value}
                  </span>
                )}
              />
            </PieChart>
          </ResponsiveContainer>
        </div>
        
        {/* Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
          {data.map((item) => (
            <div key={item.name} className="text-center p-3 bg-accent/50 rounded-lg">
              <div 
                className="w-4 h-4 rounded mx-auto mb-2"
                style={{ backgroundColor: item.color }}
              />
              <p className="text-sm text-muted-foreground">{item.name}</p>
              <p className="font-bold text-lg">{formatCurrency(item.value)}</p>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
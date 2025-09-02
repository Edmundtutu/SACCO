import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import { Target, TrendingUp } from 'lucide-react';

interface SavingsProgressProps {
  currentAmount: number;
  targetAmount: number;
  progressPercentage: number;
}

export function SavingsProgress({ currentAmount, targetAmount, progressPercentage }: SavingsProgressProps) {
  const remaining = Math.max(0, targetAmount - currentAmount);
  const isGoalReached = progressPercentage >= 100;

  return (
    <Card className="bg-gradient-to-r from-primary/10 to-success/10 border-primary/20">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Target className="w-5 h-5 text-primary" />
          Monthly Savings Goal
          {isGoalReached && <Badge className="ml-2">🎯 Goal Reached!</Badge>}
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="flex justify-between items-end">
          <div>
            <p className="text-2xl font-bold text-primary">
              UGX {currentAmount.toLocaleString()}
            </p>
            <p className="text-sm text-muted-foreground">
              of UGX {targetAmount.toLocaleString()} target
            </p>
          </div>
          <div className="text-right">
            <p className="text-lg font-semibold text-success">
              {Math.round(progressPercentage)}%
            </p>
            <p className="text-xs text-muted-foreground">Progress</p>
          </div>
        </div>

        <div className="space-y-2">
          <Progress value={progressPercentage} className="h-3" />
          <div className="flex justify-between text-xs text-muted-foreground">
            <span>0</span>
            <span>Target: {targetAmount.toLocaleString()}</span>
          </div>
        </div>

        {!isGoalReached && (
          <div className="p-3 bg-background/50 rounded-lg">
            <div className="flex items-center gap-2">
              <TrendingUp className="w-4 h-4 text-accent" />
              <p className="text-sm">
                <span className="font-medium">UGX {remaining.toLocaleString()}</span> more to reach your goal
              </p>
            </div>
          </div>
        )}

        {isGoalReached && (
          <div className="p-3 bg-success/10 rounded-lg border border-success/20">
            <p className="text-sm text-success font-medium">
              🎉 Congratulations! You've reached your monthly savings goal!
            </p>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
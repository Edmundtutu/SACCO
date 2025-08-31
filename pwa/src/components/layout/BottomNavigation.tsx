import { Link, useLocation } from 'react-router-dom';
import { Home, PiggyBank, CreditCard, TrendingUp, User } from 'lucide-react';
import { cn } from '@/lib/utils';

const navigationItems = [
  { icon: Home, label: 'Home', path: '/' },
  { icon: PiggyBank, label: 'Savings', path: '/savings' },
  { icon: CreditCard, label: 'Loans', path: '/loans' },
  { icon: TrendingUp, label: 'Shares', path: '/shares' },
  { icon: User, label: 'Profile', path: '/profile' },
];

export function BottomNavigation() {
  const location = useLocation();

  return (
    <nav className="fixed bottom-0 left-0 right-0 bg-card border-t border-border z-50 md:hidden">
      <div className="flex items-center justify-around py-2 px-1">
        {navigationItems.map(({ icon: Icon, label, path }) => {
          const isActive = location.pathname === path;
          return (
            <Link
              key={path}
              to={path}
              className={cn(
                "flex flex-col items-center gap-1 py-2 px-3 rounded-lg transition-colors min-w-0 flex-1",
                isActive 
                  ? "text-primary bg-primary/10" 
                  : "text-muted-foreground hover:text-foreground hover:bg-accent"
              )}
            >
              <Icon className="w-5 h-5" />
              <span className="text-xs font-medium truncate">{label}</span>
            </Link>
          );
        })}
      </div>
    </nav>
  );
}
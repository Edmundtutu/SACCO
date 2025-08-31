import { ReactNode } from 'react';
import { Sidebar } from './Sidebar';
import { BottomNavigation } from './BottomNavigation';
import { useSelector } from 'react-redux';
import { RootState } from '@/store';

interface DashboardLayoutProps {
  children: ReactNode;
}

export function DashboardLayout({ children }: DashboardLayoutProps) {
  const { isAuthenticated } = useSelector((state: RootState) => state.auth);

  if (!isAuthenticated) {
    return <>{children}</>;
  }

  return (
    <div className="min-h-screen bg-background">
      <div className="flex">
        {/* Desktop Sidebar */}
        <div className="hidden md:block">
          <Sidebar />
        </div>
        
        {/* Main Content */}
        <main className="flex-1 pb-16 md:pb-0">
          {children}
        </main>
      </div>
      
      {/* Mobile Bottom Navigation */}
      <BottomNavigation />
    </div>
  );
}
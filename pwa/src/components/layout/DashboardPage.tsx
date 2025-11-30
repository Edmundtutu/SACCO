import { ReactNode } from 'react';
import { MobileToolbar } from './MobileToolbar';
import { useSelector } from 'react-redux';
import { RootState } from '@/store';

interface DashboardPageProps {
  title: string;
  subtitle?: string;
  toolbarActions?: ReactNode;
  mobileActions?: ReactNode;
  onNotificationClick?: () => void;
  children: ReactNode;
}

export function DashboardPage({ 
  title, 
  subtitle, 
  toolbarActions, 
  mobileActions,
  onNotificationClick,
  children 
}: DashboardPageProps) {
  const { user } = useSelector((state: RootState) => state.auth);

  return (
    <>
      {/* Mobile Toolbar */}
      <MobileToolbar 
        title={title} 
        user={user}
        showNotifications={true}
        onNotificationClick={onNotificationClick}
      />

      <div className="p-4 md:p-6 space-y-4 md:space-y-6 overflow-x-hidden">
        {/* Desktop Header */}
        <div className="hidden md:block">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="font-heading text-2xl md:text-3xl font-bold">{title}</h1>
              {subtitle && <p className="text-muted-foreground mt-1">{subtitle}</p>}
            </div>
            {toolbarActions && <div className="flex gap-2">{toolbarActions}</div>}
          </div>
        </div>

        {/* Mobile Header (only show if subtitle and no toolbar already rendered it) */}
        {/* Mobile Action Buttons (optional) */}
        {mobileActions && (
          <div className="md:hidden flex gap-3">
            {mobileActions}
          </div>
        )}

        {/* Page Content */}
        {children}
      </div>
    </>
  );
}

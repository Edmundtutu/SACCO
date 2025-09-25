import { useState } from 'react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Menu, Bell, Settings } from 'lucide-react';
import { NavigationDrawer } from './NavigationDrawer';

interface MobileToolbarProps {
  title: string;
  user?: {
    name?: string;
    avatar?: string;
  };
  showNotifications?: boolean;
  onNotificationClick?: () => void;
}

export function MobileToolbar({ 
  title, 
  user, 
  showNotifications = true, 
  onNotificationClick 
}: MobileToolbarProps) {
  const [isDrawerOpen, setIsDrawerOpen] = useState(false);

  const handleAvatarClick = () => {
    setIsDrawerOpen(true);
  };

  const handleNotificationClick = () => {
    if (onNotificationClick) {
      onNotificationClick();
    }
  };

  return (
    <>
      {/* Mobile Toolbar */}
      <div className="md:hidden fixed top-0 left-0 right-0 z-[80] bg-primary text-primary-foreground shadow-lg">
        <div className="flex items-center justify-between px-4 py-3 h-16">
          {/* Left side - Menu button (optional) */}
          <div className="flex items-center">
            <Button
              variant="ghost"
              size="sm"
              className="text-primary-foreground hover:bg-primary-foreground/10 p-2"
              onClick={handleAvatarClick}
            >
              <Menu className="h-5 w-5" />
            </Button>
          </div>

          {/* Center - Title */}
          <div className="flex-1 text-center">
            <h1 className="text-lg font-semibold truncate">
              {title}
            </h1>
          </div>

          {/* Right side - Notifications and Avatar */}
          <div className="flex items-center space-x-2">
            {showNotifications && (
              <Button
                variant="ghost"
                size="sm"
                className="text-primary-foreground hover:bg-primary-foreground/10 p-2 relative"
                onClick={handleNotificationClick}
              >
                <Bell className="h-5 w-5" />
                {/* Notification badge */}
                <span className="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full text-xs flex items-center justify-center text-white">
                  {/* You can add notification count here */}
                </span>
              </Button>
            )}
            
            <Button
              variant="ghost"
              size="sm"
              className="p-0 hover:bg-primary-foreground/10"
              onClick={handleAvatarClick}
            >
              <Avatar className="h-8 w-8">
                <AvatarImage src={user?.avatar} />
                <AvatarFallback className="bg-primary-foreground/20 text-primary-foreground text-sm font-bold">
                  {user?.name?.charAt(0).toUpperCase() || 'U'}
                </AvatarFallback>
              </Avatar>
            </Button>
          </div>
        </div>
      </div>

      {/* Spacer for fixed toolbar */}
      <div className="md:hidden h-16 z-[80]" />

      {/* Navigation Drawer */}
      <NavigationDrawer 
        isOpen={isDrawerOpen} 
        onClose={() => setIsDrawerOpen(false)}
        user={user}
      />
    </>
  );
}

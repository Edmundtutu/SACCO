import { useNavigate } from 'react-router-dom';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { 
  X, 
  User, 
  Settings, 
  Bell, 
  HelpCircle, 
  LogOut, 
  Shield,
  CreditCard,
  PieChart
} from 'lucide-react';
import { useDispatch } from 'react-redux';
import { AppDispatch } from '@/store';
import { logoutUser } from '@/store/authSlice';

interface NavigationDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  user?: {
    name?: string;
    email?: string;
    avatar?: string;
    member_number?: string;
  };
}

export function NavigationDrawer({ isOpen, onClose, user }: NavigationDrawerProps) {
  const navigate = useNavigate();
  const dispatch = useDispatch<AppDispatch>();

  const handleLogout = () => {
    dispatch(logoutUser());
    navigate('/login');
    onClose();
  };

  const handleNavigation = (path: string) => {
    navigate(path);
    onClose();
  };

  const menuItems = [
    {
      icon: User,
      label: 'Profile',
      path: '/profile',
      description: 'Manage your account'
    },
    {
      icon: CreditCard,
      label: 'Payment Methods',
      path: '/profile/payment-methods',
      description: 'Manage payment options'
    },
    {
      icon: PieChart,
      label: 'Reports',
      path: '/reports',
      description: 'View your reports'
    },
    {
      icon: Bell,
      label: 'Notifications',
      path: '/profile/notifications',
      description: 'Notification preferences'
    },
    {
      icon: Settings,
      label: 'Settings',
      path: '/profile/settings',
      description: 'App preferences'
    },
    {
      icon: Shield,
      label: 'Security',
      path: '/profile/security',
      description: 'Privacy & security'
    },
    {
      icon: HelpCircle,
      label: 'Help & Support',
      path: '/help',
      description: 'Get help and support'
    }
  ];

  return (
    <>
      {/* Backdrop */}
      {isOpen && (
        <div 
          className="fixed inset-0 bg-black/50 z-[60] md:hidden"
          onClick={onClose}
        />
      )}

      {/* Drawer */}
      <div className={`
        fixed top-0 left-0 h-full w-80 max-w-[85vw] bg-background shadow-2xl z-[70]
        transform transition-transform duration-300 ease-in-out md:hidden
        ${isOpen ? 'translate-x-0' : '-translate-x-full'}
      `}>
        {/* Header */}
        <div className="bg-primary text-primary-foreground p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold">Menu</h2>
            <Button
              variant="ghost"
              size="sm"
              className="text-primary-foreground hover:bg-primary-foreground/10 p-2"
              onClick={onClose}
            >
              <X className="h-5 w-5" />
            </Button>
          </div>

          {/* User Info */}
          <div className="flex items-center space-x-3">
            <Avatar className="h-12 w-12">
              <AvatarImage src={user?.avatar} />
              <AvatarFallback className="bg-primary-foreground/20 text-primary-foreground text-lg font-bold">
                {user?.name?.charAt(0).toUpperCase() || 'U'}
              </AvatarFallback>
            </Avatar>
            <div className="flex-1 min-w-0">
              <p className="font-semibold truncate">{user?.name || 'User'}</p>
              <p className="text-sm text-primary-foreground/80 truncate">
                {user?.email || 'user@example.com'}
              </p>
              {user?.member_number && (
                <p className="text-xs text-primary-foreground/60">
                  Member #{user.member_number}
                </p>
              )}
            </div>
          </div>
        </div>

        {/* Menu Items */}
        <div className="flex-1 overflow-y-auto py-4">
          <div className="space-y-1 px-2">
            {menuItems.map((item) => {
              const IconComponent = item.icon;
              return (
                <Button
                  key={item.path}
                  variant="ghost"
                  className="w-full justify-start h-auto p-4 text-left"
                  onClick={() => handleNavigation(item.path)}
                >
                  <div className="flex items-center space-x-3 w-full">
                    <IconComponent className="h-5 w-5 text-muted-foreground flex-shrink-0" />
                    <div className="flex-1 min-w-0">
                      <p className="font-medium">{item.label}</p>
                      <p className="text-sm text-muted-foreground truncate">
                        {item.description}
                      </p>
                    </div>
                  </div>
                </Button>
              );
            })}
          </div>
        </div>

        {/* Footer */}
        <div className="border-t p-4">
          <Button
            variant="ghost"
            className="w-full justify-start text-red-600 hover:text-red-700 hover:bg-red-50"
            onClick={handleLogout}
          >
            <LogOut className="h-5 w-5 mr-3" />
            Sign Out
          </Button>
        </div>
      </div>
    </>
  );
}

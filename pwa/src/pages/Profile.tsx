import { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState } from '@/store';
import { logoutUser } from '@/store/authSlice';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { User, Settings, Shield, LogOut, Edit, Camera } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import { ProfileEdit } from '@/components/profile/ProfileEdit';
import { PasswordChange } from '@/components/profile/PasswordChange';
import { KYCInformation } from '@/components/profile/KYCInformation';
import { DashboardPage } from '@/components/layout/DashboardPage';

export default function Profile() {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { user } = useSelector((state: RootState) => state.auth);
  const [activeTab, setActiveTab] = useState('overview');

  const handleLogout = () => {
    dispatch(logoutUser() as any);
    toast({
      title: "Logged out",
      description: "You have been successfully logged out.",
    });
  };

  const getInitials = (name: string) => {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  return (
    <DashboardPage 
      title="Profile" 
      subtitle="Manage your account and preferences"
    >
      <div className="max-w-4xl mx-auto space-y-6">
      {/* Profile Header */}
      <Card>
        <CardContent className="pt-6">
          <div className="flex items-center space-x-4">
            <div className="relative">
              <Avatar className="w-20 h-20">
                <AvatarImage src={(user as any)?.avatar} alt={user?.name} />
                <AvatarFallback className="text-lg font-medium">
                  {user?.name ? getInitials(user.name) : 'U'}
                </AvatarFallback>
              </Avatar>
              <Button
                size="icon"
                variant="outline"
                className="absolute -bottom-2 -right-2 w-8 h-8 rounded-full"
              >
                <Camera className="w-4 h-4" />
              </Button>
            </div>
            
            <div className="flex-1">
              <h2 className="text-xl font-semibold">{user?.name || 'User Name'}</h2>
              <p className="text-muted-foreground">{user?.email}</p>
              <div className="flex items-center gap-2 mt-2">
                <Badge variant={(user as any)?.email_verified_at ? 'default' : 'secondary'}>
                  {(user as any)?.email_verified_at ? 'Verified' : 'Unverified'}
                </Badge>
                {user?.membership && (
                  <Badge variant={user.membership.approval_status === 'approved' ? 'default' : 'secondary'}>
                    {user.membership.approval_status === 'approved' ? 'Approved Member' : 'Pending Approval'}
                  </Badge>
                )}
                {user?.membership?.id && (
                  <Badge variant="outline">#{user.membership.id}</Badge>
                )}
              </div>
            </div>

            <div className="text-right">
              <p className="text-sm text-muted-foreground">Member since</p>
              <p className="font-medium">
                {user?.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="edit">Edit Profile</TabsTrigger>
          <TabsTrigger value="security">Security</TabsTrigger>
          <TabsTrigger value="kyc">KYC Info</TabsTrigger>
        </TabsList>

        <TabsContent value="overview">
          <div className="grid gap-6 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <User className="w-5 h-5" />
                  Personal Information
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">Full Name</Label>
                    <p className="mt-1">{user?.name || 'Not provided'}</p>
                  </div>
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">Email</Label>
                    <p className="mt-1">{user?.email}</p>
                  </div>
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">Phone</Label>
                    <p className="mt-1">{(user?.profile as any)?.phone || 'Not provided'}</p>
                  </div>
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">National ID</Label>
                    <p className="mt-1">{(user?.profile as any)?.national_id || 'Not provided'}</p>
                  </div>
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">Occupation</Label>
                    <p className="mt-1">{(user?.profile as any)?.occupation || 'Not provided'}</p>
                  </div>
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">Monthly Income</Label>
                    <p className="mt-1">{(user?.profile as any)?.monthly_income ? `UGX ${Number((user.profile as any).monthly_income).toLocaleString()}` : 'Not provided'}</p>
                  </div>
                </div>
                
                {(user?.profile as any)?.address && (
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">Address</Label>
                    <p className="mt-1">{(user.profile as any).address}</p>
                  </div>
                )}

                {user?.membership && user.membership.approval_status === 'pending' && (
                  <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <h4 className="text-sm font-medium text-yellow-800">Membership Status</h4>
                    <p className="text-sm text-yellow-700 mt-1">
                      Your membership application is pending approval. You'll be notified once it's been reviewed.
                    </p>
                  </div>
                )}
                
                <Separator />
                
                <Button variant="outline" className="w-full" onClick={() => setActiveTab('edit')}>
                  <Edit className="w-4 h-4 mr-2" />
                  Edit Profile
                </Button>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Settings className="w-5 h-5" />
                  Account Settings
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-3">
                  <Button 
                    variant="outline" 
                    className="w-full justify-start" 
                    onClick={() => setActiveTab('security')}
                  >
                    <Shield className="w-4 h-4 mr-2" />
                    Change Password
                  </Button>
                  
                  <Button 
                    variant="outline" 
                    className="w-full justify-start"
                    onClick={() => setActiveTab('kyc')}
                  >
                    <User className="w-4 h-4 mr-2" />
                    KYC Information
                  </Button>
                </div>

                <Separator />

                <AlertDialog>
                  <AlertDialogTrigger asChild>
                    <Button variant="destructive" className="w-full">
                      <LogOut className="w-4 h-4 mr-2" />
                      Logout
                    </Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>Are you sure you want to logout?</AlertDialogTitle>
                      <AlertDialogDescription>
                        You will be redirected to the login page and will need to sign in again.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction onClick={handleLogout}>
                        Logout
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="edit">
          <ProfileEdit user={user} />
        </TabsContent>

        <TabsContent value="security">
          <PasswordChange />
        </TabsContent>

        <TabsContent value="kyc">
          <KYCInformation user={user} />
        </TabsContent>
      </Tabs>
      </div>
    </DashboardPage>
  );
}
import { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/hooks/use-toast';
import { User, Save } from 'lucide-react';
import { RootState } from '@/store';
import { updateProfile } from '@/store/authSlice';
import type { User as UserType } from '@/types/api';

interface ProfileEditProps {
  user: UserType | null;
}

export function ProfileEdit({ user }: ProfileEditProps) {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { loading } = useSelector((state: RootState) => state.auth);
  const [formData, setFormData] = useState({
    name: user?.name || '',
    phone: user?.phone || '',
    address: user?.address || '',
    occupation: user?.occupation || '',
    monthly_income: user?.monthly_income ? parseFloat(user.monthly_income) : 0,
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.name) {
      toast({
        title: "Error",
        description: "Name is required",
        variant: "destructive",
      });
      return;
    }

    try {
      const result = await dispatch(updateProfile(formData) as any);
      if (updateProfile.fulfilled.match(result)) {
        toast({
          title: "Success",
          description: "Profile updated successfully",
        });
      }
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to update profile",
        variant: "destructive",
      });
    }
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <User className="w-5 h-5" />
          Edit Profile
        </CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label htmlFor="name">Full Name *</Label>
              <Input
                id="name"
                type="text"
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                placeholder="Enter your full name"
                required
                disabled={loading}
              />
            </div>
            
            <div>
              <Label htmlFor="phone">Phone Number</Label>
              <Input
                id="phone"
                type="tel"
                value={formData.phone}
                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                placeholder="Enter your phone number"
                disabled={loading}
              />
            </div>
            
            <div className="md:col-span-2">
              <Label htmlFor="address">Address</Label>
              <Input
                id="address"
                type="text"
                value={formData.address}
                onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                placeholder="Enter your address"
                disabled={loading}
              />
            </div>
            
            <div>
              <Label htmlFor="occupation">Occupation</Label>
              <Input
                id="occupation"
                type="text"
                value={formData.occupation}
                onChange={(e) => setFormData({ ...formData, occupation: e.target.value })}
                placeholder="Enter your occupation"
                disabled={loading}
              />
            </div>

            <div>
              <Label htmlFor="monthly_income">Monthly Income (KES)</Label>
              <Input
                id="monthly_income"
                type="number"
                value={formData.monthly_income || ''}
                onChange={(e) => setFormData({ ...formData, monthly_income: parseFloat(e.target.value) || 0 })}
                placeholder="Enter your monthly income"
                disabled={loading}
              />
            </div>
          </div>

          <div className="flex gap-3 pt-4">
            <Button type="submit" disabled={loading} className="flex-1">
              <Save className="w-4 h-4 mr-2" />
              {loading ? 'Saving...' : 'Save Changes'}
            </Button>
            <Button 
              type="button" 
              variant="outline" 
              className="flex-1"
              disabled={loading}
              onClick={() => setFormData({
                name: user?.name || '',
                phone: user?.phone || '',
                address: user?.address || '',
                occupation: user?.occupation || '',
                monthly_income: user?.monthly_income ? parseFloat(user.monthly_income) : 0,
              })}
            >
              Reset
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
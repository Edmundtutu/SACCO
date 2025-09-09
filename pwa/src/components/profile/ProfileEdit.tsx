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
    phone: user?.profile?.phone || '',
    address: user?.profile?.address || '',
    occupation: user?.profile?.occupation || '',
    monthly_income: user?.profile?.monthly_income ? parseFloat(user.profile.monthly_income) : 0,
    next_of_kin_name: user?.profile?.next_of_kin_name || '',
    next_of_kin_relationship: user?.profile?.next_of_kin_relationship || '',
    next_of_kin_phone: user?.profile?.next_of_kin_phone || '',
    next_of_kin_address: user?.profile?.next_of_kin_address || '',
    emergency_contact_name: user?.profile?.emergency_contact_name || '',
    emergency_contact_phone: user?.profile?.emergency_contact_phone || '',
    employer_name: user?.profile?.employer_name || '',
    bank_name: user?.profile?.bank_name || '',
    bank_account_number: user?.profile?.bank_account_number || '',
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
        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <h3 className="text-lg font-medium mb-4">Personal Information</h3>
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
                <Label htmlFor="monthly_income">Monthly Income (UGX)</Label>
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
          </div>

          <div>
            <h3 className="text-lg font-medium mb-4">Next of Kin Information</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label htmlFor="next_of_kin_name">Next of Kin Name</Label>
                <Input
                  id="next_of_kin_name"
                  type="text"
                  value={formData.next_of_kin_name}
                  onChange={(e) => setFormData({ ...formData, next_of_kin_name: e.target.value })}
                  placeholder="Enter next of kin name"
                  disabled={loading}
                />
              </div>

              <div>
                <Label htmlFor="next_of_kin_relationship">Relationship</Label>
                <Input
                  id="next_of_kin_relationship"
                  type="text"
                  value={formData.next_of_kin_relationship}
                  onChange={(e) => setFormData({ ...formData, next_of_kin_relationship: e.target.value })}
                  placeholder="Enter relationship"
                  disabled={loading}
                />
              </div>

              <div>
                <Label htmlFor="next_of_kin_phone">Next of Kin Phone</Label>
                <Input
                  id="next_of_kin_phone"
                  type="tel"
                  value={formData.next_of_kin_phone}
                  onChange={(e) => setFormData({ ...formData, next_of_kin_phone: e.target.value })}
                  placeholder="Enter next of kin phone"
                  disabled={loading}
                />
              </div>

              <div>
                <Label htmlFor="next_of_kin_address">Next of Kin Address</Label>
                <Input
                  id="next_of_kin_address"
                  type="text"
                  value={formData.next_of_kin_address}
                  onChange={(e) => setFormData({ ...formData, next_of_kin_address: e.target.value })}
                  placeholder="Enter next of kin address"
                  disabled={loading}
                />
              </div>
            </div>
          </div>

          <div>
            <h3 className="text-lg font-medium mb-4">Additional Information</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label htmlFor="emergency_contact_name">Emergency Contact Name</Label>
                <Input
                  id="emergency_contact_name"
                  type="text"
                  value={formData.emergency_contact_name}
                  onChange={(e) => setFormData({ ...formData, emergency_contact_name: e.target.value })}
                  placeholder="Enter emergency contact name"
                  disabled={loading}
                />
              </div>

              <div>
                <Label htmlFor="emergency_contact_phone">Emergency Contact Phone</Label>
                <Input
                  id="emergency_contact_phone"
                  type="tel"
                  value={formData.emergency_contact_phone}
                  onChange={(e) => setFormData({ ...formData, emergency_contact_phone: e.target.value })}
                  placeholder="Enter emergency contact phone"
                  disabled={loading}
                />
              </div>

              <div>
                <Label htmlFor="employer_name">Employer Name</Label>
                <Input
                  id="employer_name"
                  type="text"
                  value={formData.employer_name}
                  onChange={(e) => setFormData({ ...formData, employer_name: e.target.value })}
                  placeholder="Enter employer name"
                  disabled={loading}
                />
              </div>

              <div>
                <Label htmlFor="bank_name">Bank Name</Label>
                <Input
                  id="bank_name"
                  type="text"
                  value={formData.bank_name}
                  onChange={(e) => setFormData({ ...formData, bank_name: e.target.value })}
                  placeholder="Enter bank name"
                  disabled={loading}
                />
              </div>

              <div className="md:col-span-2">
                <Label htmlFor="bank_account_number">Bank Account Number</Label>
                <Input
                  id="bank_account_number"
                  type="text"
                  value={formData.bank_account_number}
                  onChange={(e) => setFormData({ ...formData, bank_account_number: e.target.value })}
                  placeholder="Enter bank account number"
                  disabled={loading}
                />
              </div>
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
                phone: user?.profile?.phone || '',
                address: user?.profile?.address || '',
                occupation: user?.profile?.occupation || '',
                monthly_income: user?.profile?.monthly_income ? parseFloat(user.profile.monthly_income) : 0,
                next_of_kin_name: user?.profile?.next_of_kin_name || '',
                next_of_kin_relationship: user?.profile?.next_of_kin_relationship || '',
                next_of_kin_phone: user?.profile?.next_of_kin_phone || '',
                next_of_kin_address: user?.profile?.next_of_kin_address || '',
                emergency_contact_name: user?.profile?.emergency_contact_name || '',
                emergency_contact_phone: user?.profile?.emergency_contact_phone || '',
                employer_name: user?.profile?.employer_name || '',
                bank_name: user?.profile?.bank_name || '',
                bank_account_number: user?.profile?.bank_account_number || '',
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
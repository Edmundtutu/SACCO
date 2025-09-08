import { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link, useNavigate } from 'react-router-dom';
import { Eye, EyeOff, UserPlus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { RootState, AppDispatch } from '@/store';
import { registerUser, clearError } from '@/store/authSlice';
import { useToast } from '@/hooks/use-toast';

export default function Register() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    national_id: '',
    date_of_birth: '',
    gender: '',
    address: '',
    occupation: '',
    monthly_income: 0,
    next_of_kin_name: '',
    next_of_kin_relationship: '',
    next_of_kin_phone: '',
    next_of_kin_address: '',
    employer_name: '',
    employer_address: '',
    employer_phone: '',
  });
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  const dispatch = useDispatch<AppDispatch>();
  const navigate = useNavigate();
  const { toast } = useToast();
  const { loading, error } = useSelector((state: RootState) => state.auth);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    // Validation for all required fields
    const requiredFields = [
      'name', 'email', 'phone', 'password', 'password_confirmation',
      'national_id', 'date_of_birth', 'gender', 'address', 'occupation', 'monthly_income',
      'next_of_kin_name', 'next_of_kin_relationship', 'next_of_kin_phone', 'next_of_kin_address'
    ];

    const missingFields = requiredFields.filter(field => !formData[field as keyof typeof formData]);

    if (missingFields.length > 0) {
      toast({
        title: "Error",
        description: `Please fill in all required fields: ${missingFields.join(', ')}`,
        variant: "destructive",
      });
      return;
    }

    if (formData.password !== formData.password_confirmation) {
      toast({
        title: "Error",
        description: "Passwords do not match",
        variant: "destructive",
      });
      return;
    }

    if (formData.password.length < 8) {
      toast({
        title: "Error",
        description: "Password must be at least 8 characters long",
        variant: "destructive",
      });
      return;
    }

    // Validate date format
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(formData.date_of_birth)) {
      toast({
        title: "Error",
        description: "Date of birth must be in YYYY-MM-DD format",
        variant: "destructive",
      });
      return;
    }

    // Validate gender
    if (!['male', 'female', 'other'].includes(formData.gender.toLowerCase())) {
      toast({
        title: "Error",
        description: "Gender must be 'male', 'female', or 'other'",
        variant: "destructive",
      });
      return;
    }

    // Validate monthly income is a number
    if (isNaN(Number(formData.monthly_income)) || Number(formData.monthly_income) < 0) {
      toast({
        title: "Error",
        description: "Monthly income must be a positive number",
        variant: "destructive",
      });
      return;
    }

    try {
      // Convert monthly_income to number
      const monthlyIncome = Number(formData.monthly_income);

      const result = await dispatch(registerUser({
        ...formData,
        monthly_income: monthlyIncome,
      }));

      if (registerUser.fulfilled.match(result)) {
        toast({
          title: "Registration Successful!",
          description: "Your account is pending admin approval. You'll be notified once approved.",
        });
        navigate('/login');
      }
    } catch (error) {
      // Error is handled by the slice
    }
  };

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleErrorDismiss = () => {
    dispatch(clearError());
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/5 to-primary/10 px-4 py-8">
      <Card className="w-full max-w-2xl">
        <CardHeader className="text-center">
          <div className="mx-auto w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mb-4">
            <span className="text-primary-foreground font-heading font-bold text-2xl">S</span>
          </div>
          <CardTitle className="font-heading text-2xl">Join Our SACCO</CardTitle>
          <CardDescription>
            Create your account to start your financial journey
          </CardDescription>
        </CardHeader>

        <CardContent>
          {error && (
            <Alert variant="destructive" className="mb-4">
              <AlertDescription className="flex items-center justify-between">
                {error}
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={handleErrorDismiss}
                  className="h-auto p-0 ml-2"
                >
                  ✕
                </Button>
              </AlertDescription>
            </Alert>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <h3 className="text-lg font-medium">Personal Information</h3>
            <div>
              <Label htmlFor="name">Full Name *</Label>
              <Input
                id="name"
                type="text"
                placeholder="John Doe"
                value={formData.name}
                onChange={(e) => handleInputChange('name', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="email">Email Address *</Label>
              <Input
                id="email"
                type="email"
                placeholder="john@example.com"
                value={formData.email}
                onChange={(e) => handleInputChange('email', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="phone">Phone Number *</Label>
              <Input
                id="phone"
                type="tel"
                placeholder="+254 700 123 456"
                value={formData.phone}
                onChange={(e) => handleInputChange('phone', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="national_id">National ID *</Label>
              <Input
                id="national_id"
                type="text"
                placeholder="12345678"
                value={formData.national_id}
                onChange={(e) => handleInputChange('national_id', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="date_of_birth">Date of Birth *</Label>
              <Input
                id="date_of_birth"
                type="date"
                value={formData.date_of_birth}
                onChange={(e) => handleInputChange('date_of_birth', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="gender">Gender *</Label>
              <select
                id="gender"
                value={formData.gender}
                onChange={(e) => handleInputChange('gender', e.target.value)}
                disabled={loading}
                className="w-full mt-1 rounded-md border border-input bg-background px-3 py-2 text-sm"
              >
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div>
              <Label htmlFor="address">Address *</Label>
              <Input
                id="address"
                type="text"
                placeholder="123 Main St, City"
                value={formData.address}
                onChange={(e) => handleInputChange('address', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="occupation">Occupation *</Label>
              <Input
                id="occupation"
                type="text"
                placeholder="Software Developer"
                value={formData.occupation}
                onChange={(e) => handleInputChange('occupation', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="monthly_income">Monthly Income (KES) *</Label>
              <Input
                id="monthly_income"
                type="number"
                placeholder="50000"
                value={formData.monthly_income || ''}
                onChange={(e) => handleInputChange('monthly_income', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <h3 className="text-lg font-medium mt-6">Next of Kin Information</h3>
            <div>
              <Label htmlFor="next_of_kin_name">Next of Kin Name *</Label>
              <Input
                id="next_of_kin_name"
                type="text"
                placeholder="Jane Doe"
                value={formData.next_of_kin_name}
                onChange={(e) => handleInputChange('next_of_kin_name', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="next_of_kin_relationship">Relationship *</Label>
              <Input
                id="next_of_kin_relationship"
                type="text"
                placeholder="Spouse"
                value={formData.next_of_kin_relationship}
                onChange={(e) => handleInputChange('next_of_kin_relationship', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="next_of_kin_phone">Next of Kin Phone *</Label>
              <Input
                id="next_of_kin_phone"
                type="tel"
                placeholder="+254 700 123 456"
                value={formData.next_of_kin_phone}
                onChange={(e) => handleInputChange('next_of_kin_phone', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="next_of_kin_address">Next of Kin Address *</Label>
              <Input
                id="next_of_kin_address"
                type="text"
                placeholder="123 Main St, City"
                value={formData.next_of_kin_address}
                onChange={(e) => handleInputChange('next_of_kin_address', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <h3 className="text-lg font-medium mt-6">Employment Information (Optional)</h3>
            <div>
              <Label htmlFor="employer_name">Employer Name</Label>
              <Input
                id="employer_name"
                type="text"
                placeholder="ABC Company"
                value={formData.employer_name}
                onChange={(e) => handleInputChange('employer_name', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="employer_address">Employer Address</Label>
              <Input
                id="employer_address"
                type="text"
                placeholder="456 Business Ave, City"
                value={formData.employer_address}
                onChange={(e) => handleInputChange('employer_address', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="employer_phone">Employer Phone</Label>
              <Input
                id="employer_phone"
                type="tel"
                placeholder="+254 700 987 654"
                value={formData.employer_phone}
                onChange={(e) => handleInputChange('employer_phone', e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <h3 className="text-lg font-medium mt-6">Security</h3>
            <div>
              <Label htmlFor="password">Password *</Label>
              <div className="relative mt-1">
                <Input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  placeholder="Minimum 8 characters"
                  value={formData.password}
                  onChange={(e) => handleInputChange('password', e.target.value)}
                  disabled={loading}
                  className="pr-10"
                />
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  className="absolute right-0 top-0 h-full px-3 hover:bg-transparent"
                  onClick={() => setShowPassword(!showPassword)}
                  disabled={loading}
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </Button>
              </div>
            </div>

            <div>
              <Label htmlFor="password_confirmation">Confirm Password *</Label>
              <div className="relative mt-1">
                <Input
                  id="password_confirmation"
                  type={showConfirmPassword ? 'text' : 'password'}
                  placeholder="Re-enter your password"
                  value={formData.password_confirmation}
                  onChange={(e) => handleInputChange('password_confirmation', e.target.value)}
                  disabled={loading}
                  className="pr-10"
                />
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  className="absolute right-0 top-0 h-full px-3 hover:bg-transparent"
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  disabled={loading}
                >
                  {showConfirmPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </Button>
              </div>
            </div>

            <Button type="submit" className="w-full mt-6" disabled={loading}>
              {loading ? (
                <>
                  <div className="w-4 h-4 border-2 border-primary-foreground border-t-transparent rounded-full animate-spin mr-2" />
                  Creating Account...
                </>
              ) : (
                <>
                  <UserPlus className="w-4 h-4 mr-2" />
                  Create Account
                </>
              )}
            </Button>
          </form>
        </CardContent>

        <CardFooter className="flex flex-col space-y-4">
          <div className="text-center text-sm text-muted-foreground">
            Already have an account?{' '}
            <Link to="/login" className="text-primary hover:underline font-medium">
              Sign in here
            </Link>
          </div>

          <div className="text-xs text-muted-foreground text-center">
            By creating an account, you agree to our Terms of Service and Privacy Policy.
            Your account will be reviewed by our admin team.
          </div>
        </CardFooter>
      </Card>
    </div>
  );
}

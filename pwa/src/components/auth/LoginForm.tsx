import { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link, useNavigate } from 'react-router-dom';
import { Eye, EyeOff, LogIn, Building2, ArrowLeft } from 'lucide-react';
import logo from '/public/logo.png';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { RootState, AppDispatch } from '@/store';
import { loginUser, loginWithTenant, resetSaccoSelection, clearError } from '@/store/authSlice';
import { useToast } from '@/hooks/use-toast';

export function LoginForm() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);

  const dispatch = useDispatch<AppDispatch>();
  const navigate = useNavigate();
  const { toast } = useToast();
  const { loading, error, pendingSaccoSelection, availableTenants } = useSelector(
    (state: RootState) => state.auth,
  );

  // ── Step 1: credentials ────────────────────────────────────────────────────
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!email || !password) {
      toast({ title: 'Error', description: 'Please fill in all fields', variant: 'destructive' });
      return;
    }

    try {
      const result = await dispatch(loginUser({ email, password }));
      if (loginUser.fulfilled.match(result)) {
        if (!result.payload.requiresSelection) {
          toast({ title: 'Welcome back!', description: 'Login successful' });
          navigate('/dashboard');
        }
        // If requiresSelection === true the slice already set pendingSaccoSelection → UI switches to step 2
      }
    } catch {
      // errors handled by slice
    }
  };

  // ── Step 2: SACCO pick ─────────────────────────────────────────────────────
  const handleSelectSacco = async (tenantId: number) => {
    try {
      const result = await dispatch(loginWithTenant({ tenantId }));
      if (loginWithTenant.fulfilled.match(result)) {
        toast({ title: 'Welcome back!', description: 'Login successful' });
        navigate('/dashboard');
      }
    } catch {
      // errors handled by slice
    }
  };

  const handleBack = () => {
    dispatch(resetSaccoSelection());
  };

  const handleErrorDismiss = () => {
    dispatch(clearError());
  };

  // ── SACCO selection step ──────────────────────────────────────────────────
  if (pendingSaccoSelection) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/5 to-primary/10 px-4">
        <Card className="w-full max-w-md">
          <CardHeader className="text-center">
            <div className="mx-auto w-20 h-20 flex items-center justify-center mb-4">
              <img src={logo} alt="logo" />
            </div>
            <CardTitle>Select Your SACCO</CardTitle>
            <CardDescription>
              Your account is linked to multiple SACCOs. Choose which one to access.
            </CardDescription>
          </CardHeader>

          <CardContent>
            {error && (
              <Alert variant="destructive" className="mb-4">
                <AlertDescription className="flex items-center justify-between">
                  {error}
                  <Button variant="ghost" size="sm" onClick={handleErrorDismiss} className="h-auto p-0 ml-2">
                    ✕
                  </Button>
                </AlertDescription>
              </Alert>
            )}

            <div className="flex flex-col gap-3">
              {availableTenants.map((t) => (
                <button
                  key={t.id}
                  type="button"
                  disabled={loading}
                  onClick={() => handleSelectSacco(t.id)}
                  className="flex items-center gap-4 p-4 rounded-xl border-2 border-border hover:border-primary hover:shadow-md transition-all text-left disabled:opacity-60"
                >
                  {/* Logo or placeholder */}
                  <div className="shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-primary/10 flex items-center justify-center">
                    {t.logo_url ? (
                      <img src={t.logo_url} alt={t.name} className="w-full h-full object-contain" />
                    ) : (
                      <Building2 className="w-6 h-6 text-primary" />
                    )}
                  </div>

                  {/* Info */}
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold truncate">{t.name}</p>
                    <p className="text-xs text-muted-foreground">{t.code}</p>
                  </div>

                  {/* Status pill */}
                  <span className={[
                    'text-xs px-2 py-0.5 rounded-full shrink-0',
                    t.status === 'active'
                      ? 'bg-green-100 text-green-700'
                      : 'bg-yellow-100 text-yellow-700',
                  ].join(' ')}>
                    {t.status}
                  </span>
                </button>
              ))}
            </div>
          </CardContent>

          <CardFooter>
            <Button variant="ghost" size="sm" onClick={handleBack} className="w-full text-muted-foreground">
              <ArrowLeft className="w-4 h-4 mr-2" />
              Back — sign in as a different user
            </Button>
          </CardFooter>
        </Card>
      </div>
    );
  }

  // ── Credentials step (default) ────────────────────────────────────────────
  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/5 to-primary/10 px-4">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center">
          <div className='mx-auto w-20 h-20 flex items-center justify-center mb-4'>
            <img  src={logo} alt='logo'/>
          </div>
          <CardDescription>
            Sign in to your SACCO account to continue
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
            <div>
              <Label htmlFor="email">Email Address</Label>
              <Input
                id="email"
                type="email"
                placeholder="your@email.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                disabled={loading}
                className="mt-1"
              />
            </div>

            <div>
              <Label htmlFor="password">Password</Label>
              <div className="relative mt-1">
                <Input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  placeholder="Enter your password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
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

            <Button type="submit" className="w-full" disabled={loading}>
              {loading ? (
                <>
                  <div className="w-4 h-4 border-2 border-primary-foreground border-t-transparent rounded-full animate-spin mr-2" />
                  Signing In...
                </>
              ) : (
                <>
                  <LogIn className="w-4 h-4 mr-2" />
                  Sign In
                </>
              )}
            </Button>
          </form>
        </CardContent>

        <CardFooter className="flex flex-col space-y-4">
          <div className="text-center text-sm text-muted-foreground">
            Don't have an account?{' '}
            <Link to="/register" className="text-primary hover:underline font-medium">
              Sign up here
            </Link>
          </div>
          
          <div className="text-center">
            <Link 
              to="/forgot-password" 
              className="text-sm text-primary hover:underline"
            >
              Forgot your password?
            </Link>
          </div>
        </CardFooter>
      </Card>
    </div>
  );
}

import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
  Wallet,
  TrendingUp,
  Users,
  Shield,
  Smartphone,
  ArrowRight,
  CheckCircle2,
  Download
} from 'lucide-react';
import { usePWAInstall } from '@/hooks/usePWAInstall';

const LANDING_SEEN_KEY = 'sacco_landing_seen';

export default function Landing() {
  const navigate = useNavigate();
  const [isMobile, setIsMobile] = useState(false);
  const { isInstallable, isInstalled, promptInstall } = usePWAInstall();

  useEffect(() => {
    const checkMobile = () => {
      setIsMobile(window.innerWidth < 768);
    };
    checkMobile();
    window.addEventListener('resize', checkMobile);
    return () => window.removeEventListener('resize', checkMobile);
  }, []);

  const handleGetStarted = () => {
    localStorage.setItem(LANDING_SEEN_KEY, 'true');
    navigate('/login');
  };

  const handleStaffPortal = () => {
    localStorage.setItem(LANDING_SEEN_KEY, 'true');
    // Redirect to Laravel admin panel (staff uses same portal)
    window.location.href = `${import.meta.env.VITE_ADMIN_PANEL_BASE_URL}/admin/login`;
  };

  const handleInstall = async () => {
    if (promptInstall) {
      await promptInstall();
    }
  };

  // Desktop Design
  if (!isMobile) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
        <div className="container mx-auto px-6 py-12">
          {/* Header */}
          <div className="text-center mb-16 animate-fade-in">
            <div className="inline-flex items-center justify-center w-20 h-20 bg-primary/10 rounded-2xl mb-6">
              <img
                src="/logo.png"
                alt="AVS Logo"
                className="w-12 h-12 object-contain"
                draggable={false}
              />
            </div>
            <h1 className="text-5xl md:text-6xl font-heading font-bold text-foreground mb-4">
              Accountable Value Suite
            </h1>
            <p className="text-xl md:text-2xl text-muted-foreground max-w-2xl mx-auto mb-2">
              Complete SACCO Management System
            </p>
            <p className="text-lg text-muted-foreground max-w-3xl mx-auto">
              Manage savings, loans, and shares with confidence. Built for members, powered by transparency.
            </p>
          </div>

          {/* Features Grid */}
          <div className="grid md:grid-cols-3 gap-6 mb-12">
            <Card className="border-2 hover:border-primary/50 transition-all duration-300 hover:shadow-lg">
              <CardContent className="p-6">
                <div className="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center mb-4 mx-auto">
                  <TrendingUp className="w-6 h-6 text-green-600" />
                </div>
                <h3 className="text-xl font-heading font-semibold mb-2">Smart Savings</h3>
                <p className="text-muted-foreground">
                  Track multiple savings accounts, set goals, and watch your wealth grow with real-time insights.
                </p>
              </CardContent>
            </Card>

            <Card className="border-2 hover:border-primary/50 transition-all duration-300 hover:shadow-lg">
              <CardContent className="p-6">
                <div className="w-12 h-12 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center mb-4 mx-auto">
                  <Wallet className="w-6 h-6 text-orange-600" />
                </div>
                <h3 className="text-xl font-heading font-semibold mb-2">Flexible Loans</h3>
                <p className="text-muted-foreground">
                  Apply for loans, track repayments, and manage your credit with transparent terms and schedules.
                </p>
              </CardContent>
            </Card>

            <Card className="border-2 hover:border-primary/50 transition-all duration-300 hover:shadow-lg">
              <CardContent className="p-6">
                <div className="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center mb-4 mx-auto">
                  <Users className="w-6 h-6 text-blue-600" />
                </div>
                <h3 className="text-xl font-heading font-semibold mb-2">Share Ownership</h3>
                <p className="text-muted-foreground">
                  Build equity through shares, receive dividends, and participate in cooperative governance.
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Value Propositions */}
          <div className="bg-card rounded-2xl border p-8 mb-12 shadow-sm">
            <h2 className="text-2xl font-heading font-bold mb-6 text-center">Choose AVS For :</h2>
            <div className="grid md:grid-cols-2 gap-6">
              <div className="flex items-start gap-4">
                <CheckCircle2 className="w-6 h-6 text-primary flex-shrink-0 mt-1" />
                <div>
                  <h3 className="font-semibold mb-1">Double-Entry Bookkeeping</h3>
                  <p className="text-sm text-muted-foreground">
                    Every transaction is recorded with proper accounting principles for complete transparency.
                  </p>
                </div>
              </div>
              <div className="flex items-start gap-4">
                <CheckCircle2 className="w-6 h-6 text-primary flex-shrink-0 mt-1" />
                <div>
                  <h3 className="font-semibold mb-1">Real-Time Updates</h3>
                  <p className="text-sm text-muted-foreground">
                    See your balances, transactions, and account status update instantly across all devices.
                  </p>
                </div>
              </div>
              <div className="flex items-start gap-4">
                <CheckCircle2 className="w-6 h-6 text-primary flex-shrink-0 mt-1" />
                <div>
                  <h3 className="font-semibold mb-1">Secure & Private</h3>
                  <p className="text-sm text-muted-foreground">
                    Your financial data is protected with industry-standard encryption and access controls.
                  </p>
                </div>
              </div>
              <div className="flex items-start gap-4">
                <CheckCircle2 className="w-6 h-6 text-primary flex-shrink-0 mt-1" />
                <div>
                  <h3 className="font-semibold mb-1">Works Offline</h3>
                  <p className="text-sm text-muted-foreground">
                    Access your account even without internet. Changes sync automatically when you're back online.
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Portal Selection */}
          <div className="max-w-4xl mx-auto">
            <h2 className="text-3xl font-heading font-bold text-center mb-8">Access Your Portal</h2>
            <div className="grid md:grid-cols-2 gap-6">
              <Card className="border-2 hover:border-primary transition-all duration-300 cursor-pointer group">
                <CardContent className="p-8 text-center">
                  <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/20 transition-colors">
                    <Users className="w-8 h-8 text-primary" />
                  </div>
                  <h3 className="text-xl font-heading font-semibold mb-2">Member Portal</h3>
                  <p className="text-sm text-muted-foreground mb-6">
                    Access your savings, loans, shares, and transaction history
                  </p>
                  <Button
                    onClick={handleGetStarted}
                    className="w-full group-hover:scale-105 transition-transform"
                    size="lg"
                  >
                    Enter Portal
                    <ArrowRight className="ml-2 w-4 h-4" />
                  </Button>
                </CardContent>
              </Card>

              <Card className="border-2 hover:border-primary transition-all duration-300 cursor-pointer group">
                <CardContent className="p-8 text-center">
                  <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/20 transition-colors">
                    <Users className="w-8 h-8 text-primary" />
                  </div>
                  <h3 className="text-xl font-heading font-semibold mb-2">Staff Portal</h3>
                  <p className="text-sm text-muted-foreground mb-6">
                    Process transactions, approve requests, and manage operations
                  </p>
                  <Button
                    onClick={handleStaffPortal}
                    variant="outline"
                    className="w-full group-hover:scale-105 transition-transform"
                    size="lg"
                  >
                    Staff Login
                    <ArrowRight className="ml-2 w-4 h-4" />
                  </Button>
                </CardContent>
              </Card>
            </div>
          </div>

          {/* PWA Install Prompt */}
          {isInstallable && !isInstalled && (
            <div className="mt-12 max-w-2xl mx-auto">
              <Card className="border-primary/20 bg-primary/5">
                <CardContent className="p-6">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                      <Smartphone className="w-6 h-6 text-primary" />
                    </div>
                    <div className="flex-1">
                      <h3 className="font-semibold mb-1">Install AVS App</h3>
                      <p className="text-sm text-muted-foreground">
                        Add to your home screen for quick access and offline functionality
                      </p>
                    </div>
                    <Button onClick={handleInstall} size="sm">
                      <Download className="w-4 h-4 mr-2" />
                      Install
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>
          )}
        </div>
      </div>
    );
  }

  // Mobile Design - Native App-like
  return (
    <div className="min-h-screen bg-gradient-to-b from-primary/10 via-background to-background">
      <div className="container mx-auto px-4 py-8">
        {/* Mobile Header */}
        <div className="text-center mb-12 animate-fade-in">
          <div className="inline-flex items-center justify-center w-20 h-20 bg-primary/10 rounded-2xl mb-6">
            <img
              src="/logo.png"
              alt="AVS Logo"
              className="w-12 h-12 object-contain"
              draggable={false}
            />
          </div>
          <h1 className="text-3xl font-heading font-bold text-foreground mb-3">
            Accountable Value Suite
          </h1>
          <p className="text-base text-muted-foreground px-4">
            Complete SACCO management system
          </p>
        </div>

        {/* Mobile Features - Compact Cards */}
        <div className="space-y-4 mb-8">
          <Card className="border-0 shadow-md bg-gradient-to-r from-green-50 to-green-100/50 dark:from-green-950/20 dark:to-green-900/10">
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center">
                  <TrendingUp className="w-5 h-5 text-white" />
                </div>
                <div className="flex-1">
                  <h3 className="font-semibold text-sm mb-0.5">Smart Savings</h3>
                  <p className="text-xs text-muted-foreground">Track and grow your wealth</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="border-0 shadow-md bg-gradient-to-r from-orange-50 to-orange-100/50 dark:from-orange-950/20 dark:to-orange-900/10">
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center">
                  <Wallet className="w-5 h-5 text-white" />
                </div>
                <div className="flex-1">
                  <h3 className="font-semibold text-sm mb-0.5">Flexible Loans</h3>
                  <p className="text-xs text-muted-foreground">Transparent credit management</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="border-0 shadow-md bg-gradient-to-r from-blue-50 to-blue-100/50 dark:from-blue-950/20 dark:to-blue-900/10">
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
                  <Users className="w-5 h-5 text-white" />
                </div>
                <div className="flex-1">
                  <h3 className="font-semibold text-sm mb-0.5">Share Ownership</h3>
                  <p className="text-xs text-muted-foreground">Build equity and dividends</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Mobile Value Props - Compact */}
        <div className="bg-card rounded-2xl border p-5 mb-8 shadow-sm">
          <h2 className="text-lg font-heading font-bold mb-4">Key Benefits</h2>
          <div className="space-y-3">
            <div className="flex items-start gap-3">
              <CheckCircle2 className="w-5 h-5 text-primary flex-shrink-0 mt-0.5" />
              <div>
                <p className="text-sm font-medium">Real-time updates</p>
                <p className="text-xs text-muted-foreground">Instant balance and transaction sync</p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <CheckCircle2 className="w-5 h-5 text-primary flex-shrink-0 mt-0.5" />
              <div>
                <p className="text-sm font-medium">Secure & private</p>
                <p className="text-xs text-muted-foreground">Bank-level security</p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <CheckCircle2 className="w-5 h-5 text-primary flex-shrink-0 mt-0.5" />
              <div>
                <p className="text-sm font-medium">Works offline</p>
                <p className="text-xs text-muted-foreground">Access anywhere, anytime</p>
              </div>
            </div>
          </div>
        </div>

        {/* Mobile Portal Buttons - Full Width */}
        <div className="space-y-4 mb-6">
          <Button
            onClick={handleGetStarted}
            className="w-full h-14 text-base font-semibold shadow-lg"
            size="lg"
          >
            <Users className="w-5 h-5 mr-2" />
            Member Portal
            <ArrowRight className="ml-auto w-5 h-5" />
          </Button>

          <Button
            onClick={handleStaffPortal}
            variant="outline"
            className="w-full h-14 text-base font-semibold border-2"
            size="lg"
          >
            <Users className="w-5 h-5 mr-2" />
            Staff Portal
            <ArrowRight className="ml-auto w-5 h-5" />
          </Button>
        </div>

        {/* Mobile PWA Install Prompt */}
        {isInstallable && !isInstalled && (
          <Card className="border-primary/30 bg-primary/5">
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-primary/20 rounded-xl flex items-center justify-center flex-shrink-0">
                  <Smartphone className="w-5 h-5 text-primary" />
                </div>
                <div className="flex-1">
                  <h3 className="font-semibold text-sm mb-0.5">Install App</h3>
                  <p className="text-xs text-muted-foreground">Add to home screen</p>
                </div>
                <Button onClick={handleInstall} size="sm" variant="outline">
                  <Download className="w-4 h-4" />
                </Button>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  );
}

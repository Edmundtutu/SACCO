import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useSelector } from 'react-redux';
import { RootState } from '@/store';

const LANDING_SEEN_KEY = 'sacco_landing_seen';

/**
 * LandingGuard component checks if user has seen the landing page.
 * If not, redirects to /welcome. Otherwise, redirects based on auth state.
 */
export function LandingGuard() {
  const navigate = useNavigate();
  const { isAuthenticated, token } = useSelector((state: RootState) => state.auth);

  useEffect(() => {
    const hasSeenLanding = localStorage.getItem(LANDING_SEEN_KEY) === 'true';

    if (!hasSeenLanding) {
      // User hasn't seen landing page - show it
      navigate('/welcome', { replace: true });
    } else if (isAuthenticated && token) {
      // User is authenticated - go to dashboard
      navigate('/dashboard', { replace: true });
    } else {
      // User has seen landing but not authenticated - go to login
      navigate('/login', { replace: true });
    }
  }, [navigate, isAuthenticated, token]);

  // Show loading state while redirecting
  return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="text-center">
        <div className="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-4" />
        <p className="text-muted-foreground">Loading...</p>
      </div>
    </div>
  );
}

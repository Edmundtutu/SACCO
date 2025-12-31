import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Navigate, useLocation } from 'react-router-dom';
import { RootState, AppDispatch } from '@/store';
import { fetchProfile } from '@/store/authSlice';

interface ProtectedRouteProps {
  children: React.ReactNode;
}

export function ProtectedRoute({ children }: ProtectedRouteProps) {
  const dispatch = useDispatch<AppDispatch>();
  const location = useLocation();
  const { isAuthenticated, user, token, loading } = useSelector((state: RootState) => state.auth);

  useEffect(() => {
    // If we have a token but no user, fetch the profile once
    if (token && !user && !loading) {
      dispatch(fetchProfile());
    }
  }, [token, user, loading, dispatch]);

  // No token = not logged in
  if (!token) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  // Has token but still loading user data
  if (!user || loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-4" />
          <p className="text-muted-foreground">Loading your profile...</p>
        </div>
      </div>
    );
  }

  return <>{children}</>;
}
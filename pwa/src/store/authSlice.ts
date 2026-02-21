import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { authAPI } from '../api/auth';
import type { User, TenantInfo, ProfileUpdateData, RegisterData } from '@/types/api';

interface AuthState {
  user: User | null;
  token: string | null;
  tenant: TenantInfo | null;
  isAuthenticated: boolean;
  loading: boolean;
  error: string | null;
  /** True when the API returned requires_sacco_selection */
  pendingSaccoSelection: boolean;
  availableTenants: TenantInfo[];
  /** Credentials held in memory while user picks a SACCO */
  pendingCredentials: { email: string; password: string } | null;
}

const initialState: AuthState = {
  user: null,
  token: null,
  tenant: null,
  isAuthenticated: false,
  loading: false,
  error: null,
  pendingSaccoSelection: false,
  availableTenants: [],
  pendingCredentials: null,
};

export const loginUser = createAsyncThunk(
  'auth/login',
  async ({ email, password }: { email: string; password: string }) => {
    const response = await authAPI.login(email, password);
    if (!response.success) throw new Error(response.message || 'Login failed');
    if (!response.data) throw new Error('Login failed');

    // Multi-SACCO: user belongs to more than one SACCO
    if ('requires_sacco_selection' in response.data && response.data.requires_sacco_selection) {
      return { requiresSelection: true as const, tenants: response.data.tenants, email, password };
    }

    return { requiresSelection: false as const, ...response.data };
  }
);

export const loginWithTenant = createAsyncThunk(
  'auth/loginWithTenant',
  async ({ tenantId }: { tenantId: number }, { getState }: any) => {
    const { pendingCredentials } = (getState() as any).auth as {
      pendingCredentials: { email: string; password: string } | null;
    };
    if (!pendingCredentials) throw new Error('No pending credentials');

    const response = await authAPI.login(pendingCredentials.email, pendingCredentials.password, tenantId);
    if (!response.success || !response.data) throw new Error(response.message || 'Login failed');
    if ('requires_sacco_selection' in response.data) throw new Error('Unexpected selection response');

    return response.data;
  }
);

export const registerUser = createAsyncThunk(
  'auth/register',
  async (userData: RegisterData) => {
    const response = await authAPI.register(userData);
    if (response.success) {
      return response;
    }
    throw new Error(response.message || 'Registration failed');
  }
);

export const fetchProfile = createAsyncThunk('auth/profile', async () => {
  const response = await authAPI.getProfile();
  if (response.success && response.data) {
    return response.data.user;
  }
  throw new Error(response.message || 'Failed to fetch profile');
});

export const updateProfile = createAsyncThunk(
  'auth/updateProfile',
  async (profileData: ProfileUpdateData) => {
    const response = await authAPI.updateProfile(profileData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to update profile');
  }
);

export const changePassword = createAsyncThunk(
  'auth/changePassword',
  async ({ currentPassword, newPassword }: { currentPassword: string; newPassword: string }) => {
    const response = await authAPI.changePassword(currentPassword, newPassword);
    if (response.success) {
      return response;
    }
    throw new Error(response.message || 'Failed to change password');
  }
);

export const logoutUser = createAsyncThunk('auth/logout', async () => {
  try {
    await authAPI.logout();
  } catch (error) {
    console.warn('Logout API call failed, but continuing with local cleanup');
  }
});

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    setAuthenticated: (state, action: PayloadAction<boolean>) => {
      state.isAuthenticated = action.payload;
    },
    resetSaccoSelection: (state) => {
      state.pendingSaccoSelection = false;
      state.availableTenants = [];
      state.pendingCredentials = null;
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      // Login
      .addCase(loginUser.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(loginUser.fulfilled, (state, action) => {
        state.loading = false;
        if (action.payload.requiresSelection) {
          state.pendingSaccoSelection = true;
          state.availableTenants = action.payload.tenants;
          state.pendingCredentials = { email: action.payload.email, password: action.payload.password };
        } else {
          state.user = action.payload.user;
          state.token = action.payload.token;
          state.tenant = action.payload.tenant ?? null;
          state.isAuthenticated = true;
          state.pendingSaccoSelection = false;
          state.availableTenants = [];
          state.pendingCredentials = null;
        }
      })
      .addCase(loginUser.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Login failed';
      })
      // Login with known tenant
      .addCase(loginWithTenant.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(loginWithTenant.fulfilled, (state, action) => {
        state.loading = false;
        state.user = action.payload.user;
        state.token = action.payload.token;
        state.tenant = (action.payload as any).tenant ?? null;
        state.isAuthenticated = true;
        state.pendingSaccoSelection = false;
        state.availableTenants = [];
        state.pendingCredentials = null;
      })
      .addCase(loginWithTenant.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Login failed';
      })
      // Register
      .addCase(registerUser.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(registerUser.fulfilled, (state) => {
        state.loading = false;
      })
      .addCase(registerUser.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Registration failed';
      })
      // Profile
      .addCase(fetchProfile.fulfilled, (state, action) => {
        state.user = action.payload;
        state.isAuthenticated = true;
      })
      .addCase(updateProfile.fulfilled, (state, action) => {
        state.user = action.payload;
      })
      // Logout
      .addCase(logoutUser.fulfilled, (state) => {
        state.user = null;
        state.token = null;
        state.isAuthenticated = false;
      });
  },
});

export const { clearError, setAuthenticated, resetSaccoSelection } = authSlice.actions;
export default authSlice.reducer;

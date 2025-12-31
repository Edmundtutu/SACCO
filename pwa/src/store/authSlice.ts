import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { authAPI } from '../api/auth';
import type { User, ProfileUpdateData, RegisterData } from '@/types/api';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  loading: boolean;
  error: string | null;
}

const initialState: AuthState = {
  user: null,
  token: null,
  isAuthenticated: false,
  loading: false,
  error: null,
};

export const loginUser = createAsyncThunk(
  'auth/login',
  async ({ email, password }: { email: string; password: string }) => {
    const response = await authAPI.login(email, password);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Login failed');
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
        state.user = action.payload.user;
        state.token = action.payload.token;
        state.isAuthenticated = true;
      })
      .addCase(loginUser.rejected, (state, action) => {
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

export const { clearError, setAuthenticated } = authSlice.actions;
export default authSlice.reducer;

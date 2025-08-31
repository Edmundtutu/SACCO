import apiClient from './client';
import type { 
  LoginResponse, 
  RegisterData, 
  User, 
  ProfileUpdateData, 
  ApiResponse 
} from '@/types/api';

export const authAPI = {
  async login(email: string, password: string): Promise<ApiResponse<LoginResponse>> {
    const response = await apiClient.post('/auth/login', { email, password });
    return response.data;
  },

  async register(userData: RegisterData): Promise<ApiResponse<{ message: string }>> {
    const response = await apiClient.post('/auth/register', userData);
    return response.data;
  },

  async getProfile(): Promise<ApiResponse<{ user: User; summary?: any }>> {
    const response = await apiClient.get('/auth/profile');
    return response.data;
  },

  async updateProfile(profileData: ProfileUpdateData): Promise<ApiResponse<User>> {
    const response = await apiClient.put('/auth/profile', profileData);
    return response.data;
  },

  async changePassword(currentPassword: string, newPassword: string): Promise<ApiResponse<{ message: string }>> {
    const response = await apiClient.post('/auth/change-password', {
      current_password: currentPassword,
      new_password: newPassword,
      new_password_confirmation: newPassword,
    });
    return response.data;
  },

  async logout(): Promise<ApiResponse<{ message: string }>> {
    const response = await apiClient.post('/auth/logout');
    return response.data;
  },

  async refreshToken(): Promise<ApiResponse<{ token: string; token_type: string; expires_in: number }>> {
    const response = await apiClient.post('/auth/refresh');
    return response.data;
  },
};
import apiClient from './client';

interface LoginResponse {
  token: string;
  user: {
    id: number;
    name: string;
    email: string;
    phone?: string;
    member_number?: string;
    status: 'active' | 'pending' | 'suspended';
    created_at: string;
  };
}

interface RegisterData {
  name: string;
  email: string;
  password: string;
  phone: string;
}

export const authAPI = {
  async login(email: string, password: string): Promise<LoginResponse> {
    const response = await apiClient.post('/auth/login', { email, password });
    return response.data;
  },

  async register(userData: RegisterData) {
    const response = await apiClient.post('/auth/register', userData);
    return response.data;
  },

  async getProfile() {
    const response = await apiClient.get('/auth/profile');
    return response.data;
  },

  async updateProfile(profileData: any) {
    const response = await apiClient.put('/auth/profile', profileData);
    return response.data;
  },

  async changePassword(currentPassword: string, newPassword: string) {
    const response = await apiClient.post('/auth/change-password', {
      current_password: currentPassword,
      new_password: newPassword,
      new_password_confirmation: newPassword,
    });
    return response.data;
  },

  async logout() {
    const response = await apiClient.post('/auth/logout');
    return response.data;
  },

  async refreshToken() {
    const response = await apiClient.post('/auth/refresh');
    return response.data;
  },
};
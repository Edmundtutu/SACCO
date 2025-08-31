import axios from 'axios';
import { store } from '../store';
import { logoutUser } from '../store/authSlice';

// Base API URL - Update this to match your Laravel backend
const BASE_URL = process.env.NODE_ENV === 'production' 
  ? 'https://your-api-domain.com/api'  // Update this with your production API URL
  : 'http://localhost:8000/api';       // Development API URL

const apiClient = axios.create({
  baseURL: BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor to add auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor to handle errors and token refresh
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        // Try to refresh token
        const refreshResponse = await apiClient.post('/auth/refresh');
        const newToken = refreshResponse.data.token;
        
        localStorage.setItem('token', newToken);
        apiClient.defaults.headers.Authorization = `Bearer ${newToken}`;
        originalRequest.headers.Authorization = `Bearer ${newToken}`;

        return apiClient(originalRequest);
      } catch (refreshError) {
        // Refresh failed, logout user
        store.dispatch(logoutUser());
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    // Handle other errors
    if (error.response?.data?.message) {
      return Promise.reject(new Error(error.response.data.message));
    }
    
    return Promise.reject(error);
  }
);

export default apiClient;
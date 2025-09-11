import apiClient from './client';
import type { Membership, User } from '@/types/api';

export interface PaginatedResponse<T> {
  current_page: number;
  data: T[];
  last_page: number;
  per_page: number;
  total: number;
}

export const membershipsAPI = {
  async list(params?: { approval_status?: string; profile_type?: string; search?: string; page?: number }) {
    const response = await apiClient.get('/memberships', { params });
    return response.data as { success: boolean; data: PaginatedResponse<Membership & { user: User }> };
  },

  async show(membershipId: number) {
    const response = await apiClient.get(`/memberships/${membershipId}`);
    return response.data as { success: boolean; data: Membership & { user: User } };
  },

  async approveLevel(membershipId: number, level: 1 | 2 | 3) {
    const response = await apiClient.post(`/memberships/${membershipId}/approve-level-${level}`);
    return response.data as { success: boolean; message: string };
  },
};


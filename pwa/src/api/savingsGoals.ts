import apiClient from './client';
import type {
  CreateSavingsGoalPayload,
  PaginatedResponse,
  SavingsGoal,
  UpdateSavingsGoalPayload,
} from '@/types/api';

type ListParams = {
  page?: number;
  perPage?: number;
};

type ApiResponse<T> = {
  success?: boolean;
  message?: string;
  data: T;
};

export const savingsGoalsAPI = {
  async list(params?: ListParams): Promise<PaginatedResponse<SavingsGoal>> {
    const response = await apiClient.get('/savings/goals', {
      params: {
        page: params?.page,
        per_page: params?.perPage,
      },
    });

    return response.data as PaginatedResponse<SavingsGoal>;
  },

  async create(payload: CreateSavingsGoalPayload): Promise<ApiResponse<SavingsGoal>> {
    const response = await apiClient.post('/savings/goals', payload);
    return response.data as ApiResponse<SavingsGoal>;
  },

  async update(goalId: number, payload: UpdateSavingsGoalPayload): Promise<ApiResponse<SavingsGoal>> {
    const response = await apiClient.put(`/savings/goals/${goalId}`, payload);
    return response.data as ApiResponse<SavingsGoal>;
  },

  async remove(goalId: number): Promise<ApiResponse<null>> {
    const response = await apiClient.delete(`/savings/goals/${goalId}`);
    return response.data as ApiResponse<null>;
  },
};

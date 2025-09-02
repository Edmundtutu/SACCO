import apiClient from './client';
import type { 
  MemberStatement, 
  SavingsSummary, 
  LoansSummary,
  ApiResponse 
} from '@/types/api';

export interface StatementParams {
  from_date?: string;
  to_date?: string;
  account_id?: number;
}

export const reportsAPI = {
  async getMemberStatement(params?: StatementParams): Promise<ApiResponse<MemberStatement>> {
    const response = await apiClient.get('/reports/member-statement', { params });
    return response.data;
  },

  async getSavingsSummary(): Promise<ApiResponse<SavingsSummary>> {
    const response = await apiClient.get('/reports/savings-summary');
    return response.data;
  },

  async getLoansSummary(): Promise<ApiResponse<LoansSummary>> {
    const response = await apiClient.get('/reports/loans-summary');
    return response.data;
  },

  async downloadStatement(params?: StatementParams): Promise<Blob> {
    const response = await apiClient.get('/reports/member-statement', {
      params: { ...params, format: 'pdf' },
      responseType: 'blob',
    });
    return response.data;
  },
};
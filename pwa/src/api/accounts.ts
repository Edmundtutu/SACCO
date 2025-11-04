import apiClient from './client';
import type { Account, ApiResponse } from '@/types/api';

export interface AccountFilters {
  type?: 'savings' | 'loan' | 'share';
  status?: 'active' | 'dormant' | 'closed' | 'suspended';
  member_id?: number;
}

export const accountsAPI = {
  /**
   * Get all accounts for the authenticated member
   */
  async getAccounts(filters?: AccountFilters): Promise<ApiResponse<Account[]>> {
    const response = await apiClient.get('/accounts', {
      params: filters
    });
    return response.data;
  },

  /**
   * Get a specific account by ID
   */
  async getAccount(accountId: number): Promise<ApiResponse<Account>> {
    const response = await apiClient.get(`/accounts/${accountId}`);
    return response.data;
  },

  /**
   * Get accounts by type
   */
  async getAccountsByType(type: 'savings' | 'loan' | 'share'): Promise<ApiResponse<Account[]>> {
    return this.getAccounts({ type });
  },

  /**
   * Get savings accounts
   */
  async getSavingsAccounts(): Promise<ApiResponse<Account[]>> {
    return this.getAccountsByType('savings');
  },

  /**
   * Get loan account
   */
  async getLoanAccount(): Promise<ApiResponse<Account>> {
    const response = await this.getAccountsByType('loan');
    if (response.success && response.data && response.data.length > 0) {
      return {
        success: response.success,
        message: response.message,
        data: response.data[0]
      };
    }
    return {
      success: false,
      message: 'No loan account found',
      data: undefined
    };
  },

  /**
   * Get share account
   */
  async getShareAccount(): Promise<ApiResponse<Account>> {
    const response = await this.getAccountsByType('share');
    if (response.success && response.data && response.data.length > 0) {
      return {
        success: response.success,
        message: response.message,
        data: response.data[0]
      };
    }
    return {
      success: false,
      message: 'No share account found',
      data: undefined
    };
  },

  /**
   * Get account balance summary
   */
  async getAccountSummary(): Promise<ApiResponse<{
    total_savings: number;
    total_loans: number;
    total_shares: number;
    accounts_count: number;
  }>> {
    const response = await apiClient.get('/accounts/summary');
    return response.data;
  },
};

import apiClient from './client';
import type { 
  Account,
  Dividend, 
  SharePurchase,
  Share,
  Transaction,
  ApiResponse 
} from '@/types/api';

export const sharesAPI = {
  // Get share account (returns Account wrapper with ShareAccount nested)
  async getShareAccount(): Promise<ApiResponse<Account>> {
    const response = await apiClient.get('/accounts', {
      params: { type: 'share' }
    });
    // Backend returns array, but member should only have one share account
    const accountsData = response.data;
    if (accountsData.success && accountsData.data && Array.isArray(accountsData.data) && accountsData.data.length > 0) {
      return { 
        success: accountsData.success, 
        message: accountsData.message,
        data: accountsData.data[0]
      };
    }
    return accountsData;
  },

  // Legacy alias for backward compatibility
  async getShares(): Promise<ApiResponse<Account>> {
    return this.getShareAccount();
  },

  async purchase(purchaseData: SharePurchase): Promise<ApiResponse<Account>> {
    const response = await apiClient.post('/shares/purchase', purchaseData);
    return response.data;
  },

  async getDividends(): Promise<ApiResponse<Dividend[]>> {
    const response = await apiClient.get('/shares/dividends');
    return response.data;
  },

  async getCertificates(): Promise<ApiResponse<Share[]>> {
    const response = await apiClient.get('/shares/certificates');
    return response.data;
  },

  // Get share transactions
  async getTransactions(memberId: number): Promise<ApiResponse<Transaction[]>> {
    const response = await apiClient.get('/transactions/history', {
      params: {
        member_id: memberId,
        type: 'share_purchase'
      }
    });
    return response.data;
  },
};
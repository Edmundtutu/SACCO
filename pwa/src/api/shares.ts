import apiClient from './client';
import type { 
  SharesAccount, 
  Dividend, 
  SharePurchase,
  ShareCertificate,
  ApiResponse 
} from '@/types/api';

export const sharesAPI = {
  async getShares(): Promise<ApiResponse<SharesAccount>> {
    const response = await apiClient.get('/shares');
    return response.data;
  },

  async purchase(purchaseData: SharePurchase): Promise<ApiResponse<SharesAccount>> {
    const response = await apiClient.post('/shares/purchase', purchaseData);
    return response.data;
  },

  async getDividends(): Promise<ApiResponse<Dividend[]>> {
    const response = await apiClient.get('/shares/dividends');
    return response.data;
  },

  async getCertificates(): Promise<ApiResponse<ShareCertificate[]>> {
    const response = await apiClient.get('/shares/certificates');
    return response.data;
  },

  // Get share transactions
  async getTransactions(memberId: number): Promise<ApiResponse<any[]>> {
    const response = await apiClient.get('/transactions/history', {
      params: {
        member_id: memberId,
        type: 'share_purchase'
      }
    });
    return response.data;
  },
};
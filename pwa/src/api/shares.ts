import apiClient from './client';

export const sharesAPI = {
  async getShares() {
    const response = await apiClient.get('/shares');
    return response.data;
  },

  async purchase(amount: number, shares: number) {
    const response = await apiClient.post('/shares/purchase', { amount, shares });
    return response.data;
  },

  async getDividends() {
    const response = await apiClient.get('/shares/dividends');
    return response.data;
  },
};
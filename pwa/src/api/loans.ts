import apiClient from './client';

export const loansAPI = {
  async getProducts() {
    const response = await apiClient.get('/loans/products');
    return response.data;
  },

  async apply(loanData: { product_id: number; amount: number; term_months: number; purpose: string }) {
    const response = await apiClient.post('/loans/apply', loanData);
    return response.data;
  },

  async getLoans() {
    const response = await apiClient.get('/loans');
    return response.data;
  },

  async repay(loanId: number, amount: number) {
    const response = await apiClient.post(`/loans/${loanId}/repay`, { amount });
    return response.data;
  },
};
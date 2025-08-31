import apiClient from './client';

export const savingsAPI = {
  async getAccounts() {
    const response = await apiClient.get('/savings/accounts');
    return response.data;
  },

  async getProducts() {
    const response = await apiClient.get('/savings/products');
    return response.data;
  },

  async getTransactions(accountId: number) {
    const response = await apiClient.get(`/savings/accounts/${accountId}/transactions`);
    return response.data;
  },

  async deposit(accountId: number, amount: number) {
    const response = await apiClient.post('/savings/deposit', {
      account_id: accountId,
      amount,
    });
    return response.data;
  },

  async withdraw(accountId: number, amount: number) {
    const response = await apiClient.post('/savings/withdraw', {
      account_id: accountId,
      amount,
    });
    return response.data;
  },
};
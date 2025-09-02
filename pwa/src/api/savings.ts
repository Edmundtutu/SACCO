import apiClient from './client';
import type { 
  SavingsAccount, 
  SavingsProduct, 
  Transaction, 
  ApiResponse 
} from '@/types/api';

export interface DepositData {
  account_id: number;
  amount: number;
  payment_method?: string;
  description?: string;
}

export interface WithdrawalData {
  account_id: number;
  amount: number;
  description?: string;
}

export const savingsAPI = {
  async getAccounts(): Promise<ApiResponse<SavingsAccount[]>> {
    const response = await apiClient.get('/savings/accounts');
    return response.data;
  },

  async getProducts(): Promise<ApiResponse<SavingsProduct[]>> {
    const response = await apiClient.get('/savings/products');
    return response.data;
  },

  async getTransactions(accountId: number): Promise<ApiResponse<Transaction[]>> {
    const response = await apiClient.get(`/savings/accounts/${accountId}/transactions`);
    return response.data;
  },

  async deposit(depositData: DepositData): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post('/savings/deposit', depositData);
    return response.data;
  },

  async withdraw(withdrawalData: WithdrawalData): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post('/savings/withdraw', withdrawalData);
    return response.data;
  },

  async openAccount(productId: number): Promise<ApiResponse<SavingsAccount>> {
    const response = await apiClient.post('/savings/accounts', {
      savings_product_id: productId,
    });
    return response.data;
  },
};
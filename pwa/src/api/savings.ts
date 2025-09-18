import apiClient from './client';
import type { 
  SavingsAccount, 
  SavingsProduct, 
  Transaction, 
  ApiResponse 
} from '@/types/api';

export interface DepositData {
  member_id: number;
  account_id: number;
  amount: number;
  payment_method?: string;
  description?: string;
  payment_reference?: string;
  metadata?: Record<string, any>;
}

export interface WithdrawalData {
  member_id: number;
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
    const response = await apiClient.post('/transactions/deposit', {
      member_id: depositData.member_id,
      account_id: depositData.account_id,
      amount: depositData.amount,
      description: depositData.description,
      payment_reference: depositData.payment_reference,
      metadata: depositData.metadata,
    });
    return response.data;
  },

  async withdraw(withdrawalData: WithdrawalData): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post('/transactions/withdrawal', {
      member_id: withdrawalData.member_id,
      account_id: withdrawalData.account_id,
      amount: withdrawalData.amount,
      description: withdrawalData.description,
    });
    return response.data;
  },

  async openAccount(productId: number): Promise<ApiResponse<SavingsAccount>> {
    const response = await apiClient.post('/savings/accounts', {
      savings_product_id: productId,
    });
    return response.data;
  },
};
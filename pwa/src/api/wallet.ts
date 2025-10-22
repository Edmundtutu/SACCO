import apiClient from './client';
import type { ApiResponse, Transaction } from '@/types/api';

export interface WalletBalance {
  account_id: number;
  account_number: string;
  balance: number;
  available_balance: number;
  last_transaction_date?: string;
}

export interface WalletTopupData {
  member_id: number;
  account_id: number;
  amount: number;
  description?: string;
}

export interface WalletWithdrawalData {
  member_id: number;
  account_id: number;
  amount: number;
  description?: string;
}

export interface WalletToSavingsData {
  member_id: number;
  wallet_account_id: number;
  savings_account_id: number;
  amount: number;
  description?: string;
}

export interface WalletToLoanData {
  member_id: number;
  account_id: number;
  loan_id: number;
  amount: number;
  description?: string;
}

export interface WalletTransaction extends Transaction {
  wallet_balance?: number;
  savings_balance?: number;
}

export const walletAPI = {
  async getBalance(accountId: number): Promise<ApiResponse<WalletBalance>> {
    const response = await apiClient.get(`/wallet/balance/${accountId}`);
    return response.data;
  },

  async topup(data: WalletTopupData): Promise<ApiResponse<{ transaction: WalletTransaction; new_balance: number }>> {
    const response = await apiClient.post('/wallet/topup', data);
    return response.data;
  },

  async withdrawal(data: WalletWithdrawalData): Promise<ApiResponse<{ transaction: WalletTransaction; new_balance: number }>> {
    const response = await apiClient.post('/wallet/withdrawal', data);
    return response.data;
  },

  async transferToSavings(data: WalletToSavingsData): Promise<ApiResponse<{
    wallet_transaction: WalletTransaction;
    savings_transaction: WalletTransaction;
    wallet_balance: number;
    savings_balance: number;
  }>> {
    const response = await apiClient.post('/wallet/transfer-to-savings', data);
    return response.data;
  },

  async repayLoan(data: WalletToLoanData): Promise<ApiResponse<{ transaction: WalletTransaction; wallet_balance: number }>> {
    const response = await apiClient.post('/wallet/repay-loan', data);
    return response.data;
  },

  async getHistory(accountId: number, params?: {
    start_date?: string;
    end_date?: string;
    type?: string;
    per_page?: number;
  }): Promise<ApiResponse<Transaction[]>> {
    const response = await apiClient.get(`/wallet/history/${accountId}`, { params });
    return response.data;
  },
};

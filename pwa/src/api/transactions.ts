import apiClient from './client';
import type { 
  Transaction, 
  ApiResponse 
} from '@/types/api';

export interface DepositData {
  member_id: number;
  account_id: number;
  amount: number;
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

export interface SharePurchaseData {
  member_id: number;
  amount: number;
  description?: string;
}

export interface LoanDisbursementData {
  loan_id: number;
  disbursement_method: 'cash' | 'bank_transfer' | 'mobile_money';
  notes?: string;
}

export interface LoanRepaymentData {
  loan_id: number;
  amount: number;
  payment_method?: string;
  notes?: string;
}

export interface TransactionHistoryParams {
  member_id: number;
  start_date?: string;
  end_date?: string;
  type?: 'deposit' | 'withdrawal' | 'share_purchase' | 'loan_disbursement' | 'loan_repayment';
  page?: number;
  per_page?: number;
}

export interface TransactionSummaryParams {
  member_id: number;
  start_date?: string;
  end_date?: string;
}

export interface TransactionReversalData {
  transaction_id: number;
  reason: string;
}

export const transactionsAPI = {
  // Deposit transactions
  async deposit(depositData: DepositData): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post('/transactions/deposit', depositData);
    return response.data;
  },

  // Withdrawal transactions
  async withdraw(withdrawalData: WithdrawalData): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post('/transactions/withdrawal', withdrawalData);
    return response.data;
  },

  // Share purchase transactions
  async purchaseShares(shareData: SharePurchaseData): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post('/transactions/share-purchase', shareData);
    return response.data;
  },

  // Loan disbursement transactions
  async disburseLoan(disbursementData: LoanDisbursementData): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post('/transactions/loan-disbursement', disbursementData);
    return response.data;
  },

  // Loan repayment transactions
  async repayLoan(repaymentData: LoanRepaymentData): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post('/transactions/loan-repayment', repaymentData);
    return response.data;
  },

  // Get transaction history
  async getHistory(params: TransactionHistoryParams): Promise<ApiResponse<{
    data: Transaction[];
    meta: {
      current_page: number;
      total: number;
      per_page: number;
      last_page: number;
    };
  }>> {
    const response = await apiClient.get('/transactions/history', { params });
    return response.data;
  },

  // Get transaction summary
  async getSummary(params: TransactionSummaryParams): Promise<ApiResponse<{
    total_transactions: number;
    total_deposits: number;
    total_withdrawals: number;
    total_loan_disbursements: number;
    total_loan_repayments: number;
    total_share_purchases: number;
    net_cash_flow: number;
  }>> {
    const response = await apiClient.get('/transactions/summary', { params });
    return response.data;
  },

  // Reverse transaction (admin only)
  async reverse(reversalData: TransactionReversalData): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post('/transactions/reverse', reversalData);
    return response.data;
  },

  // Get single transaction details
  async getTransaction(transactionId: number): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.get(`/transactions/${transactionId}`);
    return response.data;
  },

  // Get pending transactions (admin only)
  async getPending(): Promise<ApiResponse<Transaction[]>> {
    const response = await apiClient.get('/transactions/pending');
    return response.data;
  },

  // Approve transaction (admin only)
  async approve(transactionId: number, notes?: string): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post(`/transactions/${transactionId}/approve`, { notes });
    return response.data;
  },

  // Reject transaction (admin only)
  async reject(transactionId: number, reason: string): Promise<ApiResponse<Transaction>> {
    const response = await apiClient.post(`/transactions/${transactionId}/reject`, { reason });
    return response.data;
  },
};

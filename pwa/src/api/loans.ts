import apiClient from './client';
import type { 
  Loan, 
  LoanProduct, 
  LoanApplication, 
  LoanRepayment,
  RepaymentSchedule,
  ApiResponse 
} from '@/types/api';

export interface RepaymentData {
  amount: number;
  payment_method?: string;
  reference?: string;
}

export interface GuarantorResponse {
  action: 'accept' | 'decline';
  comment?: string;
}

export const loansAPI = {
  async getProducts(): Promise<ApiResponse<LoanProduct[]>> {
    const response = await apiClient.get('/loans/products');
    return response.data;
  },

  async apply(loanData: LoanApplication): Promise<ApiResponse<Loan>> {
    const response = await apiClient.post('/loans/apply', loanData);
    return response.data;
  },

  async getLoans(): Promise<ApiResponse<Loan[]>> {
    const response = await apiClient.get('/loans');
    return response.data;
  },

  async getLoan(loanId: number): Promise<ApiResponse<Loan>> {
    const response = await apiClient.get(`/loans/${loanId}`);
    return response.data;
  },

  async repay(loanId: number, repaymentData: RepaymentData): Promise<ApiResponse<LoanRepayment>> {
    const response = await apiClient.post(`/loans/${loanId}/repay`, repaymentData);
    return response.data;
  },

  // Get loan transactions
  async getLoanTransactions(loanId: number): Promise<ApiResponse<any[]>> {
    const response = await apiClient.get('/transactions/history', {
      params: {
        related_loan_id: loanId
      }
    });
    return response.data;
  },

  async getRepaymentSchedule(loanId: number): Promise<ApiResponse<RepaymentSchedule[]>> {
    const response = await apiClient.get(`/loans/${loanId}/schedule`);
    return response.data;
  },

  async addGuarantor(loanId: number, guarantorId: number): Promise<ApiResponse<any>> {
    const response = await apiClient.post(`/loans/${loanId}/guarantee`, {
      guarantor_id: guarantorId,
    });
    return response.data;
  },

  async respondToGuarantee(guarantorId: number, responseData: GuarantorResponse): Promise<ApiResponse<any>> {
    const response = await apiClient.post(`/loans/guarantors/${guarantorId}/respond`, responseData);
    return response.data;
  },
};
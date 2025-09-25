import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { transactionsAPI } from '@/api/transactions';
import type { Transaction, ApiResponse } from '@/types/api';

interface TransactionState {
  transactions: Transaction[];
  loading: boolean;
  error: string | null;
  currentTransaction: Transaction | null;
  summary: {
    total_transactions: number;
    total_deposits: number;
    total_withdrawals: number;
    total_loan_disbursements: number;
    total_loan_repayments: number;
    total_share_purchases: number;
    net_cash_flow: number;
  } | null;
}

const initialState: TransactionState = {
  transactions: [],
  loading: false,
  error: null,
  currentTransaction: null,
  summary: null,
};

// Async thunks for transaction operations
export const makeDeposit = createAsyncThunk(
  'transactions/makeDeposit',
  async (depositData: {
    member_id: number;
    account_id: number;
    amount: number;
    description?: string;
    payment_reference?: string;
    metadata?: Record<string, any>;
  }, { rejectWithValue }) => {
    try {
      const response = await transactionsAPI.deposit(depositData);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to make deposit');
    }
  }
);

export const makeWithdrawal = createAsyncThunk(
  'transactions/makeWithdrawal',
  async (withdrawalData: {
    member_id: number;
    account_id: number;
    amount: number;
    description?: string;
  }, { rejectWithValue }) => {
    try {
      const response = await transactionsAPI.withdraw(withdrawalData);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to make withdrawal');
    }
  }
);

export const purchaseShares = createAsyncThunk(
  'transactions/purchaseShares',
  async (shareData: {
    member_id: number;
    amount: number;
    description?: string;
  }, { rejectWithValue }) => {
    try {
      const response = await transactionsAPI.purchaseShares(shareData);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to purchase shares');
    }
  }
);

export const disburseLoan = createAsyncThunk(
  'transactions/disburseLoan',
  async (disbursementData: {
    loan_id: number;
    disbursement_method: 'cash' | 'bank_transfer' | 'mobile_money';
    notes?: string;
  }, { rejectWithValue }) => {
    try {
      const response = await transactionsAPI.disburseLoan(disbursementData);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to disburse loan');
    }
  }
);

export const repayLoan = createAsyncThunk(
  'transactions/repayLoan',
  async (repaymentData: {
    loan_id: number;
    amount: number;
    payment_method?: string;
    notes?: string;
  }, { rejectWithValue }) => {
    try {
      const response = await transactionsAPI.repayLoan(repaymentData);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to repay loan');
    }
  }
);

export const fetchTransactionHistory = createAsyncThunk(
  'transactions/fetchHistory',
  async (params: {
    member_id: number;
    start_date?: string;
    end_date?: string;
    type?: 'deposit' | 'withdrawal' | 'share_purchase' | 'loan_disbursement' | 'loan_repayment';
    page?: number;
    per_page?: number;
  }, { rejectWithValue }) => {
    try {
      const response = await transactionsAPI.getHistory(params);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to fetch transaction history');
    }
  }
);

export const fetchTransactionSummary = createAsyncThunk(
  'transactions/fetchSummary',
  async (params: {
    member_id: number;
    start_date?: string;
    end_date?: string;
  }, { rejectWithValue }) => {
    try {
      const response = await transactionsAPI.getSummary(params);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to fetch transaction summary');
    }
  }
);

export const fetchTransaction = createAsyncThunk(
  'transactions/fetchTransaction',
  async (transactionId: number, { rejectWithValue }) => {
    try {
      const response = await transactionsAPI.getTransaction(transactionId);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to fetch transaction');
    }
  }
);

const transactionsSlice = createSlice({
  name: 'transactions',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    clearCurrentTransaction: (state) => {
      state.currentTransaction = null;
    },
    setCurrentTransaction: (state, action: PayloadAction<Transaction>) => {
      state.currentTransaction = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
      // Make Deposit
      .addCase(makeDeposit.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(makeDeposit.fulfilled, (state, action) => {
        state.loading = false;
        state.transactions.unshift(action.payload);
        state.currentTransaction = action.payload;
      })
      .addCase(makeDeposit.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Make Withdrawal
      .addCase(makeWithdrawal.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(makeWithdrawal.fulfilled, (state, action) => {
        state.loading = false;
        state.transactions.unshift(action.payload);
        state.currentTransaction = action.payload;
      })
      .addCase(makeWithdrawal.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Purchase Shares
      .addCase(purchaseShares.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(purchaseShares.fulfilled, (state, action) => {
        state.loading = false;
        state.transactions.unshift(action.payload);
        state.currentTransaction = action.payload;
      })
      .addCase(purchaseShares.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Disburse Loan
      .addCase(disburseLoan.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(disburseLoan.fulfilled, (state, action) => {
        state.loading = false;
        state.transactions.unshift(action.payload);
        state.currentTransaction = action.payload;
      })
      .addCase(disburseLoan.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Repay Loan
      .addCase(repayLoan.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(repayLoan.fulfilled, (state, action) => {
        state.loading = false;
        state.transactions.unshift(action.payload);
        state.currentTransaction = action.payload;
      })
      .addCase(repayLoan.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Fetch Transaction History
      .addCase(fetchTransactionHistory.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTransactionHistory.fulfilled, (state, action) => {
        state.loading = false;
        // The payload is already the transactions array, not wrapped in a data property
        state.transactions = action.payload || [];
      })
      .addCase(fetchTransactionHistory.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Fetch Transaction Summary
      .addCase(fetchTransactionSummary.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTransactionSummary.fulfilled, (state, action) => {
        state.loading = false;
        state.summary = action.payload;
      })
      .addCase(fetchTransactionSummary.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Fetch Single Transaction
      .addCase(fetchTransaction.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTransaction.fulfilled, (state, action) => {
        state.loading = false;
        state.currentTransaction = action.payload;
      })
      .addCase(fetchTransaction.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });
  },
});

export const { clearError, clearCurrentTransaction, setCurrentTransaction } = transactionsSlice.actions;

export default transactionsSlice.reducer;
import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { transactionsAPI } from '../api/transactions';
import type { 
  Transaction, 
  DepositData, 
  WithdrawalData, 
  SharePurchaseData,
  LoanDisbursementData,
  LoanRepaymentData,
  TransactionHistoryParams,
  TransactionSummaryParams,
  TransactionReversalData
} from '../api/transactions';

interface TransactionsState {
  transactions: Transaction[];
  pendingTransactions: Transaction[];
  transactionSummary: {
    total_transactions: number;
    total_deposits: number;
    total_withdrawals: number;
    total_loan_disbursements: number;
    total_loan_repayments: number;
    total_share_purchases: number;
    net_cash_flow: number;
  } | null;
  loading: boolean;
  error: string | null;
  pagination: {
    current_page: number;
    total: number;
    per_page: number;
    last_page: number;
  } | null;
}

const initialState: TransactionsState = {
  transactions: [],
  pendingTransactions: [],
  transactionSummary: null,
  loading: false,
  error: null,
  pagination: null,
};

// Async thunks
export const makeDeposit = createAsyncThunk(
  'transactions/deposit',
  async (depositData: DepositData) => {
    const response = await transactionsAPI.deposit(depositData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to make deposit');
  }
);

export const makeWithdrawal = createAsyncThunk(
  'transactions/withdraw',
  async (withdrawalData: WithdrawalData) => {
    const response = await transactionsAPI.withdraw(withdrawalData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to make withdrawal');
  }
);

export const purchaseShares = createAsyncThunk(
  'transactions/purchaseShares',
  async (shareData: SharePurchaseData) => {
    const response = await transactionsAPI.purchaseShares(shareData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to purchase shares');
  }
);

export const disburseLoan = createAsyncThunk(
  'transactions/disburseLoan',
  async (disbursementData: LoanDisbursementData) => {
    const response = await transactionsAPI.disburseLoan(disbursementData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to disburse loan');
  }
);

export const repayLoan = createAsyncThunk(
  'transactions/repayLoan',
  async (repaymentData: LoanRepaymentData) => {
    const response = await transactionsAPI.repayLoan(repaymentData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to repay loan');
  }
);

export const fetchTransactionHistory = createAsyncThunk(
  'transactions/fetchHistory',
  async (params: TransactionHistoryParams) => {
    const response = await transactionsAPI.getHistory(params);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to fetch transaction history');
  }
);

export const fetchTransactionSummary = createAsyncThunk(
  'transactions/fetchSummary',
  async (params: TransactionSummaryParams) => {
    const response = await transactionsAPI.getSummary(params);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to fetch transaction summary');
  }
);

export const fetchPendingTransactions = createAsyncThunk(
  'transactions/fetchPending',
  async () => {
    const response = await transactionsAPI.getPending();
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to fetch pending transactions');
  }
);

export const reverseTransaction = createAsyncThunk(
  'transactions/reverse',
  async (reversalData: TransactionReversalData) => {
    const response = await transactionsAPI.reverse(reversalData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to reverse transaction');
  }
);

export const approveTransaction = createAsyncThunk(
  'transactions/approve',
  async ({ transactionId, notes }: { transactionId: number; notes?: string }) => {
    const response = await transactionsAPI.approve(transactionId, notes);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to approve transaction');
  }
);

export const rejectTransaction = createAsyncThunk(
  'transactions/reject',
  async ({ transactionId, reason }: { transactionId: number; reason: string }) => {
    const response = await transactionsAPI.reject(transactionId, reason);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to reject transaction');
  }
);

const transactionsSlice = createSlice({
  name: 'transactions',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    clearTransactions: (state) => {
      state.transactions = [];
      state.pagination = null;
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
      })
      .addCase(makeDeposit.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to make deposit';
      })

      // Make Withdrawal
      .addCase(makeWithdrawal.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(makeWithdrawal.fulfilled, (state, action) => {
        state.loading = false;
        state.transactions.unshift(action.payload);
      })
      .addCase(makeWithdrawal.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to make withdrawal';
      })

      // Purchase Shares
      .addCase(purchaseShares.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(purchaseShares.fulfilled, (state, action) => {
        state.loading = false;
        state.transactions.unshift(action.payload);
      })
      .addCase(purchaseShares.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to purchase shares';
      })

      // Disburse Loan
      .addCase(disburseLoan.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(disburseLoan.fulfilled, (state, action) => {
        state.loading = false;
        state.transactions.unshift(action.payload);
      })
      .addCase(disburseLoan.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to disburse loan';
      })

      // Repay Loan
      .addCase(repayLoan.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(repayLoan.fulfilled, (state, action) => {
        state.loading = false;
        state.transactions.unshift(action.payload);
      })
      .addCase(repayLoan.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to repay loan';
      })

      // Fetch Transaction History
      .addCase(fetchTransactionHistory.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTransactionHistory.fulfilled, (state, action) => {
        state.loading = false;
        state.transactions = action.payload.data;
        state.pagination = action.payload.meta;
      })
      .addCase(fetchTransactionHistory.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch transaction history';
      })

      // Fetch Transaction Summary
      .addCase(fetchTransactionSummary.fulfilled, (state, action) => {
        state.transactionSummary = action.payload;
      })

      // Fetch Pending Transactions
      .addCase(fetchPendingTransactions.fulfilled, (state, action) => {
        state.pendingTransactions = action.payload;
      })

      // Reverse Transaction
      .addCase(reverseTransaction.fulfilled, (state, action) => {
        const index = state.transactions.findIndex(t => t.id === action.payload.id);
        if (index !== -1) {
          state.transactions[index] = action.payload;
        }
      })

      // Approve Transaction
      .addCase(approveTransaction.fulfilled, (state, action) => {
        // Remove from pending transactions
        state.pendingTransactions = state.pendingTransactions.filter(
          t => t.id !== action.payload.id
        );
        // Add to main transactions
        state.transactions.unshift(action.payload);
      })

      // Reject Transaction
      .addCase(rejectTransaction.fulfilled, (state, action) => {
        // Remove from pending transactions
        state.pendingTransactions = state.pendingTransactions.filter(
          t => t.id !== action.payload.id
        );
      });
  },
});

export const { clearError, clearTransactions } = transactionsSlice.actions;
export default transactionsSlice.reducer;

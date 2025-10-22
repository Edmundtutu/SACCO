import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { walletAPI, WalletBalance, WalletTopupData, WalletWithdrawalData, WalletToSavingsData, WalletToLoanData } from '@/api/wallet';
import type { Transaction } from '@/types/api';

interface WalletState {
  balance: WalletBalance | null;
  transactions: Transaction[];
  loading: boolean;
  error: string | null;
  lastTransactionSuccess: boolean;
}

const initialState: WalletState = {
  balance: null,
  transactions: [],
  loading: false,
  error: null,
  lastTransactionSuccess: false,
};

// Async thunks
export const fetchWalletBalance = createAsyncThunk(
  'wallet/fetchBalance',
  async (accountId: number, { rejectWithValue }) => {
    try {
      const response = await walletAPI.getBalance(accountId);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to fetch wallet balance');
    }
  }
);

export const topupWallet = createAsyncThunk(
  'wallet/topup',
  async (data: WalletTopupData, { rejectWithValue }) => {
    try {
      const response = await walletAPI.topup(data);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to top up wallet');
    }
  }
);

export const withdrawFromWallet = createAsyncThunk(
  'wallet/withdrawal',
  async (data: WalletWithdrawalData, { rejectWithValue }) => {
    try {
      const response = await walletAPI.withdrawal(data);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to withdraw from wallet');
    }
  }
);

export const transferToSavings = createAsyncThunk(
  'wallet/transferToSavings',
  async (data: WalletToSavingsData, { rejectWithValue }) => {
    try {
      const response = await walletAPI.transferToSavings(data);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to transfer to savings');
    }
  }
);

export const repayLoanFromWallet = createAsyncThunk(
  'wallet/repayLoan',
  async (data: WalletToLoanData, { rejectWithValue }) => {
    try {
      const response = await walletAPI.repayLoan(data);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to repay loan from wallet');
    }
  }
);

export const fetchWalletHistory = createAsyncThunk(
  'wallet/fetchHistory',
  async (params: { accountId: number; filters?: any }, { rejectWithValue }) => {
    try {
      const response = await walletAPI.getHistory(params.accountId, params.filters);
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to fetch wallet history');
    }
  }
);

const walletSlice = createSlice({
  name: 'wallet',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    resetTransactionSuccess: (state) => {
      state.lastTransactionSuccess = false;
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch Balance
      .addCase(fetchWalletBalance.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchWalletBalance.fulfilled, (state, action: PayloadAction<WalletBalance>) => {
        state.loading = false;
        state.balance = action.payload;
      })
      .addCase(fetchWalletBalance.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Top-up
      .addCase(topupWallet.pending, (state) => {
        state.loading = true;
        state.error = null;
        state.lastTransactionSuccess = false;
      })
      .addCase(topupWallet.fulfilled, (state, action) => {
        state.loading = false;
        state.lastTransactionSuccess = true;
        if (state.balance) {
          state.balance.balance = action.payload.new_balance;
          state.balance.available_balance = action.payload.new_balance;
        }
      })
      .addCase(topupWallet.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Withdrawal
      .addCase(withdrawFromWallet.pending, (state) => {
        state.loading = true;
        state.error = null;
        state.lastTransactionSuccess = false;
      })
      .addCase(withdrawFromWallet.fulfilled, (state, action) => {
        state.loading = false;
        state.lastTransactionSuccess = true;
        if (state.balance) {
          state.balance.balance = action.payload.new_balance;
          state.balance.available_balance = action.payload.new_balance;
        }
      })
      .addCase(withdrawFromWallet.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Transfer to Savings
      .addCase(transferToSavings.pending, (state) => {
        state.loading = true;
        state.error = null;
        state.lastTransactionSuccess = false;
      })
      .addCase(transferToSavings.fulfilled, (state, action) => {
        state.loading = false;
        state.lastTransactionSuccess = true;
        if (state.balance) {
          state.balance.balance = action.payload.wallet_balance;
          state.balance.available_balance = action.payload.wallet_balance;
        }
      })
      .addCase(transferToSavings.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Repay Loan
      .addCase(repayLoanFromWallet.pending, (state) => {
        state.loading = true;
        state.error = null;
        state.lastTransactionSuccess = false;
      })
      .addCase(repayLoanFromWallet.fulfilled, (state, action) => {
        state.loading = false;
        state.lastTransactionSuccess = true;
        if (state.balance) {
          state.balance.balance = action.payload.wallet_balance;
          state.balance.available_balance = action.payload.wallet_balance;
        }
      })
      .addCase(repayLoanFromWallet.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Fetch History
      .addCase(fetchWalletHistory.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchWalletHistory.fulfilled, (state, action: PayloadAction<Transaction[]>) => {
        state.loading = false;
        state.transactions = action.payload;
      })
      .addCase(fetchWalletHistory.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });
  },
});

export const { clearError, resetTransactionSuccess } = walletSlice.actions;
export default walletSlice.reducer;

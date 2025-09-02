import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { savingsAPI, DepositData, WithdrawalData } from '../api/savings';
import type { SavingsAccount, SavingsProduct, Transaction } from '@/types/api';

interface SavingsState {
  accounts: SavingsAccount[];
  products: SavingsProduct[];
  transactions: Transaction[];
  loading: boolean;
  error: string | null;
}

const initialState: SavingsState = {
  accounts: [],
  products: [],
  transactions: [],
  loading: false,
  error: null,
};

export const fetchSavingsAccounts = createAsyncThunk(
  'savings/fetchAccounts',
  async () => {
    const response = await savingsAPI.getAccounts();
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to fetch savings accounts');
  }
);

export const fetchSavingsProducts = createAsyncThunk(
  'savings/fetchProducts',
  async () => {
    const response = await savingsAPI.getProducts();
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to fetch savings products');
  }
);

export const fetchTransactions = createAsyncThunk(
  'savings/fetchTransactions',
  async (accountId: number) => {
    const response = await savingsAPI.getTransactions(accountId);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to fetch transactions');
  }
);

export const makeDeposit = createAsyncThunk(
  'savings/deposit',
  async (depositData: DepositData) => {
    const response = await savingsAPI.deposit(depositData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to make deposit');
  }
);

export const makeWithdrawal = createAsyncThunk(
  'savings/withdraw',
  async (withdrawalData: WithdrawalData) => {
    const response = await savingsAPI.withdraw(withdrawalData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to make withdrawal');
  }
);

export const openSavingsAccount = createAsyncThunk(
  'savings/openAccount',
  async (productId: number) => {
    const response = await savingsAPI.openAccount(productId);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to open savings account');
  }
);

const savingsSlice = createSlice({
  name: 'savings',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchSavingsAccounts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchSavingsAccounts.fulfilled, (state, action) => {
        state.loading = false;
        state.accounts = action.payload;
      })
      .addCase(fetchSavingsAccounts.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch accounts';
      })
      .addCase(fetchSavingsProducts.fulfilled, (state, action) => {
        state.products = action.payload;
      })
      .addCase(fetchTransactions.fulfilled, (state, action) => {
        state.transactions = action.payload;
      })
      .addCase(makeDeposit.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(makeDeposit.fulfilled, (state, action) => {
        state.loading = false;
        // Update account balance
        const transaction = action.payload;
        const account = state.accounts.find(acc => acc.id === transaction.account_id);
        if (account) {
          account.balance = transaction.balance_after;
          account.available_balance = transaction.balance_after;
        }
        // Add transaction to the beginning of the list
        state.transactions.unshift(transaction);
      })
      .addCase(makeDeposit.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to make deposit';
      })
      .addCase(makeWithdrawal.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(makeWithdrawal.fulfilled, (state, action) => {
        state.loading = false;
        // Update account balance
        const transaction = action.payload;
        const account = state.accounts.find(acc => acc.id === transaction.account_id);
        if (account) {
          account.balance = transaction.balance_after;
          account.available_balance = transaction.balance_after;
        }
        // Add transaction to the beginning of the list
        state.transactions.unshift(transaction);
      })
      .addCase(makeWithdrawal.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to make withdrawal';
      })
      .addCase(openSavingsAccount.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(openSavingsAccount.fulfilled, (state, action) => {
        state.loading = false;
        state.accounts.push(action.payload);
      })
      .addCase(openSavingsAccount.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to open account';
      });
  },
});

export const { clearError } = savingsSlice.actions;
export default savingsSlice.reducer;
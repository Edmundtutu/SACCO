import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { savingsAPI } from '../api/savings';

interface SavingsAccount {
  id: number;
  account_number: string;
  product_name: string;
  balance: number;
  interest_rate: number;
  status: 'active' | 'dormant' | 'closed';
  created_at: string;
}

interface SavingsProduct {
  id: number;
  name: string;
  description: string;
  minimum_balance: number;
  interest_rate: number;
  features: string[];
}

interface Transaction {
  id: number;
  type: 'deposit' | 'withdrawal' | 'interest' | 'fee';
  amount: number;
  balance_after: number;
  description: string;
  created_at: string;
}

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
    return response;
  }
);

export const fetchSavingsProducts = createAsyncThunk(
  'savings/fetchProducts',
  async () => {
    const response = await savingsAPI.getProducts();
    return response;
  }
);

export const fetchTransactions = createAsyncThunk(
  'savings/fetchTransactions',
  async (accountId: number) => {
    const response = await savingsAPI.getTransactions(accountId);
    return response;
  }
);

export const makeDeposit = createAsyncThunk(
  'savings/deposit',
  async ({ accountId, amount }: { accountId: number; amount: number }) => {
    const response = await savingsAPI.deposit(accountId, amount);
    return response;
  }
);

export const makeWithdrawal = createAsyncThunk(
  'savings/withdraw',
  async ({ accountId, amount }: { accountId: number; amount: number }) => {
    const response = await savingsAPI.withdraw(accountId, amount);
    return response;
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
      .addCase(makeDeposit.fulfilled, (state, action) => {
        // Update account balance and add transaction
        const account = state.accounts.find(acc => acc.id === action.payload.account_id);
        if (account) {
          account.balance = action.payload.balance_after;
        }
        state.transactions.unshift(action.payload);
      })
      .addCase(makeWithdrawal.fulfilled, (state, action) => {
        // Update account balance and add transaction
        const account = state.accounts.find(acc => acc.id === action.payload.account_id);
        if (account) {
          account.balance = action.payload.balance_after;
        }
        state.transactions.unshift(action.payload);
      });
  },
});

export const { clearError } = savingsSlice.actions;
export default savingsSlice.reducer;
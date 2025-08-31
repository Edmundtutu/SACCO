import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { sharesAPI } from '../api/shares';

interface SharesAccount {
  id: number;
  total_shares: number;
  share_value: number;
  total_value: number;
  dividends_earned: number;
  last_dividend_date?: string;
}

interface Dividend {
  id: number;
  year: number;
  rate: number;
  amount: number;
  paid_date: string;
}

interface SharesState {
  account: SharesAccount | null;
  dividends: Dividend[];
  loading: boolean;
  error: string | null;
}

const initialState: SharesState = {
  account: null,
  dividends: [],
  loading: false,
  error: null,
};

export const fetchShares = createAsyncThunk('shares/fetchShares', async () => {
  const response = await sharesAPI.getShares();
  return response;
});

export const fetchDividends = createAsyncThunk('shares/fetchDividends', async () => {
  const response = await sharesAPI.getDividends();
  return response;
});

export const purchaseShares = createAsyncThunk(
  'shares/purchase',
  async ({ amount, shares }: { amount: number; shares: number }) => {
    const response = await sharesAPI.purchase(amount, shares);
    return response;
  }
);

const sharesSlice = createSlice({
  name: 'shares',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchShares.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchShares.fulfilled, (state, action) => {
        state.loading = false;
        state.account = action.payload;
      })
      .addCase(fetchShares.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch shares';
      })
      .addCase(fetchDividends.fulfilled, (state, action) => {
        state.dividends = action.payload;
      })
      .addCase(purchaseShares.fulfilled, (state, action) => {
        state.account = action.payload;
      });
  },
});

export const { clearError } = sharesSlice.actions;
export default sharesSlice.reducer;
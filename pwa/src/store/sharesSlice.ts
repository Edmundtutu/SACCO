import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { sharesAPI } from '../api/shares';
import type { Account, Dividend, SharePurchase, Share } from '@/types/api';

interface SharesState {
  account: Account | null;  // Polymorphic Account wrapper with ShareAccount nested
  dividends: Dividend[];
  certificates: Share[];  // Individual share certificates
  loading: boolean;
  error: string | null;
}

const initialState: SharesState = {
  account: null,
  dividends: [],
  certificates: [],
  loading: false,
  error: null,
};

export const fetchShares = createAsyncThunk('shares/fetchShares', async () => {
  const response = await sharesAPI.getShareAccount();
  if (response.success && response.data) {
    return response.data;
  }
  throw new Error(response.message || 'Failed to fetch shares');
});

export const fetchDividends = createAsyncThunk('shares/fetchDividends', async () => {
  const response = await sharesAPI.getDividends();
  if (response.success && response.data) {
    return response.data;
  }
  throw new Error(response.message || 'Failed to fetch dividends');
});

export const fetchCertificates = createAsyncThunk('shares/fetchCertificates', async () => {
  const response = await sharesAPI.getCertificates();
  if (response.success && response.data) {
    return response.data;
  }
  throw new Error(response.message || 'Failed to fetch certificates');
});

export const purchaseShares = createAsyncThunk(
  'shares/purchase',
  async (purchaseData: SharePurchase) => {
    const response = await sharesAPI.purchase(purchaseData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to purchase shares');
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
      .addCase(fetchDividends.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchDividends.fulfilled, (state, action) => {
        state.loading = false;
        state.dividends = action.payload;
      })
      .addCase(fetchDividends.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch dividends';
      })
      .addCase(fetchCertificates.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchCertificates.fulfilled, (state, action) => {
        state.loading = false;
        state.certificates = action.payload;
      })
      .addCase(fetchCertificates.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch certificates';
      })
      .addCase(purchaseShares.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(purchaseShares.fulfilled, (state, action) => {
        state.loading = false;
        // Update the share account with new data
        state.account = action.payload;
      })
      .addCase(purchaseShares.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to purchase shares';
      });
  },
});

export const { clearError } = sharesSlice.actions;
export default sharesSlice.reducer;
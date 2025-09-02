import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { loansAPI, RepaymentData } from '../api/loans';
import type { Loan, LoanProduct, LoanApplication, RepaymentSchedule } from '@/types/api';

interface LoansState {
  loans: Loan[];
  products: LoanProduct[];
  selectedLoan: Loan | null;
  repaymentSchedule: RepaymentSchedule[];
  loading: boolean;
  error: string | null;
}

const initialState: LoansState = {
  loans: [],
  products: [],
  selectedLoan: null,
  repaymentSchedule: [],
  loading: false,
  error: null,
};

export const fetchLoans = createAsyncThunk('loans/fetchLoans', async () => {
  const response = await loansAPI.getLoans();
  if (response.success && response.data) {
    return response.data;
  }
  throw new Error(response.message || 'Failed to fetch loans');
});

export const fetchLoanProducts = createAsyncThunk('loans/fetchProducts', async () => {
  const response = await loansAPI.getProducts();
  if (response.success && response.data) {
    return response.data;
  }
  throw new Error(response.message || 'Failed to fetch loan products');
});

export const fetchLoan = createAsyncThunk('loans/fetchLoan', async (loanId: number) => {
  const response = await loansAPI.getLoan(loanId);
  if (response.success && response.data) {
    return response.data;
  }
  throw new Error(response.message || 'Failed to fetch loan details');
});

export const fetchRepaymentSchedule = createAsyncThunk(
  'loans/fetchSchedule',
  async (loanId: number) => {
    const response = await loansAPI.getRepaymentSchedule(loanId);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to fetch repayment schedule');
  }
);

export const applyForLoan = createAsyncThunk(
  'loans/apply',
  async (loanData: LoanApplication) => {
    const response = await loansAPI.apply(loanData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to apply for loan');
  }
);

export const repayLoan = createAsyncThunk(
  'loans/repay',
  async ({ loanId, repaymentData }: { loanId: number; repaymentData: RepaymentData }) => {
    const response = await loansAPI.repay(loanId, repaymentData);
    if (response.success && response.data) {
      return response.data;
    }
    throw new Error(response.message || 'Failed to make loan repayment');
  }
);

const loansSlice = createSlice({
  name: 'loans',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchLoans.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchLoans.fulfilled, (state, action) => {
        state.loading = false;
        state.loans = action.payload;
      })
      .addCase(fetchLoans.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch loans';
      })
      .addCase(fetchLoanProducts.fulfilled, (state, action) => {
        state.products = action.payload;
      })
      .addCase(applyForLoan.fulfilled, (state, action) => {
        state.loans.push(action.payload);
      })
      .addCase(repayLoan.fulfilled, (state, action) => {
        const loan = state.loans.find(l => l.id === action.payload.loan_id);
        if (loan) {
          loan.outstanding_balance = action.payload.outstanding_balance;
          loan.next_payment_date = action.payload.next_payment_date;
        }
      });
  },
});

export const { clearError } = loansSlice.actions;
export default loansSlice.reducer;
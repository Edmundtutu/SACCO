import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { loansAPI } from '../api/loans';

interface Loan {
  id: number;
  product_name: string;
  principal_amount: number;
  outstanding_balance: number;
  interest_rate: number;
  monthly_payment: number;
  next_payment_date: string;
  status: 'active' | 'paid' | 'overdue' | 'pending';
  created_at: string;
}

interface LoanProduct {
  id: number;
  name: string;
  description: string;
  max_amount: number;
  interest_rate: number;
  max_term_months: number;
  requirements: string[];
}

interface LoansState {
  loans: Loan[];
  products: LoanProduct[];
  loading: boolean;
  error: string | null;
}

const initialState: LoansState = {
  loans: [],
  products: [],
  loading: false,
  error: null,
};

export const fetchLoans = createAsyncThunk('loans/fetchLoans', async () => {
  const response = await loansAPI.getLoans();
  return response;
});

export const fetchLoanProducts = createAsyncThunk('loans/fetchProducts', async () => {
  const response = await loansAPI.getProducts();
  return response;
});

export const applyForLoan = createAsyncThunk(
  'loans/apply',
  async (loanData: { product_id: number; amount: number; term_months: number; purpose: string }) => {
    const response = await loansAPI.apply(loanData);
    return response;
  }
);

export const repayLoan = createAsyncThunk(
  'loans/repay',
  async ({ loanId, amount }: { loanId: number; amount: number }) => {
    const response = await loansAPI.repay(loanId, amount);
    return response;
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
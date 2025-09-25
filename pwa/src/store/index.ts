import { configureStore } from '@reduxjs/toolkit';
import authSlice from './authSlice';
import savingsSlice from './savingsSlice';
import loansSlice from './loansSlice';
import sharesSlice from './sharesSlice';
import transactionsSlice from './transactionsSlice';
import savingsGoalsSlice from './savingsGoalsSlice';

export const store = configureStore({
  reducer: {
    auth: authSlice,
    savings: savingsSlice,
    loans: loansSlice,
    shares: sharesSlice,
    transactions: transactionsSlice,
    savingsGoals: savingsGoalsSlice,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
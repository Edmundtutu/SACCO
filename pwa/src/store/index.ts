import { configureStore } from '@reduxjs/toolkit';
import authSlice from './authSlice';
import savingsSlice from './savingsSlice';
import loansSlice from './loansSlice';
import sharesSlice from './sharesSlice';

export const store = configureStore({
  reducer: {
    auth: authSlice,
    savings: savingsSlice,
    loans: loansSlice,
    shares: sharesSlice,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
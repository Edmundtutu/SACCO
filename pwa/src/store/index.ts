import { configureStore } from '@reduxjs/toolkit';
import { persistStore, persistReducer, FLUSH, REHYDRATE, PAUSE, PERSIST, PURGE, REGISTER } from 'redux-persist';
import storage from 'redux-persist/lib/storage';
import authSlice from './authSlice';
import savingsSlice from './savingsSlice';
import loansSlice from './loansSlice';
import sharesSlice from './sharesSlice';
import transactionsSlice from './transactionsSlice';
import savingsGoalsSlice from './savingsGoalsSlice';
import walletSlice from './walletSlice';

// Persist config for auth slice
const authPersistConfig = {
  key: 'auth',
  storage,
  whitelist: ['user', 'token', 'isAuthenticated'], // Only persist these fields
};

const persistedAuthReducer = persistReducer(authPersistConfig, authSlice);

export const store = configureStore({
  reducer: {
    auth: persistedAuthReducer,
    savings: savingsSlice,
    loans: loansSlice,
    shares: sharesSlice,
    transactions: transactionsSlice,
    savingsGoals: savingsGoalsSlice,
    wallet: walletSlice,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: [FLUSH, REHYDRATE, PAUSE, PERSIST, PURGE, REGISTER],
      },
    }),
});

export const persistor = persistStore(store);
export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
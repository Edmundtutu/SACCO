import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { isAxiosError } from 'axios';
import { savingsGoalsAPI } from '@/api/savingsGoals';
import type {
  CreateSavingsGoalPayload,
  PaginatedResponse,
  SavingsGoal,
  UpdateSavingsGoalPayload,
} from '@/types/api';

interface SavingsGoalsState {
  goals: SavingsGoal[];
  activeGoal: SavingsGoal | null;
  loading: boolean;
  error: string | null;
  meta?: PaginatedResponse<SavingsGoal>['meta'];
  links?: PaginatedResponse<SavingsGoal>['links'];
}

const initialState: SavingsGoalsState = {
  goals: [],
  activeGoal: null,
  loading: false,
  error: null,
  meta: undefined,
  links: undefined,
};

const getErrorMessage = (error: unknown): string => {
  if (isAxiosError(error)) {
    return (
      (error.response?.data as { message?: string })?.message ||
      error.message ||
      'Something went wrong while communicating with the server.'
    );
  }

  return error instanceof Error ? error.message : 'Unexpected error occurred.';
};

export const fetchSavingsGoals = createAsyncThunk(
  'savingsGoals/fetchGoals',
  async (
    params: { page?: number; perPage?: number } | undefined,
    thunkAPI
  ): Promise<PaginatedResponse<SavingsGoal>> => {
    try {
      return await savingsGoalsAPI.list(params);
    } catch (error) {
      return thunkAPI.rejectWithValue(getErrorMessage(error));
    }
  }
);

export const createSavingsGoal = createAsyncThunk(
  'savingsGoals/createGoal',
  async (payload: CreateSavingsGoalPayload, thunkAPI): Promise<SavingsGoal> => {
    try {
      const response = await savingsGoalsAPI.create(payload);
      return response.data;
    } catch (error) {
      return thunkAPI.rejectWithValue(getErrorMessage(error));
    }
  }
);

export const updateSavingsGoal = createAsyncThunk(
  'savingsGoals/updateGoal',
  async (
    { goalId, updates }: { goalId: number; updates: UpdateSavingsGoalPayload },
    thunkAPI
  ): Promise<SavingsGoal> => {
    try {
      const response = await savingsGoalsAPI.update(goalId, updates);
      return response.data;
    } catch (error) {
      return thunkAPI.rejectWithValue(getErrorMessage(error));
    }
  }
);

export const deleteSavingsGoal = createAsyncThunk(
  'savingsGoals/deleteGoal',
  async (goalId: number, thunkAPI): Promise<number> => {
    try {
      await savingsGoalsAPI.remove(goalId);
      return goalId;
    } catch (error) {
      return thunkAPI.rejectWithValue(getErrorMessage(error));
    }
  }
);

const savingsGoalsSlice = createSlice({
  name: 'savingsGoals',
  initialState,
  reducers: {
    setActiveGoal: (state, action: PayloadAction<SavingsGoal | null>) => {
      state.activeGoal = action.payload;
    },
    clearSavingsGoalsError: (state) => {
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchSavingsGoals.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchSavingsGoals.fulfilled, (state, action) => {
        state.loading = false;
        state.goals = action.payload.data ?? [];
        state.meta = action.payload.meta;
        state.links = action.payload.links;
        state.activeGoal =
          state.goals.find((goal) => goal.status === 'active') ??
          state.goals[0] ??
          null;
      })
      .addCase(fetchSavingsGoals.rejected, (state, action) => {
        state.loading = false;
        state.error = (action.payload as string) || action.error.message || 'Failed to fetch savings goals.';
      })
      .addCase(createSavingsGoal.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(createSavingsGoal.fulfilled, (state, action) => {
        state.loading = false;
        state.goals = [action.payload, ...state.goals];
        if (!state.activeGoal || action.payload.status === 'active') {
          state.activeGoal = action.payload;
        }
        if (state.meta) {
          state.meta.total = state.goals.length;
        }
      })
      .addCase(createSavingsGoal.rejected, (state, action) => {
        state.loading = false;
        state.error = (action.payload as string) || action.error.message || 'Failed to create savings goal.';
      })
      .addCase(updateSavingsGoal.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(updateSavingsGoal.fulfilled, (state, action) => {
        state.loading = false;
        const index = state.goals.findIndex((goal) => goal.id === action.payload.id);
        if (index !== -1) {
          state.goals[index] = action.payload;
        }
        if (state.activeGoal && state.activeGoal.id === action.payload.id) {
          state.activeGoal = action.payload;
        }
      })
      .addCase(updateSavingsGoal.rejected, (state, action) => {
        state.loading = false;
        state.error = (action.payload as string) || action.error.message || 'Failed to update savings goal.';
      })
      .addCase(deleteSavingsGoal.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(deleteSavingsGoal.fulfilled, (state, action) => {
        state.loading = false;
        state.goals = state.goals.filter((goal) => goal.id !== action.payload);
        if (state.activeGoal?.id === action.payload) {
          state.activeGoal = state.goals.find((goal) => goal.status === 'active') ?? state.goals[0] ?? null;
        }
        if (state.meta) {
          state.meta.total = state.goals.length;
        }
      })
      .addCase(deleteSavingsGoal.rejected, (state, action) => {
        state.loading = false;
        state.error = (action.payload as string) || action.error.message || 'Failed to delete savings goal.';
      });
  },
});

// Alias for legacy callers still importing the old thunk name.
export const updateCurrentAmount = updateSavingsGoal;

export const { setActiveGoal, clearSavingsGoalsError } = savingsGoalsSlice.actions;
export default savingsGoalsSlice.reducer;

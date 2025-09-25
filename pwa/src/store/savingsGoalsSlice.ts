import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';

interface SavingsGoal {
  id: number;
  member_id: number;
  title: string;
  description?: string;
  target_amount: number;
  current_amount: number;
  target_date?: string;
  status: 'active' | 'completed' | 'paused';
  created_at: string;
  updated_at: string;
}

interface SavingsGoalsState {
  goals: SavingsGoal[];
  activeGoal: SavingsGoal | null;
  loading: boolean;
  error: string | null;
}

const initialState: SavingsGoalsState = {
  goals: [],
  activeGoal: null,
  loading: false,
  error: null,
};

// Async thunks for savings goals operations
export const fetchSavingsGoals = createAsyncThunk(
  'savingsGoals/fetchGoals',
  async (memberId: number) => {
    // For now, we'll use localStorage to store goals
    // In a real app, this would be an API call
    const storedGoals = localStorage.getItem(`savings_goals_${memberId}`);
    return storedGoals ? JSON.parse(storedGoals) : [];
  }
);

export const createSavingsGoal = createAsyncThunk(
  'savingsGoals/createGoal',
  async (goalData: Omit<SavingsGoal, 'id' | 'created_at' | 'updated_at' | 'current_amount'>) => {
    const newGoal: SavingsGoal = {
      ...goalData,
      id: Date.now(), // Simple ID generation
      current_amount: 0,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    };
    
    // Store in localStorage
    const memberId = goalData.member_id;
    const storedGoals = localStorage.getItem(`savings_goals_${memberId}`);
    const goals = storedGoals ? JSON.parse(storedGoals) : [];
    goals.push(newGoal);
    localStorage.setItem(`savings_goals_${memberId}`, JSON.stringify(goals));
    
    return newGoal;
  }
);

export const updateSavingsGoal = createAsyncThunk(
  'savingsGoals/updateGoal',
  async ({ goalId, updates, memberId }: { goalId: number; updates: Partial<SavingsGoal>; memberId: number }) => {
    const storedGoals = localStorage.getItem(`savings_goals_${memberId}`);
    const goals = storedGoals ? JSON.parse(storedGoals) : [];
    const goalIndex = goals.findIndex((goal: SavingsGoal) => goal.id === goalId);
    
    if (goalIndex !== -1) {
      goals[goalIndex] = {
        ...goals[goalIndex],
        ...updates,
        updated_at: new Date().toISOString(),
      };
      localStorage.setItem(`savings_goals_${memberId}`, JSON.stringify(goals));
      return goals[goalIndex];
    }
    
    throw new Error('Goal not found');
  }
);

export const deleteSavingsGoal = createAsyncThunk(
  'savingsGoals/deleteGoal',
  async ({ goalId, memberId }: { goalId: number; memberId: number }) => {
    const storedGoals = localStorage.getItem(`savings_goals_${memberId}`);
    const goals = storedGoals ? JSON.parse(storedGoals) : [];
    const filteredGoals = goals.filter((goal: SavingsGoal) => goal.id !== goalId);
    localStorage.setItem(`savings_goals_${memberId}`, JSON.stringify(filteredGoals));
    return goalId;
  }
);

const savingsGoalsSlice = createSlice({
  name: 'savingsGoals',
  initialState,
  reducers: {
    setActiveGoal: (state, action) => {
      state.activeGoal = action.payload;
    },
    updateCurrentAmount: (state, action) => {
      const { goalId, amount } = action.payload;
      const goal = state.goals.find(g => g.id === goalId);
      if (goal) {
        goal.current_amount = amount;
        goal.updated_at = new Date().toISOString();
      }
      if (state.activeGoal && state.activeGoal.id === goalId) {
        state.activeGoal.current_amount = amount;
        state.activeGoal.updated_at = new Date().toISOString();
      }
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch Goals
      .addCase(fetchSavingsGoals.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchSavingsGoals.fulfilled, (state, action) => {
        state.loading = false;
        state.goals = action.payload;
        // Set the first active goal as the default active goal
        state.activeGoal = action.payload.find((goal: SavingsGoal) => goal.status === 'active') || null;
      })
      .addCase(fetchSavingsGoals.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch savings goals';
      })
      
      // Create Goal
      .addCase(createSavingsGoal.fulfilled, (state, action) => {
        state.goals.push(action.payload);
        if (!state.activeGoal) {
          state.activeGoal = action.payload;
        }
      })
      
      // Update Goal
      .addCase(updateSavingsGoal.fulfilled, (state, action) => {
        const index = state.goals.findIndex(goal => goal.id === action.payload.id);
        if (index !== -1) {
          state.goals[index] = action.payload;
        }
        if (state.activeGoal && state.activeGoal.id === action.payload.id) {
          state.activeGoal = action.payload;
        }
      })
      
      // Delete Goal
      .addCase(deleteSavingsGoal.fulfilled, (state, action) => {
        state.goals = state.goals.filter(goal => goal.id !== action.payload);
        if (state.activeGoal && state.activeGoal.id === action.payload) {
          state.activeGoal = state.goals.find(goal => goal.status === 'active') || null;
        }
      });
  },
});

export const { setActiveGoal, updateCurrentAmount } = savingsGoalsSlice.actions;
export default savingsGoalsSlice.reducer;

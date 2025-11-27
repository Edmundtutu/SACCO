## Plan: Deliver Member Savings Goals

Design end-to-end savings goals: add Laravel persistence and REST endpoints, enable authenticated CRUD with progress computation and lagging nudges, then refactor the PWA slice/components to consume the API, manage state, and surface actionable feedback. Document contracts and add automated coverage to keep reliability high.

### Steps
1. Define Eloquent model, migration, factory in `APP/app/Models/SavingsGoal.php` and `database/migrations/*`.
2. Add policy, form requests, controller, routes in `APP/app/Http/Controllers/API/SavingsGoalController.php`.
3. Wire service layer for nudges and progress math in `APP/app/Services/SavingsGoalService.php`.
4. Update docs and tests in `APP/openapi.yaml`, `tests/Feature/SavingsGoalTest.php`, `tests/Unit/*`.
5. Replace localStorage slice with RTK async thunks in `pwa/src/store/savingsGoalsSlice.ts`.
6. Refresh UI flows with loading/errors and nudges in `pwa/src/components/savings/*` and `pages/Savings.tsx`.

### Further Considerations
1. Clarify nudge cadence and channel → choose Option C (in-app badges + email) and specify cadence.

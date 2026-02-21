<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show the admin login form
     */
    public function showLoginForm()
    {
        if (Auth::check() && $this->isAdminUser(Auth::user())) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle admin login — supports multi-tenant disambiguation.
     *
     * If the same email resolves to admin rows in more than one SACCO,
     * the user is redirected to the SACCO selector page before being
     * authenticated. Credentials are held in the session temporarily
     * (hashed so the cleartext password is not persisted long-term).
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $remember = $request->boolean('remember');

        // Find ALL admin-eligible rows matching this email across all tenants.
        $candidates = User::withoutGlobalScopes()
            ->where('email', $request->email)
            ->where(function ($q) {
                $q->where('is_super_admin', true)
                  ->orWhereIn('role', ['admin', 'staff_level_1', 'staff_level_2', 'staff_level_3', 'super_admin']);
            })
            ->with('tenant')
            ->get()
            ->filter(fn(User $u) => Hash::check($request->password, $u->password));

        if ($candidates->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Invalid email or password, or you do not have admin access.')
                ->withInput($request->except('password'));
        }

        // Single match — log straight in.
        if ($candidates->count() === 1) {
            return $this->completeLogin($candidates->first(), $remember, $request);
        }

        // Multiple matches — store candidates in session, redirect to SACCO picker.
        $session_data = $candidates->map(fn(User $u) => [
            'user_id'    => $u->id,
            'tenant_id'  => $u->tenant_id,
            'sacco_name' => $u->tenant?->sacco_name ?? 'Unknown SACCO',
            'sacco_code' => $u->tenant?->sacco_code ?? '—',
            'status'     => $u->tenant?->status ?? 'unknown',
            'logo_url'   => $u->tenant?->getSetting('logo_url'),
            'role'       => $u->role,
        ])->values()->all();

        $request->session()->put('admin_sacco_candidates', $session_data);
        $request->session()->put('admin_sacco_remember', $remember);

        return redirect()->route('admin.select-sacco');
    }

    /**
     * Show the SACCO selection page (used only when an admin belongs to multiple SACCOs).
     */
    public function showSaccoSelect(Request $request)
    {
        $candidates = $request->session()->get('admin_sacco_candidates');

        if (empty($candidates)) {
            return redirect()->route('admin.login')
                ->with('error', 'Session expired. Please log in again.');
        }

        return view('admin.auth.select-sacco', ['candidates' => $candidates]);
    }

    /**
     * Complete login after SACCO selection.
     */
    public function completeSaccoSelect(Request $request)
    {
        $candidates = $request->session()->get('admin_sacco_candidates');

        if (empty($candidates)) {
            return redirect()->route('admin.login')
                ->with('error', 'Session expired. Please log in again.');
        }

        $request->validate(['user_id' => 'required|integer']);

        $validIds = array_column($candidates, 'user_id');
        if (!in_array((int) $request->user_id, $validIds, true)) {
            return redirect()->route('admin.select-sacco')
                ->with('error', 'Invalid selection. Please try again.');
        }

        $user = User::withoutGlobalScopes()->findOrFail($request->user_id);
        $remember = $request->session()->get('admin_sacco_remember', false);

        $request->session()->forget(['admin_sacco_candidates', 'admin_sacco_remember']);

        return $this->completeLogin($user, $remember, $request);
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    private function completeLogin(User $user, bool $remember, Request $request)
    {
        if (!$this->isAdminUser($user)) {
            return redirect()->route('admin.login')
                ->with('error', 'You do not have permission to access the admin panel.');
        }

        Auth::loginUsingId($user->id, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    private function isAdminUser($user): bool
    {
        return $user->isStaff() || $user->isSuperAdmin();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    /** Roles that count as "admin/staff" and are manageable through this controller. */
    private const STAFF_ROLES = ['admin', 'staff_level_1', 'staff_level_2', 'staff_level_3'];

    /** Ensure the currently authenticated user is allowed to manage staff. */
    private function authorise(): void
    {
        $user = auth()->user();
        abort_unless($user->role === 'admin' || $user->isSuperAdmin(), 403, 'Only SACCO admins can manage staff.');
    }

    /**
     * List all staff members in the current tenant.
     */
    public function index(): View
    {
        $staff = User::whereIn('role', self::STAFF_ROLES)
            ->orderByRaw("FIELD(role, 'admin', 'staff_level_1', 'staff_level_2', 'staff_level_3')")
            ->orderBy('name')
            ->get();

        return view('admin.staff.index', compact('staff'));
    }

    /**
     * Show the form to create a brand-new admin/staff user.
     */
    public function create(): View
    {
        $this->authorise();

        return view('admin.staff.create');
    }

    /**
     * Persist a new admin/staff user for this tenant.
     * BelongsToTenant trait will auto-assign tenant_id.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorise();

        $data = $request->validate([
            'name'     => 'required|string|max:191',
            'email'    => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => ['required', Rule::in(self::STAFF_ROLES)],
        ]);

        // Non-admins cannot create another admin — they can only create staff_level_*
        if ($data['role'] === 'admin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can create admin accounts.');
        }

        $data['password'] = Hash::make($data['password']);
        $data['status']   = 'active';

        User::create($data); // tenant_id injected by BelongsToTenant::creating()

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    /**
     * Show the edit form for an existing staff member.
     */
    public function edit(User $user): View
    {
        $this->authorise();
        $this->assertSameTenant($user);

        return view('admin.staff.edit', compact('user'));
    }

    /**
     * Update name, email, role, status, or password for a staff member.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorise();
        $this->assertSameTenant($user);

        $data = $request->validate([
            'name'     => 'required|string|max:191',
            'email'    => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => ['required', Rule::in(self::STAFF_ROLES)],
            'status'   => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);

        if ($data['role'] === 'admin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can assign the admin role.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.staff.edit', $user)
            ->with('success', 'Staff member updated.');
    }

    /**
     * Promote a member to a staff role.
     * Expects: role (staff_level_1 / staff_level_2 / staff_level_3 / admin)
     */
    public function promote(Request $request, User $user): RedirectResponse
    {
        $this->authorise();
        $this->assertSameTenant($user);

        abort_unless($user->role === 'member', 422, 'Only members can be promoted.');

        $data = $request->validate([
            'role' => ['required', Rule::in(self::STAFF_ROLES)],
        ]);

        if ($data['role'] === 'admin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can promote to admin.');
        }

        $user->update(['role' => $data['role']]);

        return back()->with('success', "{$user->name} promoted to {$data['role']}.");
    }

    /**
     * Demote a staff member back to member role.
     * Blocked if this is the last active admin in the tenant.
     */
    public function demote(User $user): RedirectResponse
    {
        $this->authorise();
        $this->assertSameTenant($user);

        abort_unless(in_array($user->role, self::STAFF_ROLES), 422, 'User is not a staff member.');

        // Guard: cannot demote the last admin
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            abort_if($adminCount <= 1, 422, 'Cannot demote the last admin of this SACCO.');
        }

        $user->update(['role' => 'member']);

        return back()->with('success', "{$user->name} has been demoted to member.");
    }

    /**
     * Abort if the target user belongs to a different tenant.
     */
    private function assertSameTenant(User $user): void
    {
        abort_unless($user->tenant_id === auth()->user()->tenant_id, 403, 'Access denied.');
    }
}

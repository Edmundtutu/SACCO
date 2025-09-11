<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Handle admin login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Check if user has admin privileges
            if ($this->isAdminUser($user)) {
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'));
            } else {
                Auth::logout();
                return redirect()->back()
                    ->with('error', 'You do not have permission to access the admin panel.');
            }
        }

        return redirect()->back()
            ->with('error', 'Invalid email or password.')
            ->withInput($request->except('password'));
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

    /**
     * Check if user has admin privileges
     */
    private function isAdminUser($user)
    {
        return $user->isStaff();
    }
}

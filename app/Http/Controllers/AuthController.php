<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ]);

        // Trim whitespace from inputs
        $data['email'] = trim($data['email']);
        $data['password'] = trim($data['password']);

        // Check for empty inputs after trimming
        if (empty($data['email'])) {
            return back()->withErrors(['email' => 'Email address cannot be empty.'])->withInput();
        }

        if (empty($data['password'])) {
            return back()->withErrors(['password' => 'Password cannot be empty.'])->withInput();
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid email address or password.'])->withInput();
        }

        Auth::login($user, (bool)($data['remember'] ?? false));
        $request->session()->regenerate();
        // If not an admin, send to home with a notice; admins go to dashboard
        if (!($user->is_admin ?? false)) {
            return redirect()->route('home')->with('status', 'Signed in. Admin access required for dashboard.');
        }
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}

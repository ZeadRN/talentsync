<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($data, $r->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match.'])->onlyInput('email');
        }$r->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function register(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users', 'password' => ['required', 'confirmed', Password::min(8)], 'role' => 'required|in:Employer,Job Seeker']);
        $user = DB::transaction(function () use ($data) {
            $user = User::create(collect($data)->except('role')->all());
            $user->assignRole($data['role']);

            return $user;
        });
        Auth::login($user);
        $r->session()->regenerate();

        return redirect('/dashboard');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect('/');
    }
}

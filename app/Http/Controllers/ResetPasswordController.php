<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /**
     * Display the reset password form.
     */
    public function show(Request $request)
    {
        // Retrieve the user ID from session; ensure you have stored it on login.
        $uid = $request->session()->get('uid');
        
        // Optionally, you can verify that $uid exists.
        if (!$uid) {
            return redirect()->route('login')->withErrors('Your session has expired. Please log in again.');
        }
        
        return view('auth.reset_password', compact('uid'));
    }

    /**
     * Update the password for the user.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'uid'                   => 'required|exists:users,id',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        $user = User::find($validated['uid']);
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('dashboard.index')->with('success', 'Password reset successfully!');
    }
}

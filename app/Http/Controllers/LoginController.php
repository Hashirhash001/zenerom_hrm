<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserAccessPrivilege;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('auth.login'); // This points to resources/views/auth/login.blade.php
    }

    /**
     * Handle the login form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function attempt(Request $request)
    {
        $validatedData = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            // 'mode'     => 'required|string'
        ]);

        $credentials = [
            'email'    => $validatedData['email'],
            'password' => $validatedData['password'],
        ];

        // $mode = $validatedData['mode'];


        // Attempt authentication using the provided credentials.
        if (Auth::attempt($credentials)) {
            // Regenerate session to prevent session fixation.
            $request->session()->regenerate();

            // Retrieve the authenticated user.
            $user = Auth::user();

            // Store basic user id and name in session.
            session()->put('uid', $user->id);
            session()->put('uname', $user->name);
            session()->put('rid', $user->role_id);

            // Optionally, store additional data from related tables.
            session()->put('employee_id', optional($user->employee)->employee_id);
            session()->put('image', optional($user->employee)->image);
            session()->put('role', optional($user->role)->name);
            session()->put('department', optional($user->department)->name);

            // Capture the user's IP address.
            $ipAddress = $request->ip();
             $privileges = UserAccessPrivilege::where('user_id', $user->id)
                        ->get()
                        ->keyBy('menu_item_id');
            session()->put('user_privileges', $privileges);

            // Redirect based on the user's role.
            if ($user->role_id == 1) {
                return redirect()->route('dashboard.index');
            } else if ($user->role_id == 2) {
                return redirect()->route('dashboard.techhead');
            } else if ($user->role_id == 3) {
                return redirect()->route('dashboard.teamlead');
            } else if ($user->role_id == 4) {
                return redirect()->route('dashboard.staff');
            } else if ($user->role_id == 5) {
                return redirect()->route('dashboard.projectmanager');
            } else if ($user->role_id == 6) {
                return redirect()->route('dashboard.interns');
            } else if ($user->role_id == 7) {
                return redirect()->route('dashboard.hr');
            } else if ($user->role_id == 12) {
                return redirect()->route('dashboard.junior');
            } else if ($user->role_id == 13) {
                return redirect()->route('dashboard.trainee');
            }
        }

        // If authentication fails, return back with an error.
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.'
        ]);
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();

        // Standard logout procedure.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

}

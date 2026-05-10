<?php

namespace App\Http\Controllers;

use App\Models\Login;
use App\Models\User;
use App\Models\Exchange;
use App\Models\BankUser;
use Illuminate\Http\Request;
use Hash;
use Auth;
class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $exchangeRecords = Exchange::all();
            return view("auth.login", compact('exchangeRecords'));
        } catch (\Exception $e) {
            return response("Error loading login page", 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required',
            'role' => 'required',
            'exchange' => 'nullable|required_if:role,exchange',
        ]);

        try {
            $user = User::where('name', $request->name)->first();
            
            if (!$user || $user->status != 'active') {
                return redirect()->back()->withErrors(['error' => 'You are not authorized or user not found.']);
            }

            if (Auth::attempt($request->only('name', 'password'))) {
                $request->session()->regenerate();
                $user = Auth::user();
        
                switch ($user->role) {
                    case 'admin':
                        return redirect()->route('admin.dashboard');
                    case 'exchange':
                        $bankUser = BankUser::where('user_id', $user->id)->first();
                        session(['bankUser' => $bankUser]);
                        return redirect()->route('exchange.dashboard');
                    case 'assistant':
                        return redirect()->route('assistant.dashboard');
                    default:
                        Auth::logout(); 
                        return back()->withErrors(['name' => 'Invalid role assigned.']);
                }
            }

            return redirect()->route('auth.login')
                ->withErrors(['name' => 'The provided credentials do not match our records.'])
                ->withInput($request->only('name'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Login error: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }

        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8',
        ]);
        
        try {
            $user = Auth::user();
            
            if ($user->role !== "admin") {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            if (!Hash::check($request->currentPassword, $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 422);
            }

            $user->password = Hash::make($request->newPassword);
            $user->save();
            
            return response()->json(['message' => 'Password updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating password: ' . $e->getMessage()], 500);
        }
    }
    
    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('auth.login');
        } catch (\Exception $e) {
            return redirect()->route('auth.login');
        }
    }

    public function logoutAll(Request $request)
    {
        if (!auth()->check() || Auth::user()->role !== 'admin') {
            return redirect()->route('auth.login');
        }

        try {
            Auth::logout();
            $this->invalidateAllSessions();
            return redirect()->route('auth.login')->with('status', 'All users have been logged out.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error logging out all users.');
        }
    }

    protected function invalidateAllSessions()
    {
        \DB::table('sessions')->truncate();
    }

}

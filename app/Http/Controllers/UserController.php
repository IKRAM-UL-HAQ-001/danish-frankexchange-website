<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Exchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }

        $userRecords = User::with('exchange')
            ->where('role', '!=', 'admin')
            ->orderBy('created_at', 'desc')
            ->get();
        $exchangeRecords = Exchange::all();
        return view("admin.user.list", compact('userRecords', 'exchangeRecords'));
    }

    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'exchange' => 'required|exists:exchanges,id',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'password' => Hash::make($request->password), 
                'exchange_id' => $request->exchange,
                'role' => "exchange",
                'status' => 'active', // Default to active for new users
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User added successfully!',
                'exchange_name' => $user->exchange->name,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error adding user: ' . $e->getMessage()], 500);
        }
    }

    public function userStatus(Request $request)
    {
        try {
            $user = User::findOrFail($request->userId);
            $user->status = $request->status;
            $user->save();
            return redirect()->back()->with('success', 'User status updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }
    
    public function update(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'exchange' => 'nullable|exists:exchanges,id',
            'password' => 'nullable|string|min:8',
        ]);

        try {
            $user = User::findOrFail($request->id);    
            $user->name = $request->name;
            $user->exchange_id = $request->exchange;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();    
            return response()->json(['success' => true, 'message' => 'User updated successfully.', 'exchange_name' => $user->exchange->name]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating user: ' . $e->getMessage()], 500);
        }
    }
    
    public function destroy(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $user = User::findOrFail($request->id);

            // Prevent deleting self or other admins (Optional but recommended)
            if ($user->role === 'admin') {
                 return response()->json(['success' => false, 'message' => 'Admin users cannot be deleted here.'], 403);
            }

            $user->delete();
            return response()->json(['success' => true, 'message' => 'User deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()], 500);
        }
    }
}

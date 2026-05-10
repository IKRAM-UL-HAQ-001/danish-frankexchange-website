<?php

namespace App\Http\Controllers;

use App\Models\ClientBalance;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientBalanceMonthlyListExport;
use App\Exports\ClientBalanceWeeklyListExport;
use Auth;

class ClientBalanceController extends Controller
{
    public function clientBalanceListMonthlyExportExcel(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $user = Auth::user();
            $exchangeId = ($user->role == "admin" || $user->role == "assistant") ? null : $user->exchange_id;
            return Excel::download(new clientBalanceMonthlyListExport($exchangeId), 'MonthlyclientBalanceRecord.xlsx');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }
    
    public function clientBalanceListWeeklyExportExcel(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $user = Auth::user();
            $exchangeId = ($user->role == "admin" || $user->role == "assistant") ? null : $user->exchange_id;
            return Excel::download(new clientBalanceWeeklyListExport($exchangeId), 'WeeklyclientBalanceRecord.xlsx');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }
    
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $clientBalanceRecords = ClientBalance::with(['exchange', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();    
            return view("admin.client_balance.list", compact('clientBalanceRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }
    
    public function indexAssistant()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $clientBalanceRecords = ClientBalance::with(['exchange', 'user'])->get();
            return view("assistant.client_balance.list", compact('clientBalanceRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }
    
    public function exchangeIndex()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $user = auth()->user();
            $clientBalanceRecords = ClientBalance::with(['exchange', 'user'])
                ->where('exchange_id', $user->exchange_id)
                ->where('user_id', $user->id)
                ->get();
            return view("exchange.client_balance.list", compact('clientBalanceRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }
    
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'client_balance' => 'required',
            'remarks' => 'required',
        ]);        

        try {
            $user = auth()->user();
            ClientBalance::create([
                'client_balance' => $validatedData['client_balance'],
                'remarks' => $validatedData['remarks'],
                'exchange_id' => $user->exchange_id,
                'user_id' => $user->id,
            ]);
            return response()->json(['success' => true, 'message' => 'Client Balance saved successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    
    public function update(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'id' => 'required|exists:client_balances,id',
            'client_balance' => 'required',
            'remarks' => 'required',
        ]);

        try {
            $user = Auth::user();
            $query = ClientBalance::where('id', $request->id);
            if ($user->role !== 'admin') {
                $query->where('exchange_id', $user->exchange_id);
            }
            $clientBalance = $query->firstOrFail();

            $clientBalance->client_balance = $request->client_balance;
            $clientBalance->remarks = $request->remarks;
            $clientBalance->save();
            return response()->json(['success' => true, 'message' => 'Client Balance updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Record not found or access denied.'], 404);
        }
    }
    
    public function destroy(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $user = Auth::user();
            $query = ClientBalance::where('id', $request->id);
            if ($user->role !== 'admin') {
                $query->where('exchange_id', $user->exchange_id);
            }
            $clientBalance = $query->firstOrFail();

            $clientBalance->delete();
            return response()->json(['success' => true, 'message' => 'Client Balance deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Record not found or access denied.'], 404);
        }
    }
}

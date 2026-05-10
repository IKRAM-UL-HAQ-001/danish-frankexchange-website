<?php

namespace App\Http\Controllers;

use App\Models\OpenCloseBalance;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OpenCloseBalanceListExport;
use Carbon\Carbon;
use Auth;

class OpenCloseBalanceController extends Controller
{

    public function openCloseBalanceExportExcel(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $user = Auth::user();
            $exchangeId = ($user->role == "admin" || $user->role == "assistant") ? null : $user->exchange_id;
            return Excel::download(new OpenCloseBalanceListExport($exchangeId), 'openingClosingBalanceRecord.xlsx');
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
            $openingClosingBalanceRecords = OpenCloseBalance::orderBy('created_at', 'desc')->get();
            return view('admin.open_close_balance.list', compact('openingClosingBalanceRecords'));
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
            $startOfWeek = Carbon::now()->startOfWeek();
            $exchangeId = Auth::user()->exchange_id;
            $openingClosingBalanceRecords = OpenCloseBalance::where('exchange_id', $exchangeId)
                ->where('created_at', '>=', $startOfWeek)
                ->orderBy('created_at', 'desc')
                ->get();
    
            return view('exchange.open_close_balance.list', compact("openingClosingBalanceRecords"));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }
    
    public function assistantIndex()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $openingClosingBalanceRecords = OpenCloseBalance::orderBy('created_at', 'desc')->get();
            return view('assistant.open_close_balance.list', compact("openingClosingBalanceRecords"));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }
    
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'open_balance' => 'required|numeric',
            'remarks' => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            OpenCloseBalance::create([
                'open_balance' => $validatedData['open_balance'],
                'remarks' => $validatedData['remarks'],
                'exchange_id' => $user->exchange_id,
                'user_id' => $user->id,
            ]);
    
            return response()->json(['success' => true, 'message' => 'Opening Closing Balance saved successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    
    public function destroy(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $user = Auth::user();
            $query = OpenCloseBalance::where('id', $request->id);

            if ($user->role !== 'admin' && $user->role !== 'assistant') {
                $query->where('exchange_id', $user->exchange_id);
            }

            $openCloseBalance = $query->firstOrFail();
            $openCloseBalance->delete();
            return response()->json(['success' => true, 'message' => 'Opening closing balance deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Record not found or access denied.'], 404);
        }
    }
    
}

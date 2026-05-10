<?php

namespace App\Http\Controllers;

use App\Models\OwnerProfit;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OwnerProfitListExport;
use Auth;
class OwnerProfitController extends Controller
{
    public function ownerProfitListExportExcel(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $user = Auth::user();
            $exchangeId = ($user->role == "admin" || $user->role == "assistant") ? null : $user->exchange_id;
            return Excel::download(new OwnerProfitListExport($exchangeId), 'ownerProfitRecord.xlsx');
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
            $ownerProfitRecords = OwnerProfit::orderBy('created_at', 'desc')->get();
            return view('admin.owner_profit.list', compact('ownerProfitRecords'));
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
            $ownerProfitRecords = OwnerProfit::orderBy('created_at', 'desc')->get();
            return view('assistant.owner_profit.list', compact('ownerProfitRecords'));
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
            'cash_amount' => 'required|numeric',
            'remarks' => 'required|string|max:255',
        ]);

        try {
            $user = Auth::user();
            OwnerProfit::create([
                'cash_amount' => $validatedData['cash_amount'],
                'remarks' => $validatedData['remarks'],
                'exchange_id' => $user->exchange_id,
                'user_id' => $user->id,
            ]);

            return response()->json(['success' => true, 'message' => 'Transaction successfully added!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error adding profit: ' . $e->getMessage()], 500);
        }
    }
    
    public function destroy(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $user = Auth::user();
            $query = OwnerProfit::where('id', $request->id);

            if ($user->role !== 'admin' && $user->role !== 'assistant') {
                $query->where('exchange_id', $user->exchange_id);
            }

            $ownerProfit = $query->firstOrFail();
            $ownerProfit->delete();
            return response()->json(['success' => true, 'message' => 'Owner Profit deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Record not found or access denied.'], 404);
        }
    }
    
    public function exchangeIndex()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $user = Auth::user();
            $ownerProfitRecords = OwnerProfit::where('exchange_id', $user->exchange_id)
                ->where('user_id', $user->id)
                ->get();
            return view("exchange.owner_profit.list", compact('ownerProfitRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }
    
}

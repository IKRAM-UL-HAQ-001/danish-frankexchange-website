<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankEntry;
use Illuminate\Http\Request;
Use App\Exports\BankListExport;
use Maatwebsite\Excel\Facades\Excel;
use Auth;
class BankController extends Controller
{
    public function bankExportExcel()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            return Excel::download(new BankListExport, 'BankList.xlsx');
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
            $bankRecords = Bank::all();
            $bank_names = $bankRecords->pluck('name');

            $bankBalances = BankEntry::selectRaw('bank_name, SUM(CASE WHEN cash_type = "add" THEN cash_amount WHEN cash_type = "minus" THEN -cash_amount END) as balance')
                ->whereIn('bank_name', $bank_names)
                ->groupBy('bank_name')
                ->pluck('balance', 'bank_name');

            $finalBalances = $bank_names->mapWithKeys(function ($name) use ($bankBalances) {
                return [$name => $bankBalances->get($name, 0)];
            });

            return view("admin.bank.list", compact('bankRecords', 'finalBalances'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('BankController@index error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate(['name' => 'required|string|max:255']);

        try {
            Bank::create(['name' => $request->name]);
            return response()->json(['message' => 'Bank added successfully!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding bank: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $bank = Bank::findOrFail($request->id);
            BankEntry::where('bank_name', $bank->name)->delete();
            $bank->delete();
            return response()->json(['success' => true, 'message' => 'Bank deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Bank not found or deletion failed.'], 404);
        }
    }

    public function freezBankIndex(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $bankEntryRecords = BankEntry::where('status', "freez")->get();
            $bankRecords = Bank::all();
            return view('admin.bank_freez.list', compact('bankEntryRecords', 'bankRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }    
    }

    public function assistantFreezBankIndex(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $bankEntryRecords = BankEntry::where('status', "freez")->get();
            $bankRecords = Bank::all();
            return view('assistant.bank_freez.list', compact('bankEntryRecords', 'bankRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }    
    }

    public function freezDelete(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $user = Auth::user();
            $query = BankEntry::where('id', $request->id)->where('status', 'freez');

            if ($user->role !== 'admin' && $user->role !== 'assistant') {
                $query->where('exchange_id', $user->exchange_id);
            }

            $bank = $query->firstOrFail();
            $bank->delete();
            return redirect()->back()->with('success', 'Bank entry deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Bank entry not found or access denied.');
        }
    }

}

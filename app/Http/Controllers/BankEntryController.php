<?php

namespace App\Http\Controllers;

use App\Models\BankEntry;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;
class BankEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $exchangeId = Auth::user()->exchange_id;
            $bankEntryRecords = BankEntry::where('exchange_id', $exchangeId)->get();
            $bankRecords = Bank::all();
            
            return view('exchange.bank.list', compact('bankEntryRecords', 'bankRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading bank entries: ' . $e->getMessage());
        }
    }

    public function create()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $bankRecords = Bank::all();
            return view("exchange.bank.list", compact('bankRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading create page: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'account_number' => [
                Rule::requiredIf($request->input('status') !== 'freez'),
                'string',
                'max:255',
            ],
            'cash_type' => 'required|string|max:255',
            'cash_amount' => 'required|numeric',
            'remarks' => 'required|string',
            'status' => 'nullable|string',
            'bank_name'=> 'required',       
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validatedData) {
                $bankExists = Bank::where('name', $request->bank_name)->firstOrFail();
                
                if ($request->status == 'freez') {
                    $bankExists->status = 'freez';
                    $bankExists->save();
                }

                $user = Auth::user();
                $bankEntry = BankEntry::create([
                    'account_number' => $validatedData['account_number'] ?? 0,
                    'bank_name' =>  $validatedData['bank_name'],
                    'cash_amount' => (int) $validatedData['cash_amount'],
                    'cash_type' => $validatedData['cash_type'],
                    'remarks' => $validatedData['remarks'],
                    'status' => $validatedData['status'],
                    'exchange_id' => $user->exchange_id,
                    'user_id' => $user->id,
                ]);

                return response()->json(['success' => true, 'message' => 'Bank Entry Data saved successfully!'], 200);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error saving Bank Entry: ' . $e->getMessage()], 500);
        }
    }

    public function getBankBalance(Request $request) 
    {
        try {
            $request->validate(['bank_name' => 'required|string']);

            $sumBalance = BankEntry::where('bank_name', $request->bank_name)
                ->selectRaw('SUM(CASE WHEN cash_type = "add" THEN cash_amount WHEN cash_type = "minus" THEN -cash_amount END) as balance')
                ->value('balance');

            return response()->json(['balance' => $sumBalance ?? 0]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exchangeFreezBank(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $exchangeId = Auth::user()->exchange_id;
            $userId = Auth::user()->id;
            $bankEntryRecords = BankEntry::where('exchange_id', $exchangeId)
                ->where('user_id', $userId)
                ->where('status', "freez")
                ->get();
            $bankRecords = Bank::whereNull('status')->get();
            return view('exchange.bank_freez.list', compact('bankEntryRecords', 'bankRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading frozen banks: ' . $e->getMessage());
        }
    }

    public function unFreez(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $bankEntry = BankEntry::findOrFail($request->id);
                $bank = $bankEntry->bank_name;
                $bankRec = Bank::where('name', $bank)->first();
                
                if ($bankRec) {
                    $bankRec->status = null;
                    $bankRec->save();
                }

                $bankEntry->delete();
                return redirect()->back()->with('success', 'Bank un-frozen successfully!');
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error un-freezing bank: ' . $e->getMessage());
        }
    }
}

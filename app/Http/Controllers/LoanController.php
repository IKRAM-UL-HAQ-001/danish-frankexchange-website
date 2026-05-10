<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Exchange;
use Illuminate\Http\Request;
Use App\Exports\LoanListExport;
use Maatwebsite\Excel\Facades\Excel;
use Auth;

class LoanController extends Controller
{
    public function loanExportExcel(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
    
        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $user = auth()->user();
            
            $exchangeId = ($user->role === "admin" || $user->role === "assistant") ? null : $user->exchange_id;
        
            if (!$startDate || !$endDate) {
                return redirect()->back()->with('error', 'Start date and end date are required.');
            }
        
            return Excel::download(new LoanListExport($startDate, $endDate, $exchangeId), 'loanRecord.xlsx');
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
            $exchangeRecords = Exchange::all();
            $loanRecords = Loan::with(['exchange', 'user','receiverExchange'])
                ->orderBy('created_at', 'desc')
                ->get();
            return view('admin.loan.list', compact('loanRecords','exchangeRecords'));
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
            $exchangeRecords = Exchange::all();
            $loanRecords = Loan::with(['exchange', 'user','receiverExchange'])
                ->orderBy('created_at', 'desc')
                ->get();
            return view('assistant.loan.list', compact('loanRecords','exchangeRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }
    public function exchangeIndex(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        } 
    
        try {
            $user = auth()->user();
            $exchangeId = $user->exchange_id; 
            
            $loanRecords = Loan::with('receiverExchange')
            ->where(function ($query) use ($exchangeId) {
                $query->where('exchange_id', $exchangeId)
                      ->orWhere('receiver_id', $exchangeId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            $exchangeRecords = Exchange::all();
        
            return view('exchange.loan.list', compact('loanRecords', 'exchangeRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }
    


    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    
        $validatedData = $request->validate([
            'receiver_exchange' => 'required|numeric',
            'cash_type' => 'required|string',
            'cash_amount' => 'required|string',
            'remarks' => 'nullable|string',
        ]);
    
        try {
            $user = auth()->user();
            Loan::create([
                'receiver_id' => $validatedData['receiver_exchange'],
                'cash_type' => $validatedData['cash_type'],
                'cash_amount' => $validatedData['cash_amount'],
                'remarks' => $validatedData['remarks'],
                'exchange_id' => $user->exchange_id,
                'user_id' => $user->id,
            ]);
    
            return response()->json(['success' => true, 'message' => 'Loan added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error adding Loan: ' . $e->getMessage()]);
        }
    }
    

    public function show(Loan $loan)
    {
        //
    }

    public function edit(Loan $loan)
    {
        //
    }

    public function update(Request $request, Loan $loan)
    {
        //
    }

    public function destroy(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $user = Auth::user();
            $query = Loan::where('id', $request->id);

            if ($user->role !== 'admin' && $user->role !== 'assistant') {
                $query->where('exchange_id', $user->exchange_id);
            }

            $loan = $query->firstOrFail();
            $loan->delete();
            return response()->json(['success' => true, 'message' => 'Loan deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Loan not found or access denied.'], 404);
        }
    }
}

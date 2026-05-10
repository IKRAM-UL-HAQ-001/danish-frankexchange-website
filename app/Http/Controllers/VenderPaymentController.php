<?php

namespace App\Http\Controllers;

use App\Models\VenderPayment;
use App\Models\Exchange;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VenderPaymentListExport;
use Auth;
use Carbon\Carbon;
class VenderPaymentController extends Controller
{

    public function venderPaymentExportExcel(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $exchangeId = $request->exchange_id;
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            return Excel::download(new VenderPaymentListExport($startDate, $endDate, $exchangeId), 'venderPaymentRecord.xlsx');
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
            $venderPaymentRecords = VenderPayment::orderBy('created_at', 'desc')->get();
            $exchangeRecords = Exchange::all();
            return view('admin.vender_payment.list', compact('venderPaymentRecords', 'exchangeRecords'));
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
            $exchangeId = auth()->user()->exchange_id; 
            $userId = auth()->user()->id;
            $venderPaymentRecords = VenderPayment::with(['exchange', 'user'])
                ->where('exchange_id', $exchangeId)
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            return view('exchange.vender_payment.list', compact('venderPaymentRecords'));
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
            $venderPaymentRecords = VenderPayment::orderBy('created_at', 'desc')->get();
            $exchangeRecords = Exchange::all();
            return view('assistant.vender_payment.list', compact('venderPaymentRecords', 'exchangeRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }
    
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $validatedData = $request->validate([
            'paid_amount' => 'required|numeric',
            'remaining_amount' => 'required|numeric',
            'payment_type' => 'required|string',
            'exchange_id' => 'nullable',
            'remarks' => 'required|string|max:255',
        ]);

        try {
            $user = auth()->user();
            if ($user->role != "admin") {
                $exchange_id = $user->exchange_id; 
                $user_id = $user->id;
            } else {
                $exchange_id = $request->exchange_id;
                $user_id = null;
            }

            VenderPayment::create([
                'paid_amount' => $validatedData['paid_amount'],
                'remaining_amount' => $validatedData['remaining_amount'],
                'payment_type' => $validatedData['payment_type'],
                'exchange_id' => $exchange_id,
                'user_id' => $user_id,
                'remarks' => $validatedData['remarks'],
            ]);
    
            return redirect()->back()->with('success', 'Vender Payment added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error adding vender payment: ' . $e->getMessage());
        }
    }
    
    public function destroy(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $user = Auth::user();
            $query = VenderPayment::where('id', $request->id);

            if ($user->role !== 'admin' && $user->role !== 'assistant') {
                $query->where('exchange_id', $user->exchange_id);
            }

            $venderPayment = $query->firstOrFail();
            $venderPayment->delete();
            return response()->json(['success' => true, 'message' => 'Vender payment deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Record not found or access denied.'], 404);
        }
    }
    
}

<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
Use App\Exports\CustomerListExport;
use Maatwebsite\Excel\Facades\Excel;
use Auth;
class CustomerController extends Controller
{

    public function customerExportExcel(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $exchangeId = (Auth::user()->role == "admin" || Auth::user()->role == "assistant") ? null : Auth::user()->exchange_id;
            return Excel::download(new CustomerListExport($exchangeId), 'customerRecord.xlsx');
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
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $customerRecords = Customer::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->orderBy('created_at', 'desc')->get();

            return view("admin.customer.list", compact('customerRecords'))
                ->withHeaders(['X-Frame-Options' => 'DENY']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading customers: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'cash_amount' => 'required|numeric',
            'reference_number' => 'required|string|max:255',
            'remarks' => 'required|string|max:255',
        ]);

        try {
            $user = Auth::user();
            Customer::create([
                'name' => $validatedData['name'],
                'reference_number' => $validatedData['reference_number'],
                'cash_amount' => $validatedData['cash_amount'],
                'remarks' => $validatedData['remarks'],
                'exchange_id' => $user->exchange_id,
                'user_id' => $user->id,
            ]);

            return response()->json(['message' => 'Customer added successfully!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding customer: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $user = Auth::user();
            $query = Customer::where('id', $request->id);

            if ($user->role !== 'admin' && $user->role !== 'assistant') {
                $query->where('exchange_id', $user->exchange_id);
            }

            $customer = $query->firstOrFail();
            $customer->delete();
            return response()->json(['success' => true, 'message' => 'Customer deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Customer record not found or access denied.'], 404);
        }
    }
    
    public function exchangeIndex()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $user = Auth::user();
            $customerRecords = Customer::where('exchange_id', $user->exchange_id)
                ->where('user_id', $user->id)
                ->get();

            return view("exchange.customer.list", compact('customerRecords'))
                ->withHeaders(['X-Frame-Options' => 'DENY']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading records: ' . $e->getMessage());
        }
    }

}

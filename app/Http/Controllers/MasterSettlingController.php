<?php

namespace App\Http\Controllers;

use App\Models\MasterSettling;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MasterSettlingMonthlyListExport;
use App\Exports\MasterSettlingWeeklyListExport;
use Carbon\Carbon;
use Auth;

class MasterSettlingController extends Controller
{
    public function masterSettlingListMonthlyExportExcel(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $user = Auth::user();
            $exchangeId = ($user->role == "admin" || $user->role == "assistant") ? null : $user->exchange_id;
            return Excel::download(new MasterSettlingMonthlyListExport($exchangeId), 'MonthlyMasterSettlingRecord.xlsx');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }
    
    public function masterSettlingListWeeklyExportExcel(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $user = Auth::user();
            $exchangeId = ($user->role == "admin" || $user->role == "assistant") ? null : $user->exchange_id;
            return Excel::download(new MasterSettlingWeeklyListExport($exchangeId), 'WeeklyMasterSettlingRecord.xlsx');
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
            $masterSettlingRecords = MasterSettling::with(['exchange', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();    
            return view("admin.master_settling.list", compact('masterSettlingRecords'));
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
            $masterSettlingRecords = MasterSettling::with(['exchange', 'user'])->get();
            return view("assistant.master_settling.list", compact('masterSettlingRecords'));
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
            $masterSettlingRecords = MasterSettling::with(['exchange', 'user'])
                ->where('exchange_id', $user->exchange_id)
                ->where('user_id', $user->id)
                ->get();
    
            return view("exchange.master_settling.list", compact('masterSettlingRecords'));
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
            'white_label' => 'nullable|string|max:255',
            'credit_reff' => 'nullable|string',
            'settling_point' => 'nullable|numeric',
            'price' => 'nullable|numeric',
        ]);

        try {
            $user = auth()->user();
            MasterSettling::create([
                'white_label' => $validatedData['white_label'],
                'credit_reff' => $validatedData['credit_reff'],
                'settling_point' => $validatedData['settling_point'],
                'price' => $validatedData['price'],
                'exchange_id' => $user->exchange_id,
                'user_id' => $user->id,
            ]);
            return response()->json(['success' => true, 'message' => 'Master Settling saved successfully!']);
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
            'id' => 'required|exists:master_settlings,id',
            'white_label' => 'required|string',
            'credit_reff' => 'required|string',
            'settling_point' => 'required|numeric',
            'price' => 'required|numeric',
        ]);

        try {
            $user = Auth::user();
            $query = MasterSettling::where('id', $request->id);
            if ($user->role !== 'admin') {
                $query->where('exchange_id', $user->exchange_id);
            }
            $masterSettling = $query->firstOrFail();

            $masterSettling->white_label = $request->white_label;
            $masterSettling->credit_reff = $request->credit_reff;
            $masterSettling->settling_point = $request->settling_point;
            $masterSettling->price = $request->price;
            $masterSettling->save();
            return response()->json(['success' => true, 'message' => 'Master Settling updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating record or access denied.'], 404);
        }
    }
    
    public function destroy(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $user = Auth::user();
            $query = MasterSettling::where('id', $request->id);
            if ($user->role !== 'admin') {
                $query->where('exchange_id', $user->exchange_id);
            }
            $masterSettling = $query->firstOrFail();

            $masterSettling->delete();
            return response()->json(['success' => true, 'message' => 'Master Settling deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Master Settling not found or access denied.'], 404);
        }
    }
}    


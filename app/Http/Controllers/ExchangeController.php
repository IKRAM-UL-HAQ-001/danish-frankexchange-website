<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use App\Models\BankEntry;
use App\Models\Cash;
use App\Models\Customer;
use App\Models\OwnerProfit;
use App\Models\OpenCloseBalance;
use App\Models\VenderPayment;
use App\Models\MasterSettling;
use App\Models\User;
use App\Models\Loan;
use Carbon\Carbon;
use App\Models\ClientBalance;
use DB;
use Auth;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $today = Carbon::today();
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $user = Auth::user();
            $exchangeId = $user->exchange_id;
            $exchange = Exchange::find($exchangeId);
            
            $userCount = Cash::where('exchange_id', $exchangeId)->distinct('user_id')->count('user_id');

            $totalOpenCloseBalance = OpenCloseBalance::where('exchange_id', $exchangeId)
                ->whereDate('created_at', $today)
                ->sum('open_balance');
            
            $totalLoanSends = Loan::where('exchange_id', $exchangeId)
                ->where('cash_type', 'send')
                ->sum('cash_amount');

            $totalLoanReceived = Loan::where('receiver_id', $exchangeId)
                ->where('cash_type', 'return')
                ->sum('cash_amount');

            $totalLoanReturn = Loan::where('exchange_id', $exchangeId)
                ->where('cash_type', 'return')
                ->sum('cash_amount');
        
            $totalClientBalance = ClientBalance::whereDate('created_at', $today)
                ->sum('client_balance');
            
            $totalLoanSend = $totalLoanSends - $totalLoanReceived;

            $customerCountDaily = Cash::where('exchange_id', $exchangeId)
                ->whereDate('created_at', $today)
                ->distinct('reference_number')
                ->count('reference_number');

            $totalDepositDaily = Cash::where('exchange_id', $exchangeId)
                ->where('cash_type', 'deposit')
                ->whereDate('created_at', $today)
                ->sum('cash_amount');

            $totalWithdrawalDaily = Cash::where('exchange_id', $exchangeId)
                ->where('cash_type', 'withdrawal')
                ->whereDate('created_at', $today)
                ->sum('cash_amount');

            $totalExpenseDaily = Cash::where('exchange_id', $exchangeId)
                ->where('cash_type', 'expense')
                ->whereDate('created_at', $today)
                ->sum('cash_amount');

            $totalBonusDaily = Cash::where('exchange_id', $exchangeId)
                ->whereDate('created_at', $today)
                ->sum('bonus_amount');

            $totalOwnerProfitDaily = OwnerProfit::where('exchange_id', $exchangeId)
                ->whereDate('created_at', $today)
                ->sum('cash_amount');

            $totalNewCustomerDaily = Customer::where('exchange_id', $exchangeId)
                ->whereDate('created_at', $today)
                ->distinct('id')
                ->count('id');

            $totalMasterSettlingDaily = MasterSettling::where('exchange_id', $exchangeId)
                ->whereDate('created_at', $today)
                ->distinct('settling_point')
                ->sum('settling_point');
            
            $totalVenderPaymentDaily = VenderPayment::where('exchange_id', $exchangeId)
                ->whereDate('created_at', $today)
                ->sum('paid_amount');
            
            $totalBalanceDaily = $totalDepositDaily - $totalWithdrawalDaily - $totalExpenseDaily;

            $totalOpenCloseBalanceDaily1 = $totalOpenCloseBalance + $totalBalanceDaily;
            $totalOpenCloseBalanceDaily = $totalOpenCloseBalanceDaily1 - $totalVenderPaymentDaily - $totalOwnerProfitDaily;

            // Weekly Metrics
            $totalDepositWeekly = Cash::where('exchange_id', $exchangeId)
                ->where('cash_type', 'deposit')
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('cash_amount');

            $totalWithdrawalWeekly = Cash::where('exchange_id', $exchangeId)
                ->where('cash_type', 'withdrawal')
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('cash_amount');

            $totalExpenseWeekly = Cash::where('exchange_id', $exchangeId)
                ->where('cash_type', 'expense')
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('cash_amount');

            $totalBonusWeekly = Cash::where('exchange_id', $exchangeId)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('bonus_amount');

            $totalOwnerProfitWeekly = OwnerProfit::where('exchange_id', $exchangeId)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('cash_amount');

            $totalNewCustomerWeekly = Customer::where('exchange_id', $exchangeId)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->distinct('id')
                ->count('id');

            $totalBalanceWeekly = $totalDepositWeekly - $totalWithdrawalWeekly - $totalExpenseWeekly;

            $totalVenderPaymentWeekly = VenderPayment::where('exchange_id', $exchangeId)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('paid_amount');

            // Monthly
            $totalDepositMonthly = Cash::where('exchange_id', $exchangeId)
                ->where('cash_type', 'deposit')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('cash_amount');

            $totalBonusMonthly = Cash::where('exchange_id', $exchangeId)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('bonus_amount');

            $totalOwnerProfitMonthly = OwnerProfit::where('exchange_id', $exchangeId)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('cash_amount');

            $totalNewCustomerMonthly = Customer::where('exchange_id', $exchangeId)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->distinct('id')
                ->count('id');

            $totalBalanceMonthly = $totalDepositMonthly - $totalWithdrawalMonthly - $totalExpenseMonthly;

            $totalVenderPaymentMonthly = VenderPayment::where('exchange_id', $exchangeId)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('paid_amount');

            // Filtered Bank Metrics
            $totalFreezAmountDaily = BankEntry::where('status', 'freez')
                ->where('exchange_id', $exchangeId)
                ->whereDate('created_at', $today)
                ->sum('cash_amount');

            $totalFreezAmount = BankEntry::where('status', 'freez')
                ->where('exchange_id', $exchangeId)
                ->sum('cash_amount');

            $totalAmountAdd = BankEntry::where('cash_type', 'add')
                ->where('exchange_id', $exchangeId)
                ->whereNull('status')
                ->sum('cash_amount');

            $totalAmountSubtract = BankEntry::where('cash_type', 'minus')
                ->where('exchange_id', $exchangeId)
                ->whereNull('status')
                ->sum('cash_amount');

            $totalBankBalance = $totalAmountAdd - $totalAmountSubtract - $totalFreezAmount;

            $totalFreezAmountWeekly = BankEntry::where('status', 'freez')
                ->where('exchange_id', $exchangeId)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('cash_amount');

            $totalFreezAmountMonthly = BankEntry::where('status', 'freez')
                ->where('exchange_id', $exchangeId)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('cash_amount');
            
            return view("exchange.dashboard", compact(
                'totalClientBalance', 'totalBankBalance', 'totalLoanSend', 'totalLoanReturn',
                'totalBalanceDaily', 'totalDepositDaily', 'totalWithdrawalDaily', 'totalExpenseDaily',
                'totalBonusDaily', 'totalNewCustomerDaily', 'totalOwnerProfitDaily', 'totalOpenCloseBalanceDaily',
                'totalMasterSettlingDaily', 'totalFreezAmountDaily', 'totalVenderPaymentDaily',
                'totalBalanceMonthly', 'totalDepositMonthly', 'totalWithdrawalMonthly', 'totalExpenseMonthly',
                'totalBonusMonthly', 'totalNewCustomerMonthly', 'totalOwnerProfitMonthly', 'totalFreezAmountMonthly', 'totalVenderPaymentMonthly',
                'totalBalanceWeekly', 'totalDepositWeekly', 'totalWithdrawalWeekly', 'totalExpenseWeekly',
                'totalBonusWeekly', 'totalOwnerProfitWeekly', 'totalNewCustomerWeekly', 'totalFreezAmountWeekly', 'totalVenderPaymentWeekly'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading dashboard: ' . $e->getMessage());
        }
    }

    public function exchangeList()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.login');
        }
        try {
            $exchangeRecords = Exchange::orderBy('created_at', 'desc')->get();
            return view("admin.exchange.list", compact('exchangeRecords'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading exchanges: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate(['name' => 'required|string|max:255']);
        
        try {
            Exchange::create(['name' => $request->name]);
            return response()->json(['message' => 'Exchange added successfully!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding exchange: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $exchange = Exchange::findOrFail($request->id);
            $exchange->delete();
            return response()->json(['success' => true, 'message' => 'Exchange deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Exchange not found or deletion failed.'], 404);
        }
    }
}

<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Contact;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $period = $request->period ?? 'this_month';

        [$from, $to] = $this->getPeriodDates($period);

        $income = Income::where('user_id', $userId)->whereBetween('date', [$from, $to])->sum('amount');
        $expense = Expense::where('user_id', $userId)->whereBetween('date', [$from, $to])->sum('amount');

        $bankAccounts = BankAccount::where('user_id', $userId)
            ->where('status', 'active')
            ->get(['id', 'name', 'account_type', 'currency', 'current_balance']);

        $contactsReceivable = Contact::where('user_id', $userId)
            ->where('opening_balance_type', 'receivable')
            ->where('opening_balance', '>', 0)
            ->orderByDesc('opening_balance')
            ->take(5)
            ->get(['id', 'name', 'opening_balance']);

        $contactsPayable = Contact::where('user_id', $userId)
            ->where('opening_balance_type', 'payable')
            ->where('opening_balance', '>', 0)
            ->orderByDesc('opening_balance')
            ->take(5)
            ->get(['id', 'name', 'opening_balance']);

        $totalIncome = Income::where('user_id', $userId)->sum('amount');
        $totalExpense = Expense::where('user_id', $userId)->sum('amount');

        return response()->json([
            'income' => $income,
            'expense' => $expense,
            'profit' => $income - $expense,
            'bank_accounts' => $bankAccounts,
            'contacts_receivable' => $contactsReceivable,
            'contacts_payable' => $contactsPayable,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_profit' => $totalIncome - $totalExpense,
            'period' => $period,
        ]);
    }

    private function getPeriodDates(string $period): array
    {
        return match ($period) {
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'this_quarter' => [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }
}

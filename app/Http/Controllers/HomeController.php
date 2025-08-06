<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\BusinessType;
use App\Models\City;
use App\Models\CreateBill;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerRecovery;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            if ($usertype == 'orderbooker') {
                $user = Auth::user();
                $userId = $user->user_id;

                // Fetch assigned or lowercase assigned bills
                $bills = CreateBill::with(['customer', 'orderBooker', 'salesman', 'assignUser'])
                    ->whereIn('status', ['Assigned', 'assigned']) // to handle both cases
                    ->where(function ($query) use ($userId) {
                        $query->where('order_booker_id', $userId)
                            ->orWhere(function ($q) use ($userId) {
                                $q->where('assign_type', 'booker')
                                    ->where('assign_user_id', $userId);
                            });
                    })
                    ->get();

                // Total customers for this order booker
                $customerIds = $bills->pluck('customer_id')->unique();
                $totalCustomers = $customerIds->count();

                // Bill counts
                $totalBills = $bills->count();
                $paidBills = $bills->where('payment_status', 'Paid')->count();
                $unpaidBills = $bills->where('payment_status', 'Unpaid')->count();

                // Amounts
                $totalAmount = $bills->sum('amount');
                $paidAmount = $bills->where('payment_status', 'Paid')->sum('amount');
                $unpaidAmount = $bills->where('payment_status', 'Unpaid')->sum('amount');

                return view('orderbooker_panel.dashboard', compact(
                    'totalCustomers',
                    'totalBills',
                    'paidBills',
                    'unpaidBills',
                    'totalAmount',
                    'paidAmount',
                    'unpaidAmount',
                    'userId'
                ));
            } else if ($usertype == 'saleman') {

                $user = Auth::user();
                $userId = $user->user_id;

                // Get all bills assigned to this salesman
                $bills = CreateBill::with(['customer', 'orderBooker', 'salesman', 'assignUser'])
                    ->where('assign_type', 'salesman')
                    ->where('assign_user_id', $userId)
                    ->get();

                // Calculate required values
                $totalAssignedBills = $bills->count();
                $totalAssignedAmount = $bills->sum('amount');
                $totalPaidAmount = $bills->where('payment_status', 'Paid')->sum('amount');
                $totalDueAmount = $bills->where('payment_status', 'Unpaid')->sum('amount');

                return view('saleman_panel.dashboard', [
                    'userId' => $userId,
                    'totalAssignedBills' => $totalAssignedBills,
                    'totalAssignedAmount' => $totalAssignedAmount,
                    'totalPaidAmount' => $totalPaidAmount,
                    'totalDueAmount' => $totalDueAmount,
                ]);
            } else if ($usertype == 'accountant') {

                $totalOrderBookers = Salesman::where('admin_or_user_id', $userId)
                    ->where('designation', 'orderbooker')
                    ->count();

                $totalSalesmen = Salesman::where('admin_or_user_id', $userId)
                    ->where('designation', 'saleman')
                    ->count();

                $bills = CreateBill::with(['customer', 'orderBooker', 'salesman', 'assignUser'])
                    ->get();

                $totalAssignedBills = $bills->count();
                $totalAssignedAmount = $bills->sum('amount');
                $totalPaidAmount = $bills->where('payment_status', 'Paid')->sum('amount');
                $totalDueAmount = $totalAssignedAmount - $totalPaidAmount;
                return view('accountant_panel.dashboard', [
                    'userId' => $userId,
                    'totalAssignedBills' => $totalAssignedBills,
                    'totalAssignedAmount' => $totalAssignedAmount,
                    'totalPaidAmount' => $totalPaidAmount,
                    'totalDueAmount' => $totalDueAmount,
                    'totalOrderBookers' => $totalOrderBookers,
                    'totalSalesmen' => $totalSalesmen,
                ]);
            } else if ($usertype == 'admin') {

                $totalCities = City::where('admin_or_user_id', $userId)->count();
                $totalAreas = Area::where('admin_or_user_id', $userId)->count();
                $totalBusinessTypes = BusinessType::where('admin_or_user_id', $userId)->count();

                // Staff Summary
                $totalOrderBookers = Salesman::where('admin_or_user_id', $userId)->where('designation', 'orderbooker')->count();
                $totalSalesmen = Salesman::where('admin_or_user_id', $userId)->where('designation', 'saleman')->count();
                $totalAccountants = Salesman::where('admin_or_user_id', $userId)->where('designation', 'accountant')->count();

                // Customer Management
                $totalCustomers = Customer::where('admin_or_user_id', $userId)->count();
                $totalLedgerAmount = CustomerLedger::where('admin_or_user_id', $userId)->sum('closing_balance');
                $totalRecoveries = CustomerRecovery::where('admin_or_user_id', $userId)->sum('amount_paid');

                return view('admin_panel.dashboard', compact(
                    'totalCities',
                    'totalAreas',
                    'totalBusinessTypes',
                    'totalOrderBookers',
                    'totalSalesmen',
                    'totalAccountants',
                    'totalCustomers',
                    'totalLedgerAmount',
                    'totalRecoveries'
                ));
            } else {
                return redirect()->back();
            }
        }
    }
}

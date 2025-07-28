<?php

namespace App\Http\Controllers;

use App\Models\CreateBill;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateBillController extends Controller
{
    public function create_bill()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $OrderBookers = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'orderbooker')
                ->get();

            $Salesmen = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'saleman')
                ->get();

            $Customers = Customer::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.Create_bills.add_bill', compact('OrderBookers', 'Salesmen', 'Customers'));
        } else {
            return redirect()->back();
        }
    }

    public function store_bill(Request $request)
    {
        $request->validate([
            'order_booker_id' => 'required',
            'salesman_id' => 'required',
            'bills' => 'required|array|min:1',
            'bills.*.invoice_number' => 'required',
            'bills.*.date' => 'required|date',
            'bills.*.customer_id' => 'required|exists:customers,id',
            'bills.*.amount' => 'required|numeric|min:0',
        ]);

        $adminId = Auth::id();

        $bills = $request->input('bills');

        // Step 1: Store each bill
        foreach ($bills as $billData) {
            CreateBill::create([
                'admin_or_user_id' => $adminId,
                'invoice_number' => $billData['invoice_number'],
                'date' => $billData['date'],
                'customer_id' => $billData['customer_id'],
                'amount' => $billData['amount'],
                'order_booker_id' => $request->order_booker_id,
                'salesman_id' => $request->salesman_id,
                'status' => 'unassigned',
                'payment_status' => 'Unpaid',
            ]);
        }

        // Step 2: Sum amounts per customer
        $totalsPerCustomer = [];

        foreach ($bills as $bill) {
            $customerId = $bill['customer_id'];
            $amount = $bill['amount'];

            if (!isset($totalsPerCustomer[$customerId])) {
                $totalsPerCustomer[$customerId] = 0;
            }

            $totalsPerCustomer[$customerId] += $amount;
        }

        // Step 3: Update customer ledger per unique customer
        foreach ($totalsPerCustomer as $customerId => $totalAmount) {
            $ledger = CustomerLedger::where('customer_id', $customerId)
                ->where('admin_or_user_id', $adminId)
                ->first();

            if ($ledger) {
                $ledger->previous_balance = $ledger->closing_balance;
                $ledger->closing_balance += $totalAmount;
                $ledger->save();
            } else {
                CustomerLedger::create([
                    'admin_or_user_id' => $adminId,
                    'customer_id' => $customerId,
                    'opening_balance' => 0,
                    'previous_balance' => 0,
                    'closing_balance' => $totalAmount,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Bills created and ledgers updated successfully.');
    }

    public function getCustomerLedger($id)
    {
        $adminId = Auth::id();

        $ledger = CustomerLedger::where('admin_or_user_id', $adminId)
            ->where('customer_id', $id)
            ->first();

        $customer = Customer::find($id);

        return response()->json([
            'closing_balance' => $ledger?->closing_balance ?? 0,
            'city' => $customer?->city ?? '',
            'area' => $customer?->area ?? ''
        ]);
    }

    public function bills()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $user = Auth::user();
        $userId = $user->user_id;

        if ($user->usertype === 'admin') {
            // Admin sees all bills
            $bills = CreateBill::with(['customer', 'orderBooker', 'salesman', 'assignUser'])->get();
        } elseif ($user->usertype === 'orderbooker') {
            // Order Booker sees only Assigned bills either assigned to him or created by him
            $bills = CreateBill::with(['customer', 'orderBooker', 'salesman', 'assignUser'])
                ->where('status', 'Assigned') // show only assigned bills
                ->where(function ($query) use ($userId) {
                    $query->where('order_booker_id', $userId)
                        ->orWhere(function ($q) use ($userId) {
                            $q->where('assign_type', 'booker')
                                ->where('assign_user_id', $userId);
                        });
                })
                ->get();
        } else {
            // Others see nothing
            $bills = collect();
        }

        $OrderBookers = Salesman::where('designation', 'orderbooker')->get();
        $Salesmen = Salesman::where('designation', 'saleman')->get();
        $Customers = Customer::all();

        return view('admin_panel.Create_bills.all_bills', compact('bills', 'OrderBookers', 'Salesmen', 'Customers'));
    }



    public function deleteBill(Request $request)
    {
        $billId = $request->id;
        $bill = CreateBill::find($billId);

        if (!$bill) {
            return response()->json(['message' => 'Bill not found.'], 404);
        }

        // Fetch Customer Ledger record
        $ledger = CustomerLedger::where('customer_id', $bill->customer_id)
            ->orderBy('id', 'desc') // most recent ledger
            ->first();

        if ($ledger) {
            $ledger->closing_balance = $ledger->closing_balance - $bill->amount;
            $ledger->save();
        }

        // Now permanently delete the bill
        $bill->delete();

        return response()->json(['message' => 'Bill deleted successfully.']);
    }

    public function updateBill(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:create_bills,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $bill = CreateBill::find($request->id);

        // Ledger Adjustment
        $oldAmount = (float) $request->old_amount;
        $newAmount = (float) $request->amount;
        $diff = $newAmount - $oldAmount;

        $ledger = CustomerLedger::where('customer_id', $bill->customer_id)->orderBy('id', 'desc')->first();
        if ($ledger) {
            $ledger->closing_balance += $diff;
            $ledger->save();
        }

        // Update Bill
        $bill->customer_id = $request->customer_id;
        $bill->order_booker_id = $request->order_booker_id;
        $bill->salesman_id = $request->salesman_id;
        $bill->invoice_number = $request->invoice_number;
        $bill->date = $request->date;
        $bill->amount = $newAmount;
        $bill->save();

        return response()->json(['message' => 'Bill updated successfully.']);
    }

    public function bill_asign()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $OrderBookers = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'orderbooker')
                ->get();

            $Salesmen = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'saleman')
                ->get();

            $Customers = Customer::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.Create_bills.bill_asign', compact('OrderBookers', 'Salesmen', 'Customers'));
        } else {
            return redirect()->back();
        }
    }

    public function fetchUsersByRole(Request $request)
    {
        $role = $request->role;
        $userId = Auth::id();

        $users = Salesman::where('admin_or_user_id', $userId)
            ->where('designation', $role == 'booker' ? 'orderbooker' : 'saleman')
            ->get();

        return response()->json($users);
    }

    // Fetch Unassigned Bills (AJAX)
    public function fetchUnassignedBills()
    {
        $userId = Auth::id();

        $bills = CreateBill::with('customer') // Load customer relation
            ->where('admin_or_user_id', $userId)
            ->where('status', 'unassigned')
            ->get();

        return response()->json($bills);
    }

    public function assignBills(Request $request)
    {
        $assignType = $request->assign_to;
        $userId = $request->user_id;
        $asigned_date = $request->asigned_date;
        $billIds = $request->bill_ids;

        // Update all selected bills
        DB::table('create_bills')
            ->whereIn('id', $billIds)
            ->update([
                'status' => 'assigned',
                'assign_type' => $assignType,
                'assign_user_id' => $userId,
                'asigned_date' => $asigned_date,
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Bills assigned successfully!');
    }
}

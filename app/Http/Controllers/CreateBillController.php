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
            'invoice_number' => 'required',
            'date' => 'required',
            'customer_id' => 'required',
            'amount' => 'required',
            'order_booker_id' => 'required',
            'salesman_id' => 'required',
        ]);

        $adminId = Auth::id();

        // Step 1: Create the bill
        $bill = new CreateBill();
        $bill->admin_or_user_id = $adminId;
        $bill->invoice_number = $request->invoice_number;
        $bill->date = $request->date;
        $bill->customer_id = $request->customer_id;
        $bill->amount = $request->amount;
        $bill->order_booker_id = $request->order_booker_id;
        $bill->salesman_id = $request->salesman_id;
        $bill->status = 'unassigned';
        $bill->payment_status = 'Unpaid';
        $bill->save();

        // Step 2: Update Customer Ledger
        $ledger = CustomerLedger::where('customer_id', $request->customer_id)
            ->where('admin_or_user_id', $adminId)
            ->first();

        if ($ledger) {
            // Add bill amount to both balances
            $ledger->previous_balance = $request->amount;
            $ledger->closing_balance += $request->amount;
            $ledger->save();
        } else {
            // First time ledger entry
            CustomerLedger::create([
                'admin_or_user_id' => $adminId,
                'customer_id' => $request->customer_id,
                'opening_balance' => 0,
                'previous_balance' => $request->amount,
                'closing_balance' => $request->amount,
            ]);
        }


        return redirect()->back()->with('success', 'Bill created and customer ledger updated!');
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
        if (Auth::id()) {
            $userId = Auth::id();
            $bills = CreateBill::where('admin_or_user_id', $userId)->get();

            $OrderBookers = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'orderbooker')
                ->get();

            $Salesmen = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'saleman')
                ->get();

            $Customers = Customer::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.Create_bills.all_bills', compact('bills', 'OrderBookers', 'Salesmen', 'Customers'));
        } else {
            return redirect()->back();
        }
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
        $bills = CreateBill::where('admin_or_user_id', $userId)
            ->where('status', 'unassigned')
            ->get();

        return response()->json($bills);
    }

    public function assignBills(Request $request)
    {
        $assignType = $request->assign_to;
        $userId = $request->user_id;
        $billIds = $request->bill_ids;

        // Update all selected bills
        DB::table('create_bills')
            ->whereIn('id', $billIds)
            ->update([
                'status' => 'assigned',
                'assign_type' => $assignType,
                'assign_user_id' => $userId,
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Bills assigned successfully!');
    }
}

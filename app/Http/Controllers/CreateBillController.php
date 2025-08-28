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

            $OrderBookers = Salesman::where('designation', 'orderbooker')
                ->get();

            $Salesmen = Salesman::where('designation', 'saleman')
                ->get();

            $Customers = Customer::get();

            return view('admin_panel.Create_bills.add_bill', compact('OrderBookers', 'Salesmen', 'Customers'));
        } else {
            return redirect()->back();
        }
    }

    public function store_bill(Request $request)
    {
        $user = Auth::user();
        $usertype = $user->usertype;

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
                'usertype' => $usertype,
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

        $ledger = CustomerLedger::where('customer_id', $id)
            ->first();

        $customer = Customer::find($id);

        return response()->json([
            'closing_balance' => $ledger?->closing_balance ?? 0,
            'city' => $customer?->city ?? '',
            'area' => $customer?->area ?? ''
        ]);
    }

    public function bills(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $user = Auth::user();
        $userId = $user->user_id;

        // Base query with relationships
        $query = CreateBill::with(['customer', 'orderBooker', 'salesman', 'assignUser']);

        // Role-based restrictions
        if ($user->usertype === 'admin' || $user->usertype === 'accountant') {
            // Admin & Accountant: see all bills
        } elseif ($user->usertype === 'orderbooker') {
            $query->where('status', 'Assigned')
                ->where(function ($q) use ($userId) {
                    $q->where('order_booker_id', $userId)
                        ->orWhere(function ($sub) use ($userId) {
                            $sub->where('assign_type', 'booker')
                                ->where('assign_user_id', $userId);
                        });
                });
        } elseif ($user->usertype === 'saleman') {
            $query->where('assign_type', 'salesman')
                ->where('assign_user_id', $userId);
        } else {
            $query->whereRaw('1=0'); // No results for other roles
        }

        // -------- FILTERS --------
        if ($request->filled('booker_id')) {
            $query->where('order_booker_id', $request->booker_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $bills = $query->get();

        // Booker & Salesman lists from Salesman table
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
    public function extendAssignedDate(Request $request)
    {
        $request->validate([
            'bill_id' => 'required|exists:create_bills,id',
            'new_assigned_date' => 'required|date',
        ]);

        $bill = CreateBill::find($request->bill_id);
        $bill->asigned_date = $request->new_assigned_date;
        $bill->updated_at = now();
        $bill->save();

        return redirect()->back()->with('success', 'Assigned date updated successfully.');
    }
    public function unassign(Request $request)
    {
        $bill = CreateBill::findOrFail($request->bill_id);

        // ✅ Update fields
        $bill->status = 'unassigned';
        $bill->assign_type = null;
        $bill->assign_user_id = null;
        $bill->asigned_date = null;
        $bill->save();

        return response()->json(['success' => true]);
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
        $user = Auth::user();

        if (!$user) {
            return response()->json([], 401); // Unauthorized
        }

        // Admin logic
        if ($user->usertype === 'admin') {
            $users = Salesman::where('designation', $role === 'booker' ? 'orderbooker' : 'saleman')
                ->get();
        }

        // Orderbooker logic (filter salesmen based on area)
        elseif ($user->usertype === 'orderbooker' && $role === 'salesman') {
            $userAreas = json_decode($user->area, true);

            $allSalesmen = Salesman::where('designation', 'saleman')->get();

            $users = $allSalesmen->filter(function ($salesman) use ($userAreas) {
                $salesmanAreas = json_decode($salesman->area, true);

                if (is_array($salesmanAreas)) {
                    return !empty(array_intersect($userAreas, $salesmanAreas));
                }

                return false;
            })->values(); // Reset collection keys
        }

        // Any other case, return empty
        else {
            $users = collect();
        }

        return response()->json($users);
    }


    // Fetch Unassigned Bills (AJAX)
    public function fetchUnassignedBills(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([], 401); // Unauthorized
        }
        $userId = $request->user_id;
        // Admin sees all unassigned bills
        if ($user->usertype === 'admin') {
            $bills = CreateBill::with('customer')
                ->where('status', 'unassigned')
                ->where('order_booker_id', $userId)
                ->get();
        }

        // Order Booker sees only their relevant unassigned bills by order_booker_id
        elseif ($user->usertype === 'orderbooker') {
            $userId = $user->user_id;

            $bills = CreateBill::with('customer')
                ->where('status', 'Assigned')
                ->where('payment_status', 'Unpaid') // ✅ Only unpaid bills
                ->where('order_booker_id', $userId)
                ->get();
        }

        // Others get nothing
        else {
            $bills = collect();
        }

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
                'status' => 'Assigned',
                'assign_type' => $assignType,
                'assign_user_id' => $userId,
                'asigned_date' => $asigned_date,
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Bills assigned successfully!');
    }
}

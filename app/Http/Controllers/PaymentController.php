<?php

namespace App\Http\Controllers;

use App\Models\CreateBill;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerRecovery;
use App\Models\Distributor;
use App\Models\DistributorLedger;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Salesman;
use App\Models\Vendor;
use App\Models\VendorLedger;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{

    public function vendors_payments()
    {
        $Vendors = Vendor::all(['id', 'Party_name']);
        return view('admin_panel.payments.vendors_payments', compact('Vendors'));
    }

    public function storeVendorPayment(Request $request)
    {
        $request->validate([
            'Vendor_id' => 'required|exists:vendors,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'detail' => 'nullable|string|max:255',
        ]);

        $vendorId = $request->Vendor_id;

        // Get last ledger entry for this vendor
        $latestLedger = VendorLedger::where('vendor_id', $vendorId)->latest()->first();

        $previousBalance = $latestLedger ? $latestLedger->closing_balance : 0;
        $newClosing = $previousBalance - $request->amount;

        if ($latestLedger) {
            $latestLedger->update([
                'closing_balance' => $newClosing,
            ]);
            $ledgerId = $latestLedger->id;
        } else {
            $newLedger = VendorLedger::create([
                'admin_or_user_id' => auth()->id(),
                'vendor_id' => $vendorId,
                'previous_balance' => $previousBalance,
                'closing_balance' => $newClosing,
            ]);
            $ledgerId = $newLedger->id;
        }

        // Save the payment
        VendorPayment::create([
            'admin_or_user_id' => auth()->id(),
            'vendor_id' => $vendorId,
            'amount_paid' => $request->amount,
            'payment_date' => $request->date,
            'description' => $request->detail,
        ]);

        return redirect()->back()->with('success', 'Vendor payment saved successfully.');
    }


    public function getVendorBalance($id)
    {
        $balance = VendorLedger::where('vendor_id', $id)->value('closing_balance');

        $purchases = Purchase::where('party_name', $id)
            ->select('purchase_date', 'grand_total')
            ->orderBy('purchase_date', 'desc')
            ->get();

        return response()->json([
            'balance' => $balance ?? 0,
            'purchases' => $purchases
        ]);
    }

    public function customer_payments()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $customers = Customer::where('admin_or_user_id', $userId)
                ->get(['id', 'customer_name', 'shop_name', 'area']);
            $orderbooker = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'orderbooker')
                ->get();

            return view('admin_panel.payments.customers_payments', compact('customers', 'orderbooker'));
        } else {
            return redirect()->back();
        }
    }



    public function storeCustomerPayment(Request $request)
    {
        $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'ordbker_id'      => 'required|exists:users,id',
            'payment_date'    => 'required|date',
            'payment_method'  => 'nullable|string',
            'bill_ids'        => 'nullable|array',
            'amount_received' => 'nullable|array',
        ]);

        $latestLedger = CustomerLedger::where('customer_id', $request->customer_id)
            ->latest('id')
            ->first();

        if (!$latestLedger) {
            return redirect()->back()->with('error', 'Customer ledger not found.');
        }

        $previous_balance = $latestLedger->closing_balance;
        $amount_paid_total = 0;

        if ($request->has('bill_ids') && is_array($request->bill_ids)) {
            foreach ($request->bill_ids as $bill_id) {
                $amount_paid_total += floatval($request->amount_received[$bill_id] ?? 0);
            }
        }

        $new_closing_balance = $previous_balance - $amount_paid_total;

        $latestLedger->update([
            'admin_or_user_id'  => auth()->id(),
            'opening_balance'   => $latestLedger->opening_balance,
            'previous_balance'  => $previous_balance,
            'closing_balance'   => $new_closing_balance,
        ]);

        $recovery = CustomerRecovery::create([
            'admin_or_user_id'     => auth()->id(),
            'customer_ledger_id'   => $latestLedger->id,
            'amount_paid'          => $amount_paid_total,
            'salesman'             => $request->ordbker_id,
            'date'                 => $request->payment_date,
            'remarks'              => $request->payment_method,
        ]);

        if ($request->has('bill_ids') && is_array($request->bill_ids)) {
            foreach ($request->bill_ids as $bill_id) {
                $bill = CreateBill::find($bill_id);

                if (!$bill) continue;

                $amount_received_for_bill = floatval($request->amount_received[$bill_id] ?? 0);
                $previous_remaining = $bill->remaining_amount !== null
                    ? $bill->remaining_amount
                    : $bill->amount;

                // Avoid overpayment
                if ($amount_received_for_bill > $previous_remaining) {
                    $amount_received_for_bill = $previous_remaining;
                }

                $new_remaining = max($previous_remaining - $amount_received_for_bill, 0);

                if ($new_remaining <= 0) {
                    $payment_status = 'Paid';
                } elseif ($new_remaining < $bill->amount) {
                    $payment_status = 'Partially Paid';
                } else {
                    $payment_status = 'Unpaid';
                }

                $bill->update([
                    'payment_status'   => $payment_status,
                    'remaining_amount' => $new_remaining,
                    'status'           => 'Assigned',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Payment recorded and bills updated successfully.');
    }




    public function getCustomerBalance($id)
    {
        $latestLedger = CustomerLedger::where('customer_id', $id)->latest()->first();
        $balance = $latestLedger ? $latestLedger->closing_balance : 0;

        return response()->json(['closing_balance' => $balance]);
    }

    public function getCustomerBills($id)
    {
        $bills = CreateBill::with('customer') // eager load customer relationship
            ->where('customer_id', $id)
            ->whereIn('payment_status', ['Unpaid', 'Partially Paid'])
            ->get(['id', 'invoice_number', 'date', 'amount', 'remaining_amount', 'payment_status', 'customer_id']);
        return response()->json($bills);
    }


    public function Distributor_payments()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $distributors = Distributor::all(['id', 'Customer']); // using 'Customer' as distributor name
            $Salesmans = Salesman::where('admin_or_user_id', $userId)->where('designation', 'Saleman')->get();
            return view('admin_panel.payments.Distributor_payments', compact('distributors', 'Salesmans'));
        } else {
            return redirect()->back();
        }
    }

    public function getDistributorBalance($id)
    {
        $distributor = Distributor::with(['sales' => function ($q) {
            $q->select('id', 'distributor_id', 'Date as sale_date', 'grand_total as total')->latest()->take(10);
        }])->find($id);

        if (!$distributor) {
            return response()->json(['balance' => 0, 'sales' => []]);
        }

        $latestLedger = DistributorLedger::where('distributor_id', $id)
            ->latest('id')
            ->first();

        $closingBalance = $latestLedger ? $latestLedger->closing_balance : 0;

        return response()->json([
            'balance' => $closingBalance,
            'sales' => $distributor->sales ?? []
        ]);
    }


    public function storeDistributorPayment(Request $request)
    {
        $latestLedger = DistributorLedger::where('distributor_id', $request->distributor_id)
            ->latest('id')
            ->first();
        if (!$latestLedger) {
            return redirect()->back()->with('error', 'Ledger record not found for this distributor.');
        }

        $previous_balance = $latestLedger->closing_balance;
        $new_closing_balance = $previous_balance - $request->amount;

        // Update ledger
        $latestLedger->update([
            'closing_balance' => $new_closing_balance,
        ]);

        // Create recovery
        Recovery::create([
            'admin_or_user_id' => auth()->id(),
            'distributor_ledger_id' => $latestLedger->distributor_id,
            'amount_paid' => $request->amount,
            'salesman' => $request->salesman,
            'date' => $request->date,
            'remarks' => $request->detail,
        ]);

        return redirect()->back()->with('success', 'Distributor payment recorded successfully.');
    }
}

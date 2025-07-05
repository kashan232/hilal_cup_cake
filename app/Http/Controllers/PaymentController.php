<?php

namespace App\Http\Controllers;

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

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.payments.customers_payments', compact('customers', 'Salesmans'));
        } else {
            return redirect()->back();
        }
    }


    public function getCustomerBalance($id)
    {
        $customer = Customer::with(['localSales' => function ($q) {
            $q->select('id', 'customer_id', 'Date as sale_date', 'grand_total as total')->latest()->take(10);
        }])->find($id);

        if (!$customer) {
            return response()->json(['balance' => 0, 'sales' => []]);
        }

        $latestLedger = CustomerLedger::where('customer_id', $id)
            ->latest('id')
            ->first();

        $closingBalance = $latestLedger ? $latestLedger->closing_balance : 0;

        return response()->json([
            'balance' => $closingBalance,
            'sales' => $customer->localSales ?? []
        ]);
    }


    public function storeCustomerPayment(Request $request)
    {
        // Validate request if needed here

        $latestLedger = CustomerLedger::where('customer_id', $request->customer_id)
            ->latest('id')
            ->first();

        if (!$latestLedger) {
            return redirect()->back()->with('error', 'Ledger record not found for this customer.');
        }

        $previous_balance = $latestLedger->closing_balance;
        $new_closing_balance = $previous_balance - $request->amount;

        // Update ledger closing balance
        $latestLedger->update([
            'closing_balance' => $new_closing_balance,
        ]);

        // Create new recovery record
        CustomerRecovery::create([
            'admin_or_user_id' => auth()->id(),
            'customer_ledger_id' => $latestLedger->id,  // Important: Link to ledger record ID
            'amount_paid' => $request->amount,
            'salesman' => $request->salesman, // add if applicable
            'date' => $request->date,
            'remarks' => $request->detail, // Use remarks or bank input
        ]);

        return redirect()->back()->with('success', 'Payment recorded successfully.');
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

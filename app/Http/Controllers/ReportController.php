<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerRecovery;
use App\Models\Distributor;
use App\Models\LocalSale;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Sale;
use App\Models\Salesman;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function Distributor_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Distributors = Distributor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            return view('admin_panel.reports.distributor_ledger_record', [
                'Distributors' => $Distributors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchDistributorLedger(Request $request)
    {
        $distributorId = $request->input('distributor_id');

        // Ensure full datetime range
        $startDate = $request->input('start_date') . ' 00:00:00';
        $endDate = $request->input('end_date') . ' 23:59:59';

        // Get distributor ledger record
        $ledger = DB::table('distributor_ledgers')
            ->where('distributor_id', $distributorId)
            ->select('opening_balance', 'previous_balance', 'closing_balance')
            ->first();

        // Filter Recoveries in Date Range
        $recoveries = DB::table('recoveries')
            ->where('distributor_ledger_id', $distributorId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'amount_paid', 'salesman', 'date', 'remarks')
            ->get();

        // Filter Sales in Date Range
        $sales = DB::table('sales')
            ->where('distributor_id', $distributorId)
            ->whereBetween('Date', [$startDate, $endDate])
            ->select('invoice_number', 'Date', 'Booker', 'Saleman', 'grand_total', 'discount_value', 'scheme_value', 'net_amount')
            ->get();

        // ✅ Correct DateTime format for Sale Returns
        $saleReturns = DB::table('sale_returns')
            ->where('sale_type', 'distributor')
            ->where('party_id', $distributorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('invoice_number', 'created_at', 'total_return_amount')
            ->get();

        return response()->json([
            'opening_balance' => $ledger->opening_balance ?? 0,
            'previous_balance' => $ledger->previous_balance ?? 0,
            'closing_balance' => $ledger->closing_balance ?? 0,
            'recoveries' => $recoveries,
            'sales' => $sales,
            'sale_returns' => $saleReturns,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }


    public function vendor_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            return view('admin_panel.reports.vendor_ledger_record', [
                'Vendors' => $Vendors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchvendorLedger(Request $request)
    {
        $vendorId = $request->input('Vendor_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Ledger record including balances
        $ledger = DB::table('vendor_ledgers')
            ->where('vendor_id', $vendorId)
            ->select('opening_balance', 'previous_balance', 'closing_balance')
            ->first();

        $opening_balance = $ledger->opening_balance ?? 0;
        $previous_balance = $ledger->previous_balance ?? 0;
        $closing_balance = $ledger->closing_balance ?? 0;

        // Filter Recoveries in Date Range
        $recoveries = DB::table('vendor_payments')
            ->where('vendor_id', $vendorId)
            ->whereBetween('payment_date', [$startDate, $endDate]) // ✅ Date Range Apply
            ->select('id', 'amount_paid', 'description', 'payment_date')
            ->get();

        // ✅ Fetch Purchases
        $purchases = DB::table('purchases')
            ->where('party_name', $vendorId)
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->select('id', 'invoice_number', 'purchase_date', 'grand_total')
            ->get()
            ->map(function ($purchase) {
                return [
                    'invoice_number' => $purchase->invoice_number,
                    'date' => $purchase->purchase_date,
                    'grand_total' => $purchase->grand_total,
                    'net_amount' => $purchase->grand_total, // assuming no discounts/scheme
                    'salesman' => null, // optional
                ];
            });

        // ✅ Fetch Returns
        $returnsRaw = DB::table('purchase_returns')
            ->where('party_name', $vendorId)
            ->whereBetween('return_date', [$startDate, $endDate])
            ->select('id', 'invoice_number', 'purchase_id', 'return_date', 'return_amount')
            ->get();

        $returns = [];
        foreach ($returnsRaw as $return) {
            $amountArray = json_decode($return->return_amount, true);
            $amountSum = collect($amountArray)->sum();

            $returns[] = [
                'id' => $return->id,
                'invoice_number' => $return->invoice_number,
                'date' => $return->return_date,
                'net_amount' => $amountSum,
            ];
        }
        // ✅ Vendor Builty
        $builties = DB::table('vendor_builties')
            ->where('vendor_id', $vendorId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'date', 'amount', 'description')
            ->get();
        return response()->json([
            'opening_balance' => $opening_balance,
            'previous_balance' => $previous_balance,
            'closing_balance' => $closing_balance,
            'purchases' => $purchases,
            'recoveries' => $recoveries,
            'returns' => $returns,
            'builties' => $builties, // ✅ included here
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }



    public function Customer_Ledger_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            return view('admin_panel.reports.customer_ledger_record', [
                'Customers' => $Customers,
            ]);
        } else {
            return redirect()->back();
        }
    }


    public function fetchCustomerledger(Request $request)
    {
        $CustomerId = $request->input('Customer_id');
        $startDate = $request->input('start_date'); // User selected Start Date
        $endDate = $request->input('end_date');

        // Get ledger record
        $ledger = DB::table('customer_ledgers')
            ->where('customer_id', $CustomerId)
            ->select('opening_balance', 'previous_balance', 'closing_balance')
            ->first();

        // Get recoveries
        $recoveries = DB::table('customer_recoveries')
            ->where('customer_ledger_id', $CustomerId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'amount_paid', 'salesman', 'date', 'remarks')
            ->get();


        // Get Local Sales
        $localSales = DB::table('local_sales')
            ->where('customer_id', $CustomerId)
            ->whereBetween('Date', [$startDate, $endDate])
            ->select(
                'invoice_number',
                'Date',
                'customer_shopname',
                'grand_total',
                'discount_value',
                'scheme_value',
                'net_amount',
                'Saleman'
            )
            ->get();


        $saleReturns = DB::table('sale_returns')
            ->where('sale_type', 'customer')
            ->where('party_id', $CustomerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('invoice_number', 'total_return_amount', 'created_at')
            ->get();

        return response()->json([
            'opening_balance' => $ledger->opening_balance ?? 0,
            'previous_balance' => $ledger->previous_balance ?? 0,
            'closing_balance' => $ledger->closing_balance ?? 0,
            'recoveries' => $recoveries,
            'local_sales' => $localSales, // Local Sales Data
            'sale_returns' => $saleReturns,
            'startDate' => $startDate, // Local Sales Data
            'endDate' => $endDate, // Local Sales Data
        ]);
    }


    public function stock_Record()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $categories = Category::where('admin_or_user_id', $userId)->get();
            return view('admin_panel.reports.stock_Record', [
                'categories' => $categories,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function getItems($subcategory)
    {
        $items = Product::where('sub_category', $subcategory)->select('item_code', 'item_name')->get();
        return response()->json($items);
    }

    public function getItemDetails(Request $request)
    {
        $query = Product::query();

        if ($request->category !== 'all') {
            $query->where('category', $request->category);
        }
        if ($request->subcategory !== 'all') {
            $query->where('sub_category', $request->subcategory);
        }
        if ($request->itemCode !== 'all') {
            $query->where('item_code', $request->itemCode);
        }

        $items = $query->get();

        foreach ($items as $item) {
            // 1️⃣ **Total Purchased Quantity**
            $purchaseData = Purchase::whereJsonContains('item', $item->item_name)->get();
            $totalPurchasedQty = 0;

            foreach ($purchaseData as $purchase) {
                $itemNames = json_decode($purchase->item, true);
                $cartonQtyArray = json_decode($purchase->carton_qty, true);

                if (is_array($itemNames) && is_array($cartonQtyArray)) {
                    foreach ($itemNames as $index => $purchasedItem) {
                        if ($purchasedItem === $item->item_name) {
                            $totalPurchasedQty += isset($cartonQtyArray[$index]) ? intval($cartonQtyArray[$index]) : 0;
                        }
                    }
                }
            }

            // 2️⃣ **Total Distributor Sale Quantity**
            $salesData = Sale::whereJsonContains('item', $item->item_name)->get();
            $totalDistributorSoldQty = 0;

            foreach ($salesData as $sale) {
                $itemNames = json_decode($sale->item, true);
                $cartonQtyArray = json_decode($sale->carton_qty, true);

                if (is_array($itemNames) && is_array($cartonQtyArray)) {
                    foreach ($itemNames as $index => $soldItem) {
                        if ($soldItem === $item->item_name) {
                            $totalDistributorSoldQty += isset($cartonQtyArray[$index]) ? intval($cartonQtyArray[$index]) : 0;
                        }
                    }
                }
            }

            // 3️⃣ **Total Local Sale Quantity**
            $localSalesData = LocalSale::whereJsonContains('item', $item->item_name)->get();
            $totalLocalSoldQty = 0;

            foreach ($localSalesData as $localSale) {
                $itemNames = json_decode($localSale->item, true);
                $cartonQtyArray = json_decode($localSale->carton_qty, true);

                if (is_array($itemNames) && is_array($cartonQtyArray)) {
                    foreach ($itemNames as $index => $soldItem) {
                        if ($soldItem === $item->item_name) {
                            $totalLocalSoldQty += isset($cartonQtyArray[$index]) ? intval($cartonQtyArray[$index]) : 0;
                        }
                    }
                }
            }

            // 4️⃣ **Total Purchase Return Quantity**
            $returnData = DB::table('purchase_returns')->whereJsonContains('item', $item->item_name)->get();
            $totalPurchaseReturnQty = 0;

            foreach ($returnData as $return) {
                $itemNames = json_decode($return->item, true);
                $returnQtyArray = json_decode($return->return_qty, true);

                if (is_array($itemNames) && is_array($returnQtyArray)) {
                    foreach ($itemNames as $index => $returnItem) {
                        if ($returnItem === $item->item_name) {
                            $totalPurchaseReturnQty += isset($returnQtyArray[$index]) ? intval($returnQtyArray[$index]) : 0;
                        }
                    }
                }
            }

            // 5️⃣ **Total Distributor Return Quantity**
            $distributorReturns = DB::table('sale_returns')
                ->where('sale_type', 'distributor')
                ->whereJsonContains('item_names', $item->item_name)
                ->get();

            $totalDistributorReturnQty = 0;

            foreach ($distributorReturns as $return) {
                $itemNames = json_decode($return->item_names, true);
                $cartonQtyArray = json_decode($return->carton_qty, true);

                if (is_array($itemNames) && is_array($cartonQtyArray)) {
                    foreach ($itemNames as $index => $returnItem) {
                        if ($returnItem === $item->item_name) {
                            $totalDistributorReturnQty += isset($cartonQtyArray[$index]) ? intval($cartonQtyArray[$index]) : 0;
                        }
                    }
                }
            }

            // 6️⃣ **Total Local Return Quantity**
            $localReturns = DB::table('sale_returns')
                ->where('sale_type', 'customer')
                ->whereJsonContains('item_names', $item->item_name)
                ->get();

            $totalLocalReturnQty = 0;

            foreach ($localReturns as $return) {
                $itemNames = json_decode($return->item_names, true);
                $cartonQtyArray = json_decode($return->carton_qty, true);

                if (is_array($itemNames) && is_array($cartonQtyArray)) {
                    foreach ($itemNames as $index => $returnItem) {
                        if ($returnItem === $item->item_name) {
                            $totalLocalReturnQty += isset($cartonQtyArray[$index]) ? intval($cartonQtyArray[$index]) : 0;
                        }
                    }
                }
            }

            // ✅ Assign the Correct Values (Separate Counts)
            $item->total_purchased = $totalPurchasedQty;
            $item->total_purchase_return = $totalPurchaseReturnQty;
            $item->total_distributor_sold = $totalDistributorSoldQty;
            $item->total_distributor_return = $totalDistributorReturnQty; // New Line
            $item->total_local_sold = $totalLocalSoldQty;
            $item->total_local_return = $totalLocalReturnQty; // New Line
        }

        return response()->json($items);
    }

    public function date_wise_recovery_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure

            $Salesmans = Salesman::where('admin_or_user_id', $userId)
                ->where('designation', 'Saleman')
                ->get();

            return view('admin_panel.reports.date_wise_recovery_report', [
                'Customers' => $Customers,
                'Salesmans' => $Salesmans,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function getRecoveryReport(Request $request)
    {
        $salesman = $request->salesman;
        $type = $request->type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $recoveries = [];

        if ($type == 'all' || $type == 'distributor') {
            $query = Recovery::whereBetween('date', [$startDate, $endDate]);

            if ($salesman !== 'All') {
                $query->where('salesman', $salesman);
            }

            $distributorRecoveries = $query->get();

            foreach ($distributorRecoveries as $recovery) {
                $distributor = Distributor::find($recovery->distributor_ledger_id);
                $recoveries[] = [
                    'date' => $recovery->date,
                    'party_name' => $distributor->Customer ?? 'N/A',
                    'area' => $distributor->Area ?? 'N/A',
                    'remarks' => $recovery->remarks,
                    'amount_paid' => number_format($recovery->amount_paid),
                    'salesman' => $recovery->salesman ?? '-'
                ];
            }
        }

        if ($type == 'all' || $type == 'customer') {
            $query = CustomerRecovery::whereBetween('date', [$startDate, $endDate]);

            if ($salesman !== 'All') {
                $query->where('salesman', $salesman);
            }

            $customerRecoveries = $query->get();

            foreach ($customerRecoveries as $recovery) {
                $customer = Customer::find($recovery->customer_ledger_id);
                $recoveries[] = [
                    'date' => $recovery->date,
                    'party_name' => $customer->customer_name ?? 'N/A',
                    'area' => $customer->area ?? 'N/A',
                    'remarks' => $recovery->remarks,
                    'amount_paid' => number_format($recovery->amount_paid),
                    'salesman' => $recovery->salesman ?? '-'
                ];
            }
        }

        return response()->json($recoveries);
    }


    public function date_wise_purcahse_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            return view('admin_panel.reports.date_wise_purcahse_report', [
                'Customers' => $Customers,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetch_purchase_report(Request $request)
    {
        $userId = Auth::id();
        $start = $request->start_date;
        $end = $request->end_date;

        $purchases = Purchase::where('admin_or_user_id', $userId)
            ->whereBetween('purchase_date', [$start, $end])
            ->get();

        $report = [];
        $totals = [
            'carton' => 0,
            'pcs' => 0,
            'liter' => 0,
            'net_amount' => 0,
        ];

        foreach ($purchases as $key => $purchase) {
            $items = json_decode($purchase->item ?? '[]');
            $pcs_carton = json_decode($purchase->pcs_carton ?? '[]');
            $carton_qty = json_decode($purchase->carton_qty ?? '[]');
            $pcs = json_decode($purchase->pcs ?? '[]');
            $liter = json_decode($purchase->liter ?? '[]');
            $amounts = json_decode($purchase->amount ?? '[]'); // 👈 Use amount field here

            foreach ($items as $i => $item) {
                $netAmount = floatval($amounts[$i] ?? 0);

                $report[] = [
                    'code' => $key + 1,
                    'date' => \Carbon\Carbon::parse($purchase->purchase_date)->format('d-M-Y'),
                    'item' => $item ?? 'N/A',
                    'carton_packing' => $pcs_carton[$i] ?? 0,
                    'carton_qty' => $carton_qty[$i] ?? 0,
                    'pcs' => $pcs[$i] ?? 0,
                    'liter' => $liter[$i] ?? 0,
                    'net_amount' => $netAmount,
                ];

                $totals['carton'] += floatval($carton_qty[$i] ?? 0);
                $totals['pcs'] += floatval($pcs[$i] ?? 0);
                $totals['liter'] += floatval($liter[$i] ?? 0);
                $totals['net_amount'] += $netAmount;
            }
        }

        return response()->json([
            'report' => $report,
            'totals' => $totals,
        ]);
    }



    public function vendor_wise_purcahse_report()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Vendors = Vendor::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            return view('admin_panel.reports.vendor_wise_purcahse_report', [
                'Vendors' => $Vendors,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchVendorPurchaseReport(Request $request)
    {
        $userId = Auth::id();
        $start = $request->start_date;
        $end = $request->end_date;
        $vendorId = $request->vendor_id;

        if (!$vendorId) {
            return response()->json(['error' => 'Vendor is required'], 422);
        }

        $purchases = Purchase::where('admin_or_user_id', $userId)
            ->where('party_name', $vendorId) // ✅ match by vendor ID in party_name
            ->whereBetween('purchase_date', [$start, $end])
            ->get();

        $report = [];
        $totals = [
            'carton' => 0,
            'pcs' => 0,
            'liter' => 0,
            'net_amount' => 0,
        ];

        foreach ($purchases as $key => $purchase) {
            $items = json_decode($purchase->item ?? '[]');
            $pcs_carton = json_decode($purchase->pcs_carton ?? '[]');
            $carton_qty = json_decode($purchase->carton_qty ?? '[]');
            $pcs = json_decode($purchase->pcs ?? '[]');
            $liter = json_decode($purchase->liter ?? '[]');
            $amounts = json_decode($purchase->amount ?? '[]'); // ✅ new line

            foreach ($items as $i => $item) {
                $netAmount = floatval($amounts[$i] ?? 0);

                $report[] = [
                    'inv_no' => $purchase->invoice_number ?? 'N/A',
                    'date' => \Carbon\Carbon::parse($purchase->purchase_date)->format('d-M-Y'),
                    'item' => $item ?? 'N/A',
                    'carton_packing' => $pcs_carton[$i] ?? 0,
                    'carton_qty' => $carton_qty[$i] ?? 0,
                    'pcs' => $pcs[$i] ?? 0,
                    'liter' => $liter[$i] ?? 0,
                    'net_amount' => $netAmount,
                ];

                $totals['carton'] += floatval($carton_qty[$i] ?? 0);
                $totals['pcs'] += floatval($pcs[$i] ?? 0);
                $totals['liter'] += floatval($liter[$i] ?? 0);
                $totals['net_amount'] += $netAmount;
            }
        }

        return response()->json([
            'report' => $report,
            'totals' => $totals,
        ]);
    }

    public function Area_wise_Customer_payments()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Customers = Customer::where('admin_or_user_id', $userId)->get(); // Adjust according to your database structure
            $cities = City::all(); // Updated the variable name to avoid confusion
            return view('admin_panel.reports.Area_wise_Customer_payments', [
                'Customers' => $Customers,
                'cities' => $cities,
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function fetchReceivableReport(Request $request)
    {
        $request->validate([
            'city' => 'required',
            'area' => 'required|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $city = $request->city;
        $areas = $request->area;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $customers = DB::table('customers')
            ->where('city', $city)
            ->whereIn('area', $areas)
            ->get();

        $reportData = [];

        foreach ($customers as $customer) {
            // Step 1: Get opening balance
            $ledger = DB::table('customer_ledgers')
                ->where('customer_id', $customer->id)
                ->select('opening_balance')
                ->first();

            $openingBalance = $ledger->opening_balance ?? 0;

            // Step 2: Get total sales in selected date range
            $totalSales = DB::table('local_sales')
                ->where('customer_id', $customer->id)
                ->whereBetween('Date', [$startDate, $endDate])
                ->sum('grand_total');

            // Step 3: Get sale returns in selected date range
            $totalReturns = DB::table('sale_returns')
                ->where('sale_type', 'customer')
                ->where('party_id', $customer->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_return_amount');

            // Step 4: Get recoveries in selected date range
            $totalRecoveries = DB::table('customer_recoveries')
                ->where('customer_ledger_id', $customer->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount_paid');

            // Step 5: Final balance
            $balance = ($openingBalance + $totalSales - $totalReturns) - $totalRecoveries;

            $reportData[] = [
                'pcode' => $customer->id,
                'customer_name' => $customer->customer_name,
                'address' => $customer->area,
                'contact' => $customer->phone_number,
                'balance' => round($balance, 2),
                'cash_rec' => '',
                'remarks' => '', // Optional
            ];
        }

        return response()->json([
            'data' => $reportData
        ]);
    }
}

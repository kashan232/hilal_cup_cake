<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\LocalSale;
use App\Models\Product;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocalSaleController extends Controller
{

    public function local_sale()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $Customers = Customer::where('admin_or_user_id', $userId)->get();
            $categories = Category::All();
            // dd($Customers);
            $Staffs = Salesman::where('admin_or_user_id', $userId)->get();
            return view('admin_panel.local_sale.add_sale', compact('Customers', 'categories', 'Staffs'));
        } else {
            return redirect()->back();
        }
    }

    public function store_local_sale(Request $request)
    {
        if (!Auth::id()) {
            return redirect()->back();
        }

        $user = Auth::user();
        $userId = $user->id;
        $invoiceNo = LocalSale::generateSaleInvoiceNo();

        // Validation
        $request->validate([
            'Date' => 'required|date',
            'Booker' => 'required|string',
            'Saleman' => 'required|string',
            'grand_total' => 'required|numeric',
            'discount_value' => 'required|numeric',
            'scheme_value' => 'required|numeric',
            'net_amount' => 'required|numeric',
            'category' => 'required|array',
            'subcategory' => 'required|array',
            'code' => 'required|array',
            'item' => 'required|array',
            'size' => 'required|array',
            'pcs_carton' => 'required|array',
            'carton_qty' => 'required|array',
            'pcs' => 'required|array',
            'liter' => 'required|array',
            'rate' => 'required|array',
            'discount' => 'required|array',
            'amount' => 'required|array',
        ]);

        // Sale Data Save
        $sale = LocalSale::create([
            'admin_or_user_id' => $userId,
            'invoice_number' => $invoiceNo,
            'Date' => $request->Date,
            'customer_id' => $request->customer_id,
            'customer_shopname' => $request->customer_shopname,
            'customer_city' => $request->customer_city,
            'customer_area' => $request->customer_area,
            'customer_address' => $request->customer_address,
            'customer_phone' => $request->customer_phone,
            'Booker' => $request->Booker,
            'Saleman' => $request->Saleman,
            'category' => json_encode($request->category),
            'subcategory' => json_encode($request->subcategory),
            'code' => json_encode($request->code),
            'item' => json_encode($request->item),
            'size' => json_encode($request->size),
            'pcs_carton' => json_encode($request->pcs_carton),
            'carton_qty' => json_encode($request->carton_qty),
            'pcs' => json_encode($request->pcs),
            'liter' => json_encode($request->liter),
            'rate' => json_encode($request->rate),
            'discount' => json_encode($request->discount),
            'amount' => json_encode($request->amount),
            'grand_total' => $request->grand_total,
            'discount_value' => $request->discount_value,
            'scheme_value' => $request->scheme_value,
            'net_amount' => $request->net_amount,
        ]);

        // 🔁 STOCK LOGIC
        foreach ($request->code as $index => $item_code) {
            $cartonQty = (int) $request->carton_qty[$index];
            $pcsSold = (int) $request->pcs[$index];

            if ($user->usertype == 'admin') {
                // 🟢 Admin sale - update from Product stock
                $product = Product::where('item_code', $item_code)->first();
                if ($product) {
                    $totalSold = ($cartonQty * $product->pcs_in_carton) + $pcsSold;

                    $product->carton_quantity -= $cartonQty;
                    $product->initial_stock -= $totalSold;

                    $product->carton_quantity = max($product->carton_quantity, 0);
                    $product->initial_stock = max($product->initial_stock, 0);
                    $product->save();
                }
            }

            if ($user->usertype == 'distributor') {
                // 🔵 Distributor sale - update from DistributorProduct stock
                $distributorProduct = \App\Models\DistributorProduct::where([
                    'distributor_id' => $user->user_id,
                    'code' => $item_code,
                ])->first();

                if ($distributorProduct) {
                    $totalSold = ($cartonQty * $distributorProduct->pcs_carton) + $pcsSold;

                    $distributorProduct->carton_quantity -= $cartonQty;
                    $distributorProduct->pcs -= $pcsSold;
                    $distributorProduct->initial_stock -= $totalSold;

                    $distributorProduct->carton_quantity = max($distributorProduct->carton_quantity, 0);
                    $distributorProduct->pcs = max($distributorProduct->pcs, 0);
                    $distributorProduct->initial_stock = max($distributorProduct->initial_stock, 0);
                    $distributorProduct->save();
                }
            }
        }

        // Ledger Update (same for both)
        $previousBalance = CustomerLedger::where('customer_id', $request->customer_id)
            ->value('closing_balance') ?? 0;

        $newPreviousBalance = $request->net_amount;
        $newClosingBalance = $previousBalance + $request->net_amount;

        CustomerLedger::updateOrCreate(
            ['customer_id' => $request->customer_id],
            [
                'customer_id' => $request->customer_id,
                'admin_or_user_id' => $userId,
                'previous_balance' => $newPreviousBalance,
                'closing_balance' => $newClosingBalance,
            ]
        );

        return redirect()->route('local.sale.invoice', $sale->id)->with('success', 'Sale recorded and stock updated!');
    }





    public function all_local_sale()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $Sales = LocalSale::where('admin_or_user_id', $userId)->with('customer')->get();
            return view('admin_panel.local_sale.all_sale', compact('Sales'));
        } else {
            return redirect()->back();
        }
    }

    public function show_local_sale($id)
    {
        if (Auth::id()) {
            $sale = LocalSale::with('customer')->findOrFail($id);
            // dd($sale);
            return view('admin_panel.local_sale.show_sale', compact('sale'));
        } else {
            return redirect()->back();
        }
    }

    public function localsaleInvoice($id)
    {
        $sale = LocalSale::with('customer')->findOrFail($id);
        return view('admin_panel.local_sale.invoice', compact('sale'));
    }

    public function delete_localsale($id)
    {
        $sale = LocalSale::findOrFail($id);

        $customerId = $sale->customer_id;
        $netAmount = $sale->net_amount;

        // Delete the sale
        $sale->delete();

        // Update distributor ledger
        $ledger = CustomerLedger::where('customer_id', $customerId)->latest()->first();
        if ($ledger) {
            $ledger->closing_balance -= $netAmount;
            $ledger->save();
        }

        return redirect()->back()->with('success', 'Local Sale deleted and Customer ledger updated.');
    }

    public function localsaleEdit($id)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $Customers = Customer::where('admin_or_user_id', $userId)->get();
            $categories = Category::all();  // all categories from DB
            $Staffs = Salesman::where('admin_or_user_id', $userId)->get();
            $original = LocalSale::findOrFail($id);
            return view('admin_panel.local_sale.edit_sale', compact('Customers', 'categories', 'Staffs', 'original'));
        } else {
            return redirect()->back();
        }
    }

    public function localsaleupdate(Request $request, $id)
    {
        if (!Auth::id()) {
            return redirect()->back();
        }

        $user = Auth::user();
        $userId = $user->id;

        $request->validate([
            'Date' => 'required|date',
            'Booker' => 'required|string',
            'Saleman' => 'required|string',
            'grand_total' => 'required|numeric',
            'discount_value' => 'required|numeric',
            'scheme_value' => 'required|numeric',
            'net_amount' => 'required|numeric',
            'category' => 'required|array',
            'subcategory' => 'required|array',
            'code' => 'required|array',
            'item' => 'required|array',
            'size' => 'required|array',
            'pcs_carton' => 'required|array',
            'carton_qty' => 'required|array',
            'pcs' => 'required|array',
            'liter' => 'required|array',
            'rate' => 'required|array',
            'discount' => 'required|array',
            'amount' => 'required|array',
        ]);

        // Fetch existing sale
        $sale = LocalSale::findOrFail($id);

        // STEP 1: Optionally revert previous stock (skipped here — you can add if needed)

        // STEP 2: Update Sale Data
        $sale->update([
            'Date' => $request->Date,
            'customer_id' => $request->customer_id,
            'customer_shopname' => $request->customer_shopname,
            'customer_city' => $request->customer_city,
            'customer_area' => $request->customer_area,
            'customer_address' => $request->customer_address,
            'customer_phone' => $request->customer_phone,
            'Booker' => $request->Booker,
            'Saleman' => $request->Saleman,
            'category' => json_encode($request->category),
            'subcategory' => json_encode($request->subcategory),
            'code' => json_encode($request->code),
            'item' => json_encode($request->item),
            'size' => json_encode($request->size),
            'pcs_carton' => json_encode($request->pcs_carton),
            'carton_qty' => json_encode($request->carton_qty),
            'pcs' => json_encode($request->pcs),
            'liter' => json_encode($request->liter),
            'rate' => json_encode($request->rate),
            'discount' => json_encode($request->discount),
            'amount' => json_encode($request->amount),
            'grand_total' => $request->grand_total,
            'discount_value' => $request->discount_value,
            'scheme_value' => $request->scheme_value,
            'net_amount' => $request->net_amount,
        ]);

        // STEP 3: Update Stock
        foreach ($request->code as $index => $item_code) {
            $cartonQty = (int) $request->carton_qty[$index];
            $pcsSold = (int) $request->pcs[$index];

            if ($user->usertype == 'admin') {
                $product = Product::where('item_code', $item_code)->first();
                if ($product) {
                    $totalSold = ($cartonQty * $product->pcs_in_carton) + $pcsSold;

                    $product->carton_quantity -= $cartonQty;
                    $product->initial_stock -= $totalSold;

                    $product->carton_quantity = max($product->carton_quantity, 0);
                    $product->initial_stock = max($product->initial_stock, 0);
                    $product->save();
                }
            }

            if ($user->usertype == 'distributor') {
                $distributorProduct = \App\Models\DistributorProduct::where([
                    'distributor_id' => $user->user_id,
                    'code' => $item_code,
                ])->first();

                if ($distributorProduct) {
                    $totalSold = ($cartonQty * $distributorProduct->pcs_carton) + $pcsSold;

                    $distributorProduct->carton_quantity -= $cartonQty;
                    $distributorProduct->pcs -= $pcsSold;
                    $distributorProduct->initial_stock -= $totalSold;

                    $distributorProduct->carton_quantity = max($distributorProduct->carton_quantity, 0);
                    $distributorProduct->pcs = max($distributorProduct->pcs, 0);
                    $distributorProduct->initial_stock = max($distributorProduct->initial_stock, 0);
                    $distributorProduct->save();
                }
            }
        }

        // STEP 4: Ledger Update
        $previousBalance = CustomerLedger::where('customer_id', $request->customer_id)->value('closing_balance') ?? 0;
        $newPreviousBalance = $request->net_amount;
        $newClosingBalance = $previousBalance + $request->net_amount;

        CustomerLedger::updateOrCreate(
            ['customer_id' => $request->customer_id],
            [
                'customer_id' => $request->customer_id,
                'admin_or_user_id' => $userId,
                'previous_balance' => $newPreviousBalance,
                'closing_balance' => $newClosingBalance,
                'updated_at' => now(),
            ]
        );

        return redirect()->route('local.sale.invoice', $sale->id)->with('success', 'Sale updated successfully and stock adjusted!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Distributor;
use App\Models\DistributorLedger;
use App\Models\DistributorSaleReturn;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Salesman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function add_sale()
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $Distributors = Distributor::where('admin_or_user_id', $userId)->get();
            $categories = Category::where('admin_or_user_id', $userId)->get();
            $Staffs = Salesman::where('admin_or_user_id', $userId)->get();

            return view('admin_panel.sale.add_sale', compact('Distributors', 'categories', 'Staffs'));
        } else {
            return redirect()->back();
        }
    }

    public function store_sale(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $invoiceNo = Sale::generateSaleInvoiceNo();
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
            $sale = Sale::create([
                'admin_or_user_id' => $userId,
                'invoice_number' => $invoiceNo,
                'Date' => $request->Date,
                'distributor_id' => $request->distributor_id,
                'distributor_city' => $request->distributor_city,
                'distributor_area' => $request->distributor_area,
                'distributor_address' => $request->distributor_address,
                'distributor_phone' => $request->distributor_phone,
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

            // Stock Update Logic
            foreach ($request->code as $index => $item_code) {
                $product = Product::where('item_code', $item_code)->first();
                if ($product) {
                    $cartonQty = (int) $request->carton_qty[$index];
                    $pcsSold = (int) $request->pcs[$index];

                    // Stock Calculation
                    $product->carton_quantity -= $cartonQty;
                    $product->initial_stock -= ($cartonQty * $product->pcs_in_carton) + $pcsSold;

                    // Ensure stock doesn't go negative
                    $product->carton_quantity = max($product->carton_quantity, 0);
                    $product->initial_stock = max($product->initial_stock, 0);

                    $product->save();
                }
            }

            // Fetch previous balance for distributor
            $previousBalance = DistributorLedger::where('distributor_id', $request->distributor_id)
                ->value('closing_balance') ?? 0; // If no previous balance, start from 0

            // Calculate new balances
            $newPreviousBalance = $request->net_amount;
            $newClosingBalance = $previousBalance + $request->net_amount;

            // Update or create distributor ledger
            DistributorLedger::updateOrCreate(
                ['distributor_id' => $request->distributor_id],
                [
                    'distributor_id' => $request->distributor_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $newPreviousBalance,
                    'closing_balance' => $newClosingBalance,
                    'updated_at'        => Carbon::now(),
                ]
            );

            foreach ($request->code as $index => $item_code) {
                $category = $request->category[$index];
                $subcategory = $request->subcategory[$index];
                $item = $request->item[$index];
                $size = $request->size[$index];
                $pcs_carton = (int) $request->pcs_carton[$index];
                $carton_qty = (int) $request->carton_qty[$index];
                $pcs = (int) $request->pcs[$index];
                $liter = (int) $request->liter[$index];
                $rate = $request->rate[$index];

                // Calculate total initial stock in pieces
                $initial_stock = ($carton_qty * $pcs_carton) + $pcs;

                $distributorProduct = \App\Models\DistributorProduct::where([
                    'distributor_id' => $request->distributor_id,
                    'category' => $category,
                    'subcategory' => $subcategory,
                    'code' => $item_code,
                    'item' => $item,
                    'size' => $size,
                ])->first();

                if ($distributorProduct) {
                    // Update existing stock
                    $distributorProduct->carton_quantity += $carton_qty;
                    $distributorProduct->pcs += $pcs;

                    // Update initial stock
                    $distributorProduct->initial_stock += $initial_stock;

                    $distributorProduct->save();
                } else {
                    // Create new stock entry
                    \App\Models\DistributorProduct::create([
                        'distributor_id' => $request->distributor_id,
                        'category' => $category,
                        'subcategory' => $subcategory,
                        'code' => $item_code,
                        'item' => $item,
                        'size' => $size,
                        'price' => $rate,
                        'pcs_carton' => $pcs_carton,
                        'carton_quantity' => $carton_qty,
                        'pcs' => $pcs,
                        'initial_stock' => $initial_stock, // new field
                    ]);
                }
            }



            return redirect()->route('sale.invoice', $sale->id)->with('success', 'Sale recorded successfully and stock updated!');
        } else {
            return redirect()->back();
        }
    }




    public function all_sale()
    {
        if (Auth::id()) {
            $user = Auth::user();
            if ($user->usertype === 'admin') {
                $Sales = Sale::where('admin_or_user_id', $user->id)
                    ->with('distributor')
                    ->get();
            } elseif ($user->usertype === 'distributor') {
                $Sales = Sale::where('distributor_id', $user->user_id)
                    ->with('distributor')
                    ->get();
            } else {
                return redirect()->back()->with('error', 'Unauthorized access');
            }

            return view('admin_panel.sale.all_sale', compact('Sales'));
        } else {
            return redirect()->back();
        }
    }


    public function show_sale($id)
    {
        if (Auth::id()) {
            $sale = Sale::findOrFail($id);

            return view('admin_panel.sale.show_sale', compact('sale'));
        } else {
            return redirect()->back();
        }
    }

    public function saleInvoice($id)
    {
        $sale = Sale::with('distributor')->findOrFail($id);
        return view('admin_panel.sale.invoice', compact('sale'));
    }


    public function saleEdit($id)
    {
        if (Auth::id()) {
            $userId = Auth::id();

            $Distributors = Distributor::where('admin_or_user_id', $userId)->get();
            $categories = Category::where('admin_or_user_id', $userId)->get();  // all categories from DB
            $Staffs = Salesman::where('admin_or_user_id', $userId)->get();
            $original = Sale::findOrFail($id);
            return view('admin_panel.sale.edit_sale', compact('Distributors', 'categories', 'Staffs', 'original'));
        } else {
            return redirect()->back();
        }
    }

    public function saleupdate(Request $request, $id)
    {
        if (Auth::id()) {
            $userId = Auth::id();
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

            // Fetch existing sale record
            $sale = Sale::findOrFail($id);

            // Undo previous stock impact (if you want to be safe)
            // Optional: implement if you want to restore stock quantities based on old sale
            // For simplicity, this is skipped here

            // Update sale data
            $sale->update([
                'Date' => $request->Date,
                'distributor_id' => $request->distributor_id,
                'distributor_city' => $request->distributor_city,
                'distributor_area' => $request->distributor_area,
                'distributor_address' => $request->distributor_address,
                'distributor_phone' => $request->distributor_phone,
                'Booker' => $request->Booker,
                'Saleman' => $request->Saleman,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'code' => $request->code,
                'item' => $request->item,
                'size' => $request->size,
                'pcs_carton' => $request->pcs_carton,
                'carton_qty' => $request->carton_qty,
                'pcs' => $request->pcs,
                'liter' => $request->liter,
                'rate' => $request->rate,
                'discount' => $request->discount,
                'amount' => $request->amount,
                'grand_total' => $request->grand_total,
                'discount_value' => $request->discount_value,
                'scheme_value' => $request->scheme_value,
                'net_amount' => $request->net_amount,
            ]);


            // Update stock quantities based on new data
            foreach ($request->code as $index => $item_code) {
                $product = Product::where('item_code', $item_code)->first();
                if ($product) {
                    $cartonQty = (int) $request->carton_qty[$index];
                    $pcsSold = (int) ($request->pcs[$index] ?? 0);

                    // Adjust stock - **You may want to first revert old sale stock quantities**
                    $product->carton_quantity -= $cartonQty;
                    $product->initial_stock -= ($cartonQty * $product->pcs_in_carton) + $pcsSold;

                    // Prevent negative stock
                    $product->carton_quantity = max($product->carton_quantity, 0);
                    $product->initial_stock = max($product->initial_stock, 0);

                    $product->save();
                }
            }

            // Update distributor ledger
            $previousBalance = DistributorLedger::where('distributor_id', $request->distributor_id)
                ->value('closing_balance') ?? 0;

            $newPreviousBalance = $request->net_amount;
            $newClosingBalance = $previousBalance + $request->net_amount;

            DistributorLedger::updateOrCreate(
                ['distributor_id' => $request->distributor_id],
                [
                    'distributor_id' => $request->distributor_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $newPreviousBalance,
                    'closing_balance' => $newClosingBalance,
                    'updated_at' => now(),
                ]
            );

            return redirect()->route('sale.invoice', $sale->id)->with('success', 'Sale updated successfully and stock updated!');
        } else {
            return redirect()->back();
        }
    }


    public function delete($id)
    {
        $sale = Sale::findOrFail($id);

        $distributorId = $sale->distributor_id;
        $netAmount = $sale->net_amount;

        // Step 1: Decode product-related arrays
        $categories = json_decode($sale->category);
        $subcategories = json_decode($sale->subcategory);
        $codes = json_decode($sale->code);
        $items = json_decode($sale->item);
        $sizes = json_decode($sale->size);
        $cartonQtys = json_decode($sale->carton_qty);
        $pcs = json_decode($sale->pcs);

        // Step 2: Loop through all products in the sale
        for ($i = 0; $i < count($codes); $i++) {
            $product = Product::where('item_code', $codes[$i])
                ->where('item_name', $items[$i])
                ->where('category', $categories[$i])
                ->where('sub_category', $subcategories[$i])
                ->where('size', $sizes[$i])
                ->first();

            if ($product) {
                $cartonQty = (int) $cartonQtys[$i];
                $pcsReturned = (int) $pcs[$i];
                $pcsPerCarton = (int) $product->pcs_in_carton;

                // Restore stock as it was reduced during sale
                $product->carton_quantity += $cartonQty;
                $product->initial_stock += ($cartonQty * $pcsPerCarton) + $pcsReturned;

                $product->save();
            }
        }

        // Step 3: Delete the sale
        $sale->forceDelete();

        // Step 4: Update distributor ledger
        $ledger = DistributorLedger::where('distributor_id', $distributorId)->latest()->first();
        if ($ledger) {
            $ledger->closing_balance -= $netAmount;
            $ledger->save();
        }

        return redirect()->back()->with('success', 'Sale deleted, stock restored, and ledger adjusted.');
    }
}

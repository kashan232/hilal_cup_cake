<?php

namespace App\Http\Controllers;

use App\Models\Salesman;
use App\Models\City;
use App\Models\Area;
use App\Models\Designation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SalesmanController extends Controller
{
    // Salesmen List and Add Salesman
    public function salesmen()
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $user = Auth::user();
        $salesmen = collect(); // default empty collection

        if ($user->usertype === 'admin' || $user->usertype === 'accountant') {
            // Admin can see all salesmen
            $salesmen = Salesman::with(['city', 'area', 'designationRelation'])->get();
        } elseif ($user->usertype === 'orderbooker') {
            // Booker sees area-wise salesmen
            $userAreas = json_decode($user->area, true);

            $allSalesmen = Salesman::where('designation', 'saleman')
                ->with(['city', 'area', 'designationRelation'])
                ->get();

            $salesmen = $allSalesmen->filter(function ($salesman) use ($userAreas) {
                $salesmanAreas = json_decode($salesman->area, true);
                return is_array($salesmanAreas) && !empty(array_intersect($userAreas, $salesmanAreas));
            })->values(); // Reset collection keys

        } elseif ($user->usertype === 'saleman') {
            // Saleman sees area-wise bookers
            $userAreas = json_decode($user->area, true);

            $allBookers = Salesman::where('designation', 'orderbooker')
                ->with(['city', 'area', 'designationRelation'])
                ->get();

            $salesmen = $allBookers->filter(function ($booker) use ($userAreas) {
                $bookerAreas = json_decode($booker->area, true);
                return is_array($bookerAreas) && !empty(array_intersect($userAreas, $bookerAreas));
            })->values(); // Reset collection keys
        }

        $city = City::all();
        $designation = Designation::all();

        return view('admin_panel.salesmen.add_salesmen', compact('salesmen', 'city', 'designation'));
    }



    // Store Salesman (already correctly handles adding new salesman)
    public function store_salesman(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $salesman = Salesman::create([
                'admin_or_user_id' => $userId,
                'name' => $request->name,
                'phone' => $request->phone,
                'designation' => $request->designation,
                'city' => $request->city,
                'area' => json_encode($request->areas),
                'address' => $request->address,
                'salary' => $request->salary,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            User::create([
                'user_id' => $salesman->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'usertype' => $request->designation,
                'city' => $request->city,
                'area' => json_encode($request->areas),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Staff added successfully');
        } else {
            return redirect()->back();
        }
    }

    public function update_salesman(Request $request)
    {
        $salesman_id = $request->input('salesman_id');

        $salesman = Salesman::find($salesman_id);
        if (!$salesman) {
            return redirect()->back()->with('error', 'Salesman not found!');
        }

        // Update Salesman Table
        $salesman->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'designation' => $request->designation,
            'city' => $request->city,
            'area' => json_encode($request->areas), // Make sure 'areas' is an array
            'address' => $request->address,
            'salary' => $request->salary,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        // Update User Table
        $user = User::where('user_id', $salesman_id)->first();
        if ($user) {
            $user->update([
                'name' => $request->name,
                'usertype' => $request->designation,
                'city' => $request->city,
                'area' => json_encode($request->areas),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Salesman updated successfully');
    }


    public function getCities()
    {
        $cities = City::select('id', 'city_name')->get();
        return response()->json($cities);
    }

    public function getAreas(Request $request)

    {
        $areas = Area::where('city_name', $request->city)
            ->select('id', 'area_name')
            ->get();

        return response()->json($areas);
    }

    public function fetchdesignation()
    {
        return response()->json(Designation::all());
    }






    public function toggleStatus(Request $request)
    {
        $salesman = Salesman::find($request->salesman_id);
        if ($salesman) {
            $salesman->status = $request->status;
            $salesman->save();
            return response()->json(['success' => 'Status updated successfully!']);
        }
        return response()->json(['error' => 'Salesman not found!'], 404);
    }


    public function designation()
    {
        if (Auth::id()) {
            $designations = Designation::where('admin_or_user_id', Auth::id())->get();
            return view('admin_panel.salesmen.add_designation', compact('designations'));
        } else {
            return redirect()->back();
        }
    }

    public function store_designation(Request $request)
    {
        if (Auth::id()) {
            Designation::create([
                'admin_or_user_id' => Auth::id(),
                'designation' => $request->designation,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return redirect()->back()->with('success', 'Designation added successfully');
        } else {
            return redirect()->back();
        }
    }



    public function update_designation(Request $request)
    {
        $request->validate([
            'designation_id' => 'required|exists:designations,id',
            'designation' => 'required|string|max:255'
        ]);

        $designation = Designation::findOrFail($request->designation_id);
        $designation->update([
            'designation' => $request->designation
        ]);

        return back()->with('success', 'Designation updated successfully.');
    }

    public function destroy($id)
    {
        $designation = Designation::find($id);

        if (!$designation) {
            return response()->json(['status' => 'error', 'message' => 'Designation not found!']);
        }

        $designation->delete();

        return response()->json(['status' => 'success', 'message' => 'Designation deleted successfully!']);
    }
}

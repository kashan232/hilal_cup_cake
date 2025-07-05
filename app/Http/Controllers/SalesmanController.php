<?php

namespace App\Http\Controllers;

use App\Models\Salesman;
use App\Models\City;
use App\Models\Area;
use App\Models\Designation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesmanController extends Controller
{
    // Salesmen List and Add Salesman
    public function salesmen()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $salesmen = Salesman::where('admin_or_user_id', Auth::id())
                ->with(['city', 'area', 'designationRelation']) // Use the renamed method
                ->get();
            $city = City::all();
            $designation = Designation::all(); // Fetch all designations
            return view('admin_panel.salesmen.add_salesmen', compact('salesmen', 'city', 'designation'));
        } else {
            return redirect()->back();
        }
    }

    // Store Salesman (already correctly handles adding new salesman)
    public function store_salesman(Request $request)
    {
        if (Auth::id()) {
            $userId = Auth::id();
            Salesman::create([
                'admin_or_user_id' => $userId,
                'name' => $request->name,
                'phone' => $request->phone,
                'designation' => $request->designation,
                'city' => $request->city,
                'area' => $request->area,
                'address' => $request->address,
                'salary' => $request->salary,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return redirect()->back()->with('success', 'Salesman added successfully');
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

        $salesman->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'designation' => $request->designation,
            'city' => $request->city,
            'area' => $request->area,
            'address' => $request->address,
            'salary' => $request->salary,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

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

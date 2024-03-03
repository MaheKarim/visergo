<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleColor;
use Illuminate\Http\Request;

class VehicleColorController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Vehicle Colors';
        $vehicleColors = VehicleColor::latest()->searchable(['name'])->paginate(getPaginate());

        return view('admin.vehicle_color.index', compact('pageTitle', 'vehicleColors'));
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'name' => 'required|max:40',
            'color' => 'nullable|regex:/^[a-f0-9]{6}$/i',
        ]);

        if (!$id) {
            $color = new VehicleColor();
            $notification = 'Vehicle color added successfully';
        } else {
            $color = VehicleColor::findOrFail($id);
            $notification = 'Vehicle color updated successfully';
        }

        $color->name = $request->name;
        $color->color = str_replace('#','',$request->color);;
        $color->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return VehicleColor::changeStatus($id);
    }
}

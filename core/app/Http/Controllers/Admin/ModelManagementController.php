<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\VehicleClass;
use App\Models\VehicleColor;
use App\Models\VehicleModel;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class ModelManagementController extends Controller
{
    public function index()
    {
        $pageTitle = 'Model Management';
        $models =  VehicleModel::latest()->with(['vehicleType', 'vehicleClass', 'brand', 'colors'])
            ->searchable(['model'])->paginate(getPaginate());
        $types = VehicleType::active()->get();
        $brands = Brand::active()->get();
        $classes = VehicleClass::active()->get();
        $colors = VehicleColor::active()->get();

        return view('admin.model.index', compact('pageTitle', 'models', 'types', 'brands', 'classes', 'colors'));
    }

    public function store(Request $request, $id = 0)
    {
       $request->validate([
           'vehicle_type_id' => 'required',
           'vehicle_class_id' => 'required',
           'brand_id' => 'required',
           'model' => 'required',
           'year' => 'required',
       ]);

       if (!$id) {
           $model = new VehicleModel();
           $notification = 'Model added successfully';
       } else {
           $model = VehicleModel::findOrFail($id);
           $notification = 'Model updated successfully';
       }

       $model->vehicle_type_id = $request->vehicle_type_id;
       $model->vehicle_class_id = $request->vehicle_class_id;
       $model->brand_id = $request->brand_id;
       $model->model = $request->model;
       $model->year = $request->year;

       $model->save();
       $model->colors()->sync($request->color_id); // sync() method will take care of syncing colors

       $notify[] = ['success', $notification];
       return back()->withNotify($notify);
    }

    public function status($id)
    {
        return VehicleModel::changeStatus($id);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Brands';

        $brands = Brand::latest()->searchable(['name'])->paginate(getPaginate());
        return view('admin.brand.index', compact('pageTitle', 'brands'));
    }

    public function status($id)
    {
        return Brand::changeStatus($id);
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
           'name' => 'required|max:40',
        ]);

        if(!$id) {
            $brand = new Brand();
            $notification = 'Brand added successfully';
        } else {
            $brand = Brand::findOrFail($id);
            $notification = 'Brand updated successfully';
        }
        $brand->name = $request->name;
        $brand->save();
        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }
}

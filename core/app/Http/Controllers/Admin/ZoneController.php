<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;

class ZoneController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Zones';
        $zones     = Zone::searchable(['name'])->orderBy('name')->paginate(getPaginate());
        return view('admin.zone.index', compact('pageTitle', 'zones'));
    }

    public function create($id = 0)
    {
        $pageTitle   = 'Add Zone';
        $zone        = null;
        $coordinates = [];
        if ($id) {
            $zone        = Zone::selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->findOrFail($id);

            $pageTitle   = 'Update Zone - ' . $zone->name;
            $coordinates = json_decode($zone->coordinates->toJson())->coordinates[0];
        }
        return view('admin.zone.create', compact('zone', 'pageTitle', 'coordinates'));
    }

    public function save(Request $request, $id = 0)
    {
        $request->validate([
            'name'            => 'required|max:40|unique:zones,name,' . $id,
            'coordinates'     => 'required',
        ]);

        $coordinates = explode('),(', trim($request->coordinates, '()'));

        $points      = [];
        $lastLat     = 0;
        $lastLng     = 0;

        foreach ($coordinates as $key => $coordinate) {
            $coordinate = explode(',', $coordinate);

            $lat        = trim($coordinate[0]) * 1;
            $lng        = trim($coordinate[1]) * 1;

            if ($key == 0) {
                $lastLat = $lat;
                $lastLng = $lng;
            }

            $points[] = new Point($lat, $lng);
        }

        $points[] = new Point($lastLat, $lastLng);

        if ($id) {
            $notification = 'Zone updated successfully';
            $zone         = Zone::findOrFail($id);
        } else {
            $notification = 'Zone added successfully';
            $zone         = new Zone();
        }

        $zone->name        = $request->name;
        $zone->coordinates = new Polygon([new LineString($points)]);
        $zone->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return Zone::changeStatus($id);
    }
}

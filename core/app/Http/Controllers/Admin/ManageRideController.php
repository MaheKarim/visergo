<?php

namespace App\Http\Controllers\Admin;

use App\Models\Conversation;
use App\Models\Ride;
use App\Http\Controllers\Controller;

class ManageRideController extends Controller
{
    public function allRides()
    {
        $pageTitle = 'Rides ALL';
        $rides = $this->RideData();
        return view('admin.rides.list', compact('pageTitle', 'rides'));
    }

    public function pending()
    {
        $pageTitle = 'Pending Rides';
        $rides = $this->RideData(['initiated']);
        return view('admin.rides.list', compact('pageTitle', 'rides'));
    }

    public function accepted()
    {
        $pageTitle = 'Accepted Rides';
        $rides = $this->RideData(['initiated', 'accepted']);
        return view('admin.rides.list', compact('pageTitle', 'rides'));
    }

    public function running()
    {
        $pageTitle = 'Running Rides';
        $rides = $this->RideData(['ongoingRide']);
        return view('admin.rides.list', compact('pageTitle', 'rides'));
    }

    public function completed()
    {
        $pageTitle = 'Completed Rides';
        $rides = $this->RideData(['completed']);
        return view('admin.rides.list', compact('pageTitle', 'rides'));
    }
    public function canceled()
    {
        $pageTitle = 'Canceling Rides';
        $rides = $this->RideData(['canceled']);
        return view('admin.rides.list', compact('pageTitle', 'rides'));
    }

    protected function RideData($scope = [])
    {
        if (count($scope) > 0) {
            $rides = Ride::query();
            foreach ($scope as $value) {
                $rides = $rides->$value();
            }
        } else {
            $rides = Ride::query();
        }
        return $rides->with(['user', 'destinations'])->searchable(['uuid', 'user:username', 'driver:username'])->latest()->paginate(getPaginate());
    }

    public function detail($id)
    {
        $pageTitle = 'Ride Details';
        $ride = Ride::withCount(['driver', 'destinations'])->findOrFail($id);
        return view('admin.rides.details', compact('pageTitle', 'ride'));
    }


    public function messages($id)
    {
        $ride = Ride::findOrFail($id);
        $messages = Conversation::where('ride_id', $id)->get();
        if (request()->ajax()) {
            return response()->json(view('admin.partials.message', compact('messages'))->render());
        } else {
            $pageTitle = 'Ride Messages';
            return view('admin.rides.message', compact('pageTitle', 'messages', 'ride'));
        }
    }
}

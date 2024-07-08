<?php

namespace App\Services;

use App\Models\Location;
use App\Exceptions\NotFoundException;

class LocationService
{
    public function index()
    {
        return Location::join('routes', 'routes.id', '=', 'locations.route_id')
            ->select('locations.id', 'locations.uuid', 'locations.location_name', 'locations.address', 'locations.store_code', 'locations.phoneNumber', 'routes.route_name', 'routes.id as route_id')
            ->get();
    }

    public function create($data)
    {
        return Location::create($data);
    }

    public function edit($data, $uuid)
    {
        $location = Location::where('uuid', $uuid)->first();
        if (!$location) {
            throw new NotFoundException('Location not found');
        }

        $location->update($data);

        return $location;
    }

    public function show($uuid)
    {
        $location = Location::where('uuid', $uuid)->first();
        if (!$location) {
            throw new NotFoundException('Location not found');
        }

        return $location;
    }

    public function delete($uuid)
    {
        $location = Location::where('uuid', $uuid)->first();
        if (!$location) {
            throw new NotFoundException('Location not found');
        }

        $location->delete();

        return $location;
    }
}

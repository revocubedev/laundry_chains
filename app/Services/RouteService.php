<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Routes;

class RouteService
{
    public function index()
    {
        return Routes::join("users", "users.id", "=", "routes.staff_id")
            ->select("routes.*", "users.fullName")
            ->get();
    }

    public function create($data)
    {
        return Routes::create($data);
    }

    public function edit($data, $uuid)
    {
        $route = Routes::where("uuid", $uuid)->first();
        if (!$route) {
            throw new NotFoundException("Route not found");
        }

        $route->update($data);

        return $route;
    }

    public function show($uuid)
    {
        $route = Routes::where("uuid", $uuid)->first();
        if (!$route) {
            throw new NotFoundException("Route not found");
        }

        return $route;
    }

    public function delete($uuid)
    {
        $route = Routes::where("uuid", $uuid)->first();
        if (!$route) {
            throw new NotFoundException("Route not found");
        }

        $route->delete();
    }
}

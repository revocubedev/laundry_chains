<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;
use Closure;
use Illuminate\Http\Request;

class OwnCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin, Authorization, X-BasePath',
            "Access-Control-Allow-Origin" =>  "*",
            'Access-Control-Allow-Credentials' => 'true',
        ];

        if ($request->getMethod() == 'OPTIONS') {
            return response('OK')->withHeaders($headers);
        }

        $response = $next($request);

        foreach ($headers as $key => $value)
            $response->headers->set($key, $value);

        return $response;
    }
}

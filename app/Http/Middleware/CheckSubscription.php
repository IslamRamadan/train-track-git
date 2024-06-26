<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->user_type == "0") {
            $due_date = Carbon::parse($request->user()->due_date);
        } else {
            $coach_id = $request->user()->coach_client_client->coach_id;
            $client_coach = User::query()->where('id', $coach_id)->first();
            $due_date = Carbon::parse($client_coach->due_date);
        }
        if ($due_date->lt(Carbon::today())) {
            return sendError("Coach subscription expired", 401);
        }
        return $next($request);
    }
}

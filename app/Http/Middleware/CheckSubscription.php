<?php

namespace App\Http\Middleware;

use App\Models\Coach;
use App\Models\RequestInfoLog;
use App\Models\User;
use App\Services\CoachServices;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_Users;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function __construct(protected CoachServices $coachServices, protected DB_Coaches $DB_Coaches, protected DB_Users $DB_Users)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
//        RequestInfoLog::query()->create([
//            "user_id" => $request->user()?->id,
//            "ip" => $request->ip(),
//            "user_agent" => $request->header('User-Agent'),
//            "route" => $request->getPathInfo(),
//            "body" => $request->has('img') || $request->has('image') || $request->has('logo') ? null : $request->getContent(),
//        ]);
     

        if ($request->user()->user_type == "0") {
            $due_date = Carbon::parse($request->user()->due_date);
            $coach_id = $request->user()->id;
            $error_code = 401;

        } else {
            $coach_id = $request->user()->coach_client_client->coach_id;
            $client_coach = User::query()->where('id', $coach_id)->first();
            $due_date = Carbon::parse($client_coach->due_date);
            $error_code = 403;
            return $next($request);
        }
        if ($due_date->lt(Carbon::today())) {
            $coach_package = $this->coachServices->getCoachCurrentPackage($coach_id);
            $amount = $coach_package->amount;
            if ($amount == 0) {
                $new_due_date = Carbon::today()->addMonth()->toDateString();
                $this->DB_Coaches->update_coach_package($coach_id, $coach_package->id);
                $this->DB_Users->update_user_due_date($coach_id, $new_due_date);
            }
            $coach_info = Coach::query()->with('package')->where('user_id', $coach_id)->first();
            $coach_package_amount = $coach_info->package->amount;
            if ($coach_package_amount > 0) {
                return sendError("Coach subscription expired", $error_code);
            }
        }
        return $next($request);
    }
}

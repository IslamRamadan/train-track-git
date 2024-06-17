<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Services\DatabaseServices\DB_Coaches;
use Yajra\DataTables\Facades\DataTables;

class CoachService
{

    public function __construct(protected DB_Coaches $DB_Coaches)
    {
    }
//
//    public function showUser($user, $request)
//    {
//        $user = $this->userRepository->display_user_info($user->id);
//        $rate = "";
//        if ($user->teacher) {
//            $rate = $this->rateRepository->teacher_rate($user->teacher->user_id);
//        }
////
//        if ($request->ajax()) {
//            $data = Request::where($user->teacher ? 'teacher_id' : 'user_id', $user->id)->select('*');
//            return Datatables::of($data)
//                ->addIndexColumn()
//                ->addColumn('student_name', function ($user) {
//                    return '<a href=' . route('users.show', $user->user_id) . ' class="">' . $user->user->name . '</a>';
//                })
//                ->addColumn('teacher_name', function ($user) {
//                    return '<a href=' . route('users.show', $user->user_id) . ' class="">' . $user->teacher->name . '</a>';
//                })
//                ->addColumn('req_status', function ($row) {
//                    if ($row->status == "pending") {
//                        $btn = '
//                            <button type="button" class="btn btn-sm btn-primary">
//                              Pending
//                            </button>
//';
//                    } elseif ($row->status == "in_progress") {
//                        $btn = '
//                            <button type="button" class="btn btn-sm btn-warning">
//                              In_progress
//                            </button>
//';
//                    } elseif ($row->status == "done") {
//                        $btn = '
//                            <button type="button" class="btn btn-sm btn-success">
//                              Done
//                            </button>
//';
//                    } else {
//                        $btn = '
//                            <button type="button" class="btn btn-sm btn-danger">
//                              Rejected
//                            </button>
//';
//                    }
//                    return $btn;
//                })
//                ->rawColumns(['req_status', 'student_name', 'teacher_name'])
//                ->make(true);
//        }
////
//        return view('dashboard.users.show', compact(['user', 'rate']));
//    }
//
//    public function destroy($id)
//    {
//        $this->userRepository->delete_some_user($id);
//        return redirect()->back()->with(['msg' => "User deleted successfully"]);
//    }

    public function index($request)
    {
        if ($request->ajax()) {
            $data = User::where('user_type', "0")->with(['coach', 'coach_client_coach'])->select('*');
            $result = Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    if ($row->coach->status == "1") {
                        $btn = '
                            <button type="button" class="btn btn-sm btn-danger blockCoach" data-id=' . $row->id . ' data-status="0" data-toggle="modal" data-target="#blockCoach">
                              ' . __('translate.Block') . '
                            </button>
';
                    } else {
                        $btn = '
                            <button type="button" class="btn btn-sm btn-primary blockCoach" data-id=' . $row->id . ' data-status="1" data-toggle="modal" data-target="#blockCoach">
                              ' . __('translate.UnBlock') . '
                            </button>
';
                    }
                    return $btn;
                })
                ->addColumn('active_clients', function ($row) {
                    return $row->active_clients;
                })
                ->rawColumns(['active_clients', 'action'])
                ->make();
            return $result;
        }
        return view('dashboard.coaches.index');
    }

    public function block($id, $request)
    {
        $status = $request->status;
        $this->DB_Coaches->change_coach_status($id, $status);
        return redirect()->back()->with(['msg' => "Done successfully"]);
    }

    public function register_form($package)
    {
        return view('users.register', compact('package'));
    }

    public function register($request)
    {
        dd($request->all());
    }
}

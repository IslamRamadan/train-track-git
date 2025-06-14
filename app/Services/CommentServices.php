<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_OneToOneProgram;
use App\Services\DatabaseServices\DB_OtoExerciseComments;
use App\Services\DatabaseServices\DB_Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommentServices
{
    public function __construct(protected ValidationServices     $validationServices,
                                protected DB_OtoExerciseComments $DB_OtoExerciseComments,
                                protected DB_OneToOneProgram     $DB_OneToOneProgram,
                                protected NotificationServices $notificationServices,
                                protected DB_Users             $DB_Users

    )
    {
    }

    public function add($request)
    {
        $this->validationServices->add_comment($request);
        $date = $request->date;
        $oto_program_id = $request->client_program_id;
        $comment = $request->comment;
        $sender = $request->user()->user_type;
        $receiver_type = $request->user()->user_type == "0" ? "Coach" : "Client";
        $user_name = $request->user()->name;
        $current_time = Carbon::now()->toDateTimeString();

        $program = $this->DB_OneToOneProgram->find_oto_program(program_id: $oto_program_id);
        $user_id = $sender == 0 ? $program->client_id : $program->coach_id;
        DB::beginTransaction();
        $create_comment = $this->DB_OtoExerciseComments->create_comment(date: $date, comment: $comment,
            sender: $sender, oto_program_id: $oto_program_id);
        $this->DB_Users->update_user_data($request->user(), ['last_active' => $current_time]);
        DB::commit();

        $payload = [
            'user_id' => strval($request->user()->id),
            'user_type' => $receiver_type,
            'oto_program_id' => strval($oto_program_id),
            'date' => $date,
        ];

        $this->notificationServices->send_notification_to_user(user_id: $user_id, title: "New Comment",
            message: $user_name . " added a new comment for you on " . $date . "!", payload: $payload);


        return sendResponse(['comment_id' => $create_comment->id, 'message' => "Comment added successfully"]);
    }

    public function delete($request)
    {
        $this->validationServices->delete_comment($request);
        $comment_id = $request->comment_id;
        $this->DB_OtoExerciseComments->delete(comment_id: $comment_id);

        return sendResponse(['message' => "Comment deleted successfully"]);
    }
}

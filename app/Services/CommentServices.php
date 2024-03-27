<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_OtoExerciseComments;

class CommentServices
{
    public function __construct(protected ValidationServices     $validationServices,
                                protected DB_OtoExerciseComments $DB_OtoExerciseComments)
    {
    }

    public function add($request)
    {
        $this->validationServices->add_comment($request);
        $date = $request->date;
        $oto_program_id = $request->client_program_id;
        $comment = $request->comment;
        $sender = $request->user()->user_type;

        $this->DB_OtoExerciseComments->create_comment(date: $date, comment: $comment,
            sender: $sender, oto_program_id: $oto_program_id);

        return sendResponse(['message' => "Comment added successfully"]);
    }

    public function delete($request)
    {
        $this->validationServices->delete_comment($request);
        $comment_id = $request->comment_id;
        $this->DB_OtoExerciseComments->delete(comment_id: $comment_id);

        return sendResponse(['message' => "Comment deleted successfully"]);
    }
}

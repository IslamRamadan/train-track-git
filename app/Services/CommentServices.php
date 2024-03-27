<?php

namespace App\Services;

class CommentServices
{
    public function __construct(protected ValidationServices $validationServices)
    {
    }

    public function add($request)
    {
        $this->validationServices->add_comment($request);
        $date = $request->date;
        $client_exercise_id = $request->client_exercise_id;
        $user_id = $request->user()->id;
        dd($request->user());

    }
}

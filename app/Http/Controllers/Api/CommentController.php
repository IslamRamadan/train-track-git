<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommentServices;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(protected CommentServices $commentServices)
    {
    }

    public function add(Request $request)
    {
        return $this->commentServices->add($request);
    }
}

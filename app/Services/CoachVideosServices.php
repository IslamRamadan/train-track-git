<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_CoachVideos;
use Illuminate\Http\JsonResponse;

class CoachVideosServices
{


    public function __construct(protected ValidationServices $validationServices, protected DB_CoachVideos $DB_CoachVideos)
    {
    }

    public function edit($request)
    {
    }

    /**
     * Add coach video
     * @param $request
     * @return JsonResponse
     */
    public function add($request)
    {
        $this->validationServices->add_coach_video($request);
        $coach_id = $request->user()->id;
        $link = $request->link;
        $title = $request->title;

        $this->DB_CoachVideos->add_coach_video($coach_id, $title, $link);

        return sendResponse(["msg" => "Video Added successfully"]);
    }

    /**
     * List coach videos with a search word as optional parameter in the request
     * @param $request
     * @return JsonResponse
     */
    public function list($request)
    {
        $coach_id = $request->user()->id;
        $search = $request->search;
        $videos = $this->DB_CoachVideos->get_coach_videos($coach_id, $search);

        $coach_videos_arr = [];

        foreach ($videos as $video) {
            $single_video['id'] = $video->id;
            $single_video['title'] = $video->title;
            $single_video['link'] = $video->link;
            $coach_videos_arr[] = $single_video;
        }

        return sendResponse($coach_videos_arr);
    }

    public function delete($request)
    {
    }
}

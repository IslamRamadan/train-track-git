<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_CoachVideos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoachVideosServices
{
    public function __construct(protected ValidationServices $validationServices, protected DB_CoachVideos $DB_CoachVideos,
                                protected DB_Coaches         $DB_Coaches)
    {
    }

    public function edit($request)
    {
        $this->validationServices->edit_coach_video($request);
        $video_id = $request->video_id;
        $link = $request->link;
        $title = $request->title;

        $this->DB_CoachVideos->edit_coach_video($video_id, $link, $title);

        return sendResponse(["msg" => "Video updated successfully"]);

    }

    public function delete($request)
    {
        $this->validationServices->delete_coach_video($request);
        $video_id = $request->video_id;
        $video = $this->DB_CoachVideos->find_coach_video($video_id);

        $this->DB_CoachVideos->delete_coach_video($video);
        return sendResponse(["msg" => "Video deleted successfully"]);
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

        return sendResponse(["msg" => "Video added successfully"]);
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
        $perPage = $request->get('per_page', 10); // Default 10 items per page
        // Get paginated videos
        $videos = $this->DB_CoachVideos->get_coach_videos_paginated($coach_id, $search, $perPage);
        // Prepare the response data
        $coach_videos_arr = $videos->items(); // Get the items (videos) from the paginator

        return sendResponse([
            'data' => $coach_videos_arr,
            'pagination' => [
                'current_page' => $videos->currentPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
                'last_page' => $videos->lastPage()
            ]
        ]);
    }


    /**
     * Import coach videos
     * @param $request
     * @return JsonResponse
     */

    public function import(Request $request)
    {
        $coach_id = $request->user()->id;
        $hondaId = "22";
        $coach_info = $this->DB_Coaches->get_coach_info($coach_id);
        if ($coach_info->video_import == "1") {
            return sendError("Video library is already imported", 401);
        }
        $hondaVideos = $this->DB_CoachVideos->get_coach_videos($hondaId);
        DB::beginTransaction();
        foreach ($hondaVideos as $video) {
            $this->DB_CoachVideos->add_coach_video($coach_id, $video->title, $video->link);
        }
        $this->DB_Coaches->update_coach_data($coach_id, ['video_import' => "1"]);
        DB::commit();
        return sendResponse(["msg" => "Video library imported successfully"]);
    }


}

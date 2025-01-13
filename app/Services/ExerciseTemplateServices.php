<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_CoachExerciseTemplates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ExerciseTemplateServices
{
    public function __construct(protected ValidationServices $validationServices, protected DB_CoachExerciseTemplates $DB_ExerciseTemplates)
    {
    }

    public function edit($request)
    {
        $coach_id = $request->user()->id;

        $this->validationServices->edit_exercise_template($request, $coach_id);
        $exercise_template_id = $request->exercise_template_id;
        $description = $request->description;
        $title = $request->title;
        $video_ids = $request->video_ids;

        $template = $this->DB_ExerciseTemplates->find_exercise_template($exercise_template_id);
        DB::beginTransaction();
        $this->DB_ExerciseTemplates->edit_exercise_template($template, $description, $title);
        // Remove old videos
        $template->videos()->delete();
        // Attach videos by creating new records
        if ($video_ids != null) {
            $this->add_template_video($video_ids, $template);
        }

        DB::commit();

        return sendResponse(["msg" => "Exercise template updated successfully"]);

    }

    public function delete($request)
    {
        $this->validationServices->delete_exercise_template($request);
        $exercise_template_id = $request->exercise_template_id;
        $exercise_template = $this->DB_ExerciseTemplates->find_exercise_template($exercise_template_id);
        $exercise_template->videos()->delete();
        $this->DB_ExerciseTemplates->delete_exercise_template($exercise_template);
        return sendResponse(["msg" => "Exercise template deleted successfully"]);
    }
    /**
     * Add coach exercise template
     * @param $request
     * @return JsonResponse
     */
    public function add($request)
    {
        $coach_id = $request->user()->id;
        $this->validationServices->add_exercise_template($request, $coach_id);
        $description = $request->description;
        $title = $request->title;
        $video_ids = $request->video_ids;
        DB::beginTransaction();
        $template = $this->DB_ExerciseTemplates->add_exercise_template($coach_id, $title, $description);
        // Attach videos by creating new records
        if ($video_ids != null) {
            $this->add_template_video($video_ids, $template);
        }


        DB::commit();

        return sendResponse(["msg" => "Exercise template added successfully"]);
    }

    /**
     * List coach exercise templates with a search word as an optional parameter in the request
     * @param $request
     * @return JsonResponse
     */
    public function list($request)
    {
        $coach_id = $request->user()->id;
        $search = $request->search;

        // Fetch exercise templates
        $exercise_templates = $this->DB_ExerciseTemplates->get_exercise_templates($coach_id, $search);

        // Transform templates into desired array structure
        $response = $exercise_templates->map(function ($template) {
            return $this->formatTemplate($template);
        });

        return sendResponse($response->toArray());
    }

    /**
     * Format a single exercise template
     * @param $exercise_template
     * @return array
     */
    private function formatTemplate($exercise_template): array
    {
        return [
            'id' => $exercise_template->id,
            'title' => $exercise_template->title,
            'description' => $exercise_template->description,
            'videos' => $this->formatVideos($exercise_template->videos),
        ];
    }

    /**
     * Format videos associated with an exercise template
     * @param $videos
     * @return array
     */
    private function formatVideos($videos): array
    {
        if ($videos === null) {
            return [];
        }

        return $videos->map(function ($video) {
            return [
                'video_id' => $video->video_id,
                'title' => $video->video->title,
                'link' => $video->video->link,
            ];
        })->toArray();
    }

    /**
     * @param mixed $video_ids
     * @param Model|Builder $template
     * @return void
     */
    private function add_template_video(mixed $video_ids, Model|Builder $template): void
    {
        $videosData = [];
        foreach ($video_ids as $video_id) {
            $videosData[] = ['video_id' => $video_id, 'template_id' => $template->id];
        }
        $template->videos()->createMany($videosData);
    }


}

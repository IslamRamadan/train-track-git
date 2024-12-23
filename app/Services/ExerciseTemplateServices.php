<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_CoachExerciseTemplates;
use Illuminate\Http\JsonResponse;

class ExerciseTemplateServices
{
    public function __construct(protected ValidationServices $validationServices, protected DB_CoachExerciseTemplates $DB_ExerciseTemplates)
    {
    }

    public function edit($request)
    {
        $this->validationServices->edit_exercise_template($request);
        $exercise_template_id = $request->exercise_template_id;
        $description = $request->description;
        $title = $request->title;

        $this->DB_ExerciseTemplates->edit_exercise_template($exercise_template_id, $description, $title);

        return sendResponse(["msg" => "Exercise template updated successfully"]);

    }

    public function delete($request)
    {
        $this->validationServices->delete_exercise_template($request);
        $exercise_template_id = $request->exercise_template_id;
        $this->DB_ExerciseTemplates->delete_exercise_template($exercise_template_id);
        return sendResponse(["msg" => "Exercise template deleted successfully"]);
    }
    /**
     * Add coach exercise template
     * @param $request
     * @return JsonResponse
     */
    public function add($request)
    {
        $this->validationServices->add_exercise_template($request);
        $coach_id = $request->user()->id;
        $description = $request->description;
        $title = $request->title;

        $this->DB_ExerciseTemplates->add_exercise_template($coach_id, $title, $description);

        return sendResponse(["msg" => "Exercise template added successfully"]);
    }

    /**
     * List coach exercise templates with a search word as optional parameter in the request
     * @param $request
     * @return JsonResponse
     */
    public function list($request)
    {
        $coach_id = $request->user()->id;
        $search = $request->search;
        $exercise_templates = $this->DB_ExerciseTemplates->get_exercise_templates($coach_id, $search);

        $coach_exercise_templates_arr = [];

        foreach ($exercise_templates as $exercise_template) {
            $single_exercise_template['id'] = $exercise_template->id;
            $single_exercise_template['title'] = $exercise_template->title;
            $single_exercise_template['description'] = $exercise_template->description;
            $coach_exercise_templates_arr[] = $single_exercise_template;
        }

        return sendResponse($coach_exercise_templates_arr);
    }


}

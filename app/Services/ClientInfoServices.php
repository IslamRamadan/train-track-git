<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Users;
use Illuminate\Http\JsonResponse;

class ClientInfoServices
{
    public function __construct(protected ValidationServices $validationServices, protected DB_Clients $DB_Clients, protected DB_Users $DB_Users)
    {
    }

    /**
     * edit client tag
     * @param $request
     * @return JsonResponse
     */
    public function update($request)
    {
        $this->validationServices->update_client_info($request);
        $client_id = $request->client_id;
        $name = $request->name;
        $tag = $request->tag;
        $weight = $request->weight;
        $height = $request->height;
        $fitness_goal = $request->fitness_goal;
        $label = $request->label;
        $notes = $request->notes;
        $country_id = $request->country_id;
        $gender_id = $request->gender_id;
        $user_info = $this->DB_Users->get_user_info($client_id);
        $this->DB_Users->update_user_data($user_info, ['name' => $name, 'country_id' => $country_id, 'gender_id' => $gender_id]);
        $client_info = $this->DB_Clients->get_client_info($client_id);
        if ($client_info) {
            $this->DB_Clients->update_client_info($client_info, [
                'tag' => $tag,
                'weight' => $weight,
                'height' => $height,
                'fitness_goal' => $fitness_goal,
                'label' => $label,
                'notes' => $notes,
            ]);
        } else {
            $this->DB_Clients->create_client_data([
                'user_id' => $client_id,
                'tag' => $tag,
                'weight' => $weight,
                'height' => $height,
                'fitness_goal' => $fitness_goal,
                'label' => $label,
                'notes' => $notes,
            ]);
        }
        return sendResponse(["msg" => "Client details updated successfully"]);

    }

//    /**
//     * Add client tag
//     * @param $request
//     * @return JsonResponse
//     */
//    public function add($request)
//    {
//        $this->validationServices->add_client_tag($request);
//        $client_id = $request->client_id;
//        $tag = $request->tag;
//
//        $this->DB_Clients->create_client_tag($client_id, $tag);
//
//        return sendResponse(["msg" => "Tag added successfully"]);
//    }

}

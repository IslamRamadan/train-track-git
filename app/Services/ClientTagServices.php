<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Clients;
use Illuminate\Http\JsonResponse;

class ClientTagServices
{
    public function __construct(protected ValidationServices $validationServices, protected DB_Clients $DB_Clients)
    {
    }

    /**
     * edit client tag
     * @param $request
     * @return JsonResponse
     */
    public function update($request)
    {
        $this->validationServices->update_client_tag($request);
        $client_id = $request->client_id;
        $tag = $request->tag;

        $client_info = $this->DB_Clients->get_client_info($client_id);
        if ($client_info) {
            $this->DB_Clients->update_client_tag($client_info, $tag);
        } else {
            $this->DB_Clients->create_client_tag($client_id, $tag);
        }
        return sendResponse(["msg" => "Tag updated successfully"]);

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

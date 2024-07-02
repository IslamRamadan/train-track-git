<?php

namespace App\Services\DatabaseServices;

use App\Models\CoachVideo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DB_CoachVideos
{
    /**
     * @param mixed $coach_id
     * @param mixed $search
     * @return Collection
     */
    public function get_coach_videos(mixed $coach_id, mixed $search): Collection
    {
        return CoachVideo::query()->where('coach_id', $coach_id)
            ->when(!empty($search), function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%');
            })
            ->get();
    }

    /**
     * @param mixed $coach_id
     * @param $title
     * @param $link
     * @return Builder|Model
     */
    public function add_coach_video(mixed $coach_id, $title, $link): Model|Builder
    {
        return CoachVideo::query()->create([
            'coach_id' => $coach_id,
            'title' => $title,
            'link' => $link,
        ]);
    }
}

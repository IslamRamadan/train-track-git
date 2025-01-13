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

    public function verify_coach_id($coach_id, $video_id)
    {
        return CoachVideo::query()->where(['coach_id' => $coach_id, 'id' => $video_id])->exists();
    }

    public function edit_coach_video(mixed $video_id, mixed $link, mixed $title)
    {
        return CoachVideo::query()
            ->where('id', $video_id)
            ->update([
                'link' => $link,
                'title' => $title,
            ]);
    }

    public function find_coach_video($video_id)
    {
        return CoachVideo::query()
            ->with('videos')
            ->where('id', $video_id)->first();
    }

    public function delete_coach_video($video)
    {
        return $video->delete();
    }
}

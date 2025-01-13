<?php

namespace App\Rules;

use App\Models\CoachVideo;
use Illuminate\Contracts\Validation\Rule;

class ValidateCoachVideos implements Rule
{
    protected $coachId;

    public function __construct($coachId)
    {
        $this->coachId = $coachId;
    }

    public function passes($attribute, $value)
    {
        // Ensure $value is an array of IDs
        if (!is_array($value)) {
            return false;
        }

        // Fetch videos with these IDs
        $videoCount = CoachVideo::whereIn('id', $value)
            ->where('coach_id', $this->coachId)
            ->count();

        // Check if the number of fetched videos matches the count of provided IDs
        return $videoCount === count($value);
    }

    public function message()
    {
        return 'One or more videos do not belong to the same coach.';
    }
}

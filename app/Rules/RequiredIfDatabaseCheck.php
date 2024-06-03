<?php

namespace App\Rules;

use App\Models\Program;
use Illuminate\Contracts\Validation\Rule;
class RequiredIfDatabaseCheck implements Rule
{
    public function __construct(protected $conditionValue)
    {
    }

    public function passes($attribute, $value): bool
    {
        dd("Asdsdcsc");
        $conditionMet = Program::query()
            ->where("type", $this->conditionValue)
            ->first();

        if ($conditionMet && empty($value)) {
            return false; // Attribute is required if condition is met
        }

        return true;
    }

    public function message(): string
    {
        return 'The :attribute field is required if the :conditionField is :conditionValue.';
    }
}

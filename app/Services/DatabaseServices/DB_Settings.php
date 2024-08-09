<?php

namespace App\Services\DatabaseServices;

use App\Models\Setting;

class DB_Settings
{
    public function get_version()
    {
        return Setting::query()->first()->version;
    }

    public function update_version($version)
    {
        Setting::query()->update(['version' => $version]);
    }
}

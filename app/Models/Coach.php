<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    use HasFactory;

    protected $fillable = ['gym', 'speciality', 'certificates', 'user_id', 'status', 'package_id', 'video_import',
        'merchant_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
    protected function inTrial(): Attribute
    {
        return Attribute::make(
            get: fn($value) => !(Carbon::parse($this->created_at)->diffInMonths(Carbon::now()) >= 1),
        );
    }
}

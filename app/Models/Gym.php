<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gym extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'logo',
        'owner_id',
        'package_id'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    protected function imagePath(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->logo ? asset('storage/app/public/gym_logos/' . $this->logo) : "",
        );
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'user_type',
        'due_date',
    ];
    protected $appends = ['active_clients'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function coach()
    {
        return $this->hasOne(Coach::class, 'user_id');
    }

    public function client()
    {
        return $this->hasOne(Client::class, 'user_id');
    }

    public function coach_client_client()
    {
        return $this->hasOne(CoachClient::class, 'client_id');
    }

    public function gym_coach()
    {
        return $this->hasOne(GymCoach::class, 'coach_id');
    }

    public function coach_client_coach()
    {
        return $this->hasMany(CoachClient::class, 'coach_id');
    }

    public function getActiveClientsAttribute()
    {
        return CoachClient::where(['coach_id' => $this->id, 'status' => "1"])->distinct('client_id')->count();
    }


    public function client_programs()
    {
        return $this->hasMany(OneToOneProgram::class, 'client_id');
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class, 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(UsersPayment::class, 'coach_id');
    }

    public function notification_token()
    {
        return $this->hasMany(UserNotificationToken::class, 'user_id');
    }

    public function program_clients()
    {
        return $this->hasMany(ProgramClient::class, 'client_id');
    }

    protected function userTypeText(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->user_type == 0 ? "Coach" : "Athlete",
        );
    }

    protected function hasGym(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->gym_coach && $this->gym_coach == "1" ? 1 : 0,
        );
    }

    protected function isGymAdmin(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->gym_coach ? 1 : 0,
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value,
            set: fn($value) => Hash::make($value),
        );
    }
}

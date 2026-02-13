<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymPayment extends Model
{
    use HasFactory;

    protected $fillable = ['gym_id', 'order_id', 'amount', 'status', 'package_id', 'upgrade'];
    protected $appends = ['status_text'];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function getStatusTextAttribute()
    {
        if ($this->status == "1") {
            $payment_status = "UnPaid";
        } elseif ($this->status == "2") {
            $payment_status = "Paid";
        } else {
            $payment_status = "Cancelled";
        }
        return $payment_status;
    }
}

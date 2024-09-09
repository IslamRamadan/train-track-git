<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersPayment extends Model
{
    use HasFactory;

    protected $fillable = ['coach_id', 'order_id', 'amount', 'status', 'first_pay', 'package_id', 'upgrade'];
    protected $appends = ['status_text'];
    public function user()
    {
        return $this->belongsTo(User::class, 'coach_id');
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

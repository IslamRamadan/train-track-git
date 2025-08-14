<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'order_id', 'status', 'amount'];
    protected $appends = ['status_text'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function getStatusTextAttribute()
    {
        if ($this->status == "1") {
            $payment_status = "Pending";
        } elseif ($this->status == "2") {
            $payment_status = "Paid";
        } else {
            $payment_status = "Cancelled";
        }
        return $payment_status;
    }

}

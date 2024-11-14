<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'order';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_code',
        'customer_id',
        'order_status_id',
        'payment_id',
        'total_amount',
        'shipping_address',
        'voucher_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function orderDetails()
{
    return $this->hasMany(OrderDetail::class, 'order_id');
}
    public function customers()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }
    public function orderStatus()
    {
        return $this->belongsTo(Status::class, 'order_status_id');
    }
    public function payment()
{
    return $this->belongsTo(Payment::class,'payment_id');
}
}

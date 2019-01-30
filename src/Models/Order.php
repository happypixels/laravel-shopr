<?php

namespace Happypixels\Shopr\Models;

use Happypixels\Shopr\Money\Formatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'payment_status',
        'delivery_status',
        'token',
        'payment_gateway',
        'transaction_id',
        'transaction_reference',
        'total',
        'sub_total',
        'tax',
        'email',
        'phone',
        'first_name',
        'last_name',
        'address',
        'zipcode',
        'city',
        'country',
    ];

    protected $appends = ['total_formatted', 'sub_total_formatted', 'tax_formatted'];

    public function getTotalFormattedAttribute()
    {
        return (new Formatter)->format($this->total);
    }

    public function getSubTotalFormattedAttribute()
    {
        return (new Formatter)->format($this->sub_total);
    }

    public function getTaxFormattedAttribute()
    {
        return (new Formatter)->format($this->tax);
    }

    public function items()
    {
        return $this->hasMany(app(OrderItem::class));
    }

    public static function generateToken()
    {
        return base64_encode(uniqid().'-'.uniqid().'-'.uniqid());
    }
}

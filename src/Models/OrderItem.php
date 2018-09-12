<?php

namespace Happypixels\Shopr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Happypixels\Shopr\Money\Formatter;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'shoppable_type',
        'shoppable_id',
        'quantity',
        'title',
        'price',
        'options'
    ];

    protected $casts = [
        'options' => 'array',
    ];

    protected $appends = ['price_formatted', 'total_formatted'];

    public function getPriceFormattedAttribute()
    {
        return (new Formatter)->format($this->price);
    }

    public function getTotalFormattedAttribute()
    {
        return (new Formatter)->format($this->price * $this->quantity);
    }

    public function children()
    {
        return $this->hasMany(OrderItem::class, 'parent_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{	
	protected $table = 'order_items';
    protected $fillable = ['amount', 'price', 'rating', 'review', 'reviewed_at'];
    protected $dates = ['reviewed_at'];
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function productSku()
    {
        return $this->belongsTo('App\Models\ProductSku');
    }

    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }
}

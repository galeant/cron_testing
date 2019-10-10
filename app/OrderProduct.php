<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    public $timestamps = false;
    protected $table='cn_order_product';

    protected $fillable=[
        "order_id",
        "product_id",
        "name",
        "model",
        "quantity",
        "price",
        "total",
        "tax",
        "reward"
    ];

    public function order(){
        return $this->belongsTo('App\Order','order_id','order_id');
    }
}

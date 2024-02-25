<?php

namespace App\Models\order;

use App\Models\data\camera;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cameracart extends Model
{
    use HasFactory;
    public $table  = 'cameracart';
    public  $timestamps  =false;
    public $fillable = [
        "id", "type", "item_id", "parent_id" , "number", "order_id"
    ];

    static function store(){
        self::create([
            "type",
            "item_d",
            "number",
            "order_id",
            'parent_id'
        ]);
    }

    static function edit(order $order){
        $cart  = json_decode(request()->cart);
        foreach ($cart as $element) {
            $parent = $element->type == 1 ? Null : $element->parent_id;
            cameracart::create([
                'type' => $element->type,
                'number' => $element->number,
                'item_id' => $element->item_id,
                'parent_id' => $parent,
                'order_id' => $order->id
            ]);
        }
    }


  public function cameras(){
    return $this->belongsTo(camera::class , 'item_id');
  }
  public function order(){
    return $this->belongsTo(order::class , 'order_id');
  }
}
;

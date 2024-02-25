<?php

namespace App\Models\order\print;

use App\Models\data\pictureSize;
use App\Models\order\order;
use App\Models\order\orderDelivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class picture extends Model
{
    use HasFactory;
    public $table  = 'orderpicture';
    public  $timestamps  = false;
    protected $folder = 'picture';
    public $categoryId = 1;
    public $orderTypeId = 3;
    public $fillable = [
        "id",
        "file",
        "picture_size",
        "delivery",
        "number",
    ];

    protected $appends = ['file_name'];

    public static function boot()
    {

        parent::boot();

        static::created(function ($item) {

            $order = order::store($item);
            $order->code =  dechex($order->id);
            $order->save();
            if (request()->delivery == 1)
                orderDelivery::store($order);
            //  setOrderDelivery($order->id , $order->code);


                // notify
                $topic = 'user_'.Auth('sanctum')->user()->id;
                send_to_topic($topic , set_payload('new order' , 'new order added ' , 'new_order' , $order->id , Auth('sanctum')->user()->user_key));
        });

        // static::creating(function($item) {
        //     \Log::info('Item Creating Event:'.$item);
        // });

    }


    public function store()
    {

        $validator = Validator::make(
            request()->all(),

            [
                "file" => "required|mimes:".env('IMAGE_VALID_EXTENSIONS'),
                "picture_size" => 'required',
                "delivery" => 'required',
                "number" => 'required',
                "country" => "required_if:delivery,1",
                'city' => 'required_if:delivery,1',
                'region' => 'required_if:delivery,1',
                'latitude' => 'required_if:delivery,1',
                'longitude' => 'required_if:delivery,1',
                'address' => 'required_if:delivery,1',
                "title" => 'required',
                "description" => 'required',
            ],
            validationMessages()
        );

        if ($validator->fails())
            return apiresponse(false, 200, $validator->errors()->first());

            $file = upload_file("file" , 'picture' , 'order');

        $delivery = isset(request()->delivery) ? request()->delivery : 0;
        $picture = $this->create([
            "file" => $file,
            "picture_size" => request()->picture_size,
            "delivery" => $delivery,
            "number" => request()->number,

        ]);


        return apiresponse(true, 200, __("order.order_stored"));
    }




    public function edit(order $order)
    {
        $model = $this->find($order->order_id);

        // validation
        $validator = Validator::make(
            request()->all(),

            [
                "picture_size" => 'required',
                "delivery" => 'required',
                "number" => 'required',
                "country" => "required_if:delivery,1",
                'city' => 'required_if:delivery,1',
                'region' => 'required_if:delivery,1',
                'latitude' => 'required_if:delivery,1',
                'longitude' => 'required_if:delivery,1',
                'receiver_mobile' => 'required_if:delivery,1',
                'receiver_name' => 'required_if:delivery,1',
                'address' => 'required_if:delivery,1',
                "title" => 'required',
                "description" => 'required',
            ],
            validationMessages()
        );

        if ($validator->fails()) {
            return apiresponse(false, 200, $validator->errors()->first());
        }

        $file = $this->attributes['file'];

        if (isset(request()->file)) {
            $validator = Validator::make(request()->all(), ["file" => "mimes:" . env('IMAGE_VALID_EXTENSIONS')]);

            if ($validator->fails()) {
                return apiresponse(false, 200, $validator->errors()->first());
            }

            if(Storage::disk('order')->exists($this->folder.'/'.$this->attributes['file']));
            {
                Storage::disk('order')->delete($this->folder.'/'.$this->attributes['file']);
            }
            $file = upload_file("file", 'picture', 'order');

        }

        $model->update([
            "file" => $file,
            "picture_size" => request()->picture_size,
            "delivery" => request()->delivery ?? 0,
            "number" => request()->number,
        ]);

        //  update main order
        $order->edit();

        //  update delivery
         orderDelivery::edit($order);

        // return response;
        return apiresponse(true, 200, "updated successfully");
    }


    public function get(order $mainOrder)
    {
        $this->main_order = order::find($mainOrder->id);
        $this->picture_size = pictureSize::find($this->picture_size) ?? null;
        $this->delivey_data = $mainOrder->delivery == null ? null : $mainOrder->delivery->get();

        return $this;;
    }

    public function size()
    {
        return $this->belongsTo(pictureSize::class, 'picture_size');
    }
    public function getFileNameAttribute($value)
    {
        $name = $this->attributes['file'];

        return $name;
    }

    public function getFileAttribute($value)
    {
        return env('ORDERS_STRORAGE_LINK').$this->folder.'/'.$value;
    }


}

<?php

namespace App\Models\order\print;

use App\Models\order\order;
use App\Models\data\paperSize;
use App\Models\data\paperType;
use App\Models\data\printingColor;
use App\Models\data\printingSide;
use App\Models\order\orderDelivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class map extends Model
{
    use HasFactory;
    public $table  = 'ordermap';
    public  $timestamps  = false;
    protected $folder = 'map';

    public $categoryId = 1;
    public $orderTypeId = 2;
    public $fillable = [
        "id",
        "file",
        "paper_size",
        "paper_type",
        "delivery",
        "printing_color",
        "printing_side",
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
                "title" => 'required',
                "description" => 'required',
                "paper_size" => 'required',
                "paper_type" => 'required',
                "delivery" => 'required',
                "printing_color" => 'required',
                "printing_side" => 'required',
                "number" => 'required',
                "country" => "required_if:delivery,1",
                'city' => 'required_if:delivery,1',
                'region' => 'required_if:delivery,1',
                'latitude' => 'required_if:delivery,1',
                'longitude' => 'required_if:delivery,1',
                'address' => 'required_if:delivery,1'
            ],
            validationMessages()
        );

        if ($validator->fails())
            return apiresponse(false, 200, $validator->errors()->first());

         $file = upload_file("file" , 'map' , 'order');


        $delivery = isset(request()->delivery) ? request()->delivery : 0;
        $map = $this->create([
            "file" => $file,
            "paper_size" => request()->paper_size,
            "paper_type" => request()->paper_type,
            "delivery" => $delivery,
            "printing_color" => request()->printing_color,
            "printing_side" => request()->printing_side,
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
                "title" => 'required',
                "description" => 'required',
                "paper_size" => 'required',
                "paper_type" => 'required',
                "delivery" => 'required',
                "printing_color" => 'required',
                "printing_side" => 'required',
                "number" => 'required',
                "country" => "required_if:delivery,1",
                'city' => 'required_if:delivery,1',
                'region' => 'required_if:delivery,1',
                'latitude' => 'required_if:delivery,1',
                'longitude' => 'required_if:delivery,1',
                'receiver_mobile' => 'required_if:delivery,1',
                'receiver_name' => 'required_if:delivery,1',
                'address' => 'required_if:delivery,1'
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
            $file = upload_file("file", 'map', 'order');

        }

        $model->update([
            "file" => $file,
            "paper_size" => request()->paper_size,
            "paper_type" => request()->paper_type,
            "delivery" => request()->delivery ?? 0,
            "printing_color" => request()->printing_color,
            "printing_side" => request()->printing_side,
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
        $this->paper_size = paperSize::find($this->paper_size) ?? null;
        $this->paper_type = paperType::find($this->paper_type) ?? null;
        $this->printing_color = printingColor::find($this->printing_color) ?? null;
        $this->printing_side = printingSide::find($this->printing_side) ?? null;
        $this->delivey_data = $mainOrder->delivery == null ? null : $mainOrder->delivery->get();
        return $this;
    }

    public function size()
    {
        return $this->belongsTo(paperSize::class, 'paper_size');
    }
    public function type()
    {
        return $this->belongsTo(paperType::class, 'paper_type');
    }

    public function color()
    {
        return $this->belongsTo(printingColor::class, 'printing_color');
    }
    public function side()
    {
        return $this->belongsTo(printingSide::class, 'printing_side');
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

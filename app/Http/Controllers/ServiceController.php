<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use App\Models\BannerImage;
use Illuminate\Support\Facades\DB;
class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $input = $request->all();
        if($input['lang'] == 'en'){
            $data = Service::where('status',1)->select('id','service_name','description','image','status')->get();
        }else{
            $data = Service::where('status',1)->select('id','service_name_ar as service_name','description_ar as description','image','status')->get();
        }
        $banners = BannerImage::select('banner_image as url')->get();
        
        foreach($banners as $key => $value){
            $banners[$key]->url = env('APP_URL').'/public/uploads/'.$value->url;
        }
        
        $order['active'] = Order::where('customer_id',$input['customer_id'])->where('status','!=',7)->count();
        $order['completed'] = Order::where('customer_id',$input['customer_id'])->where('status',7)->count();
        
        if($input['lang'] == 'en'){
            $orders = DB::table('orders')
            ->leftjoin('addresses', 'addresses.id', '=', 'orders.address_id')
            ->leftjoin('labels', 'labels.id', '=', 'orders.status')
            ->leftjoin('payment_methods', 'orders.payment_mode', '=', 'payment_methods.id')
            ->select('orders.id','orders.order_id','payment_methods.payment_mode','addresses.address','orders.pickup_date','orders.pickup_time','orders.delivery_date','orders.delivery_time','orders.total','orders.discount','orders.delivery_cost','orders.sub_total','orders.status','orders.items','labels.label_name','labels.image','orders.created_at','orders.updated_at')
            ->where('orders.customer_id',$input['customer_id'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
        }else{
            $orders = DB::table('orders')
            ->leftjoin('addresses', 'addresses.id', '=', 'orders.address_id')
            ->leftjoin('labels', 'labels.id', '=', 'orders.status')
            ->leftjoin('payment_methods', 'orders.payment_mode', '=', 'payment_methods.id')
            ->select('orders.id','orders.order_id','payment_methods.payment_mode_ar as payment_mode','addresses.address','orders.pickup_date','orders.pickup_time','orders.delivery_date','orders.delivery_time','orders.total','orders.discount','orders.delivery_cost','orders.sub_total','orders.status','orders.items','labels.label_name_ar as label_name','labels.image','orders.created_at','orders.updated_at')
            ->where('orders.customer_id',$input['customer_id'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
        }
        foreach($orders as $key => $value){
            if($input['lang'] == 'en'){
                $item = DB::table('order_items')
                        ->leftjoin('services', 'services.id', '=', 'order_items.service_id')
                        ->leftjoin('products', 'products.id', '=', 'order_items.product_id')
                        ->select('order_items.id','order_items.service_id','order_items.product_id','order_items.qty','order_items.price','services.service_name','products.product_name')
                        ->where('order_items.order_id',$value->id)
                        ->get();
                $orders[$key]->items = $item;
            }else{
                $item = DB::table('order_items')
                        ->leftjoin('services', 'services.id', '=', 'order_items.service_id')
                        ->leftjoin('products', 'products.id', '=', 'order_items.product_id')
                        ->select('order_items.id','order_items.service_id','order_items.product_id','order_items.qty','order_items.price','services.service_name_ar as service_name','products.product_name_ar as product_name')
                        ->where('order_items.order_id',$value->id)
                        ->get();
                $orders[$key]->items = $item;
            }
            
        }
        
        return response()->json([
            "result" => $data,
            "banner_images" => $banners,
            "order" => $order,
            "pending_orders" => $orders,
            "pending_orders_count" => count($orders),
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

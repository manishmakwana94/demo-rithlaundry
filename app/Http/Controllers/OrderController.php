<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Product;
use App\Models\CustomerCard;
use App\Models\Service;
use App\Models\Customer;
use App\Models\AppSetting;
use App\Models\CustomerWalletHistory;
use App\Models\OrderItem;
use App\Models\PaymentResponse;
use App\Models\Label;
use Validator;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use App\FcmNotificationMessage;
use Carbon\Carbon;
use Cartalyst\Stripe\Stripe;
class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'address_id' => 'required',
            'delivery_date' => 'required',
            'delivery_time' => 'required',
            'pickup_date' => 'required',
            'pickup_time' => 'required',
            'total' => 'required',
            'discount' => 'required',
            'sub_total' => 'required',
            'promo_id' => 'required',
            'payment_mode' => 'required',
            'payment_response' => 'required',
            'items' => 'required'
        ]);

        if($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['payment_mode'] == 3){
            $wallet = Customer::where('id',$input['customer_id'])->value('wallet');
            if($input['total'] > $wallet){
                return response()->json([
                    "message" => 'Sorry, your wallet balance is low !',
                    "status" => 0
                ]);
            }else{
                $new_wallet = $wallet - $input['total'];
                Customer::where('id',$input['customer_id'])->update([ 'wallet' => $new_wallet ]);
                CustomerWalletHistory::create([ 'customer_id' => $input['customer_id'], 'type' => 2, 'message' => $input['total'].' has been debited from your account','message_ar' => $input['total'].'تم خصمه من حسابك', 'amount' => $input['total'] ]);
            }
        }
        $payment_response = $input['payment_response'];
        unset($input['payment_response']);
        $items = json_decode($input['items'], true);
		$item_names = $items;
		foreach($item_names as $key => $value){
			$item_names[$key]['product_name'] = Product::where('id',$value['product_id'])->value('product_name');
			$item_names[$key]['product_name_ar'] = Product::where('id',$value['product_id'])->value('product_name_ar');
			$item_names[$key]['service_name'] = Service::where('id',$value['service_id'])->value('service_name');
			$item_names[$key]['service_name_ar'] = Service::where('id',$value['service_id'])->value('service_name_ar');
		}
		$input['items'] = json_encode($item_names, JSON_UNESCAPED_SLASHES);
		$input['items'] = preg_replace('/\\\"/',"\"", $input['items']);
    $input['delivery_date'] =Carbon::createFromFormat('d/F/Y', $input['delivery_date'])->format('Y-m-d');
    $input['pickup_date'] =Carbon::createFromFormat('d/F/Y', $input['pickup_date'])->format('Y-m-d');
    
        $order = Order::create($input);
        $order_id = str_pad($order->id, 5, "0", STR_PAD_LEFT);
        Order::where('id',$order->id)->update([ 'order_id'=>$order_id]);
        if (is_object($order)) {
            foreach ($items as $key => $value) {
                $value['order_id'] = $order->id;
                OrderItem::create($value);
            }
            if($input['payment_mode'] != 1){
                PaymentResponse::where('payment_response',$payment_response)->update(['order_id' => $order->id]);
            }
            
            return response()->json([
                "message" => 'Order Placed Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
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

    public function getOrders(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        if($input['lang'] == 'en'){
            $orders = DB::table('orders')
            ->leftjoin('addresses', 'addresses.id', '=', 'orders.address_id')
            ->leftjoin('labels', 'labels.id', '=', 'orders.status')
            ->leftjoin('payment_methods', 'orders.payment_mode', '=', 'payment_methods.id')
            ->select('orders.id','orders.order_id','payment_methods.payment_mode','addresses.address','addresses.door_no','orders.pickup_date','orders.pickup_time','orders.delivery_date','orders.delivery_time','orders.total','orders.discount','orders.delivery_cost','orders.sub_total','orders.status','orders.items','labels.label_name','labels.image','orders.created_at','orders.updated_at')
            ->where('orders.customer_id',$input['customer_id'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
        }else{
            $orders = DB::table('orders')
            ->leftjoin('addresses', 'addresses.id', '=', 'orders.address_id')
            ->leftjoin('labels', 'labels.id', '=', 'orders.status')
            ->leftjoin('payment_methods', 'orders.payment_mode', '=', 'payment_methods.id')
            ->select('orders.id','orders.order_id','payment_methods.payment_mode_ar as payment_mode','addresses.address','addresses.door_no','orders.pickup_date','orders.pickup_time','orders.delivery_date','orders.delivery_time','orders.total','orders.discount','orders.delivery_cost','orders.sub_total','orders.status','orders.items','labels.label_name_ar as label_name','labels.image','orders.created_at','orders.updated_at')
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
        if ($orders) {
            return response()->json([
                "result" => $orders,
                "count" => count($orders),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }

    public function sendError($message) {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('',$message);
        $response['status'] = "0";
        return response()->json($response, 200);
    }

    public function order_status_change(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'status' => 'required'
        ]);

        if($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $order = Order::where('id',$input['order_id'])->first();
        if(is_object($order)){
            $old_label = Label::where('id',$input['status'])->first();
            Order::where('id',$input['order_id'])->update([ 'status' => $old_label->id ]);
            $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
            $database = $factory->createDatabase();
            $newPost = $database
            ->getReference('delivery_partners/'.$order->delivered_by.'/orders/'.$order->id.'/status')
            ->set($old_label->id);
            $newPost = $database
            ->getReference('delivery_partners/'.$order->delivered_by.'/orders/'.$order->id.'/status_name')
                ->set($old_label->label_name);

            if($input['status'] != 7){
                $new_label = Label::where('id',$input['status']+1)->first();
                $newPost = $database
                ->getReference('delivery_partners/'.$order->delivered_by.'/orders/'.$order->id.'/new_status')
                ->set($new_label->id);
                $newPost = $database
                ->getReference('delivery_partners/'.$order->delivered_by.'/orders/'.$order->id.'/new_status_name')
                ->set($new_label->label_for_delivery_boy);
            }
           
            //fcm msg
            $order_status = Order::where('id',$input['order_id'])->value('status');
            $message = DB::table('fcm_notification_messages')->where('id',$order_status)->first();
            $customer_token = Customer::where('id',$order->customer_id)->value('fcm_token');
            $this->send_fcm($message->customer_title, $message->customer_description, $customer_token);
            
            $response['message'] = "Success";
            $response['status'] = 1;
            return response()->json($response, 200);
        }else{
            $response['message'] = "Invalid order id";
            $response['status'] = 0;
            return response()->json($response, 200);
        }

    }
    
    public function get_labels(){
        $data['labels_en'] = Label::pluck('label_name')->toArray();
        $data['labels_ar'] = Label::pluck('label_name_ar')->toArray();
        //$data = array_merge($data_en, $data_ar);
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }
	
	public function check_order_count(Request $request){
		
		$input = $request->all();
        $validator = Validator::make($input, [
            'pickup_date' => 'required',
            'pickup_time' => 'required',
			'delivery_date' => 'required',
            'delivery_time' => 'required'
        ]);
		
		if($validator->fails()) {
            return $this->sendError($validator->errors());
        }
		
		$max_order_per_hour = AppSetting::where('id',1)->value('max_order_per_hour');
		
		$input['delivery_date'] = date('Y-m-d', strtotime($input['delivery_date']));
        $input['pickup_date'] = date('Y-m-d', strtotime($input['pickup_date']));
		
		$max_order_delivery_date = Order::where('delivery_date',$input['delivery_date'])->where('delivery_time',$input['delivery_time'])->count();
		$max_order_pickup_date = Order::where('pickup_date',$input['pickup_date'])->where('pickup_time',$input['pickup_time'])->count();
		
		if($max_order_pickup_date >= $max_order_per_hour){
			return response()->json([
				"message" => 'maximum orders reached for selected pickup slot',
				"status" => 0
			]);
		}else if($max_order_delivery_date >= $max_order_per_hour){
			return response()->json([
				"message" => 'maximum orders reached for selected delivery slot',
				"status" => 0
			]);
		}
		
		return response()->json([
			"message" => 'Success',
			"status" => 1
		]);
	}
	
	public function check_cards(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'amount' => 'required'
        ]);
        
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data =  CustomerCard::where('customer_id',$input['customer_id'])->where('is_default',1)->first();

        if(is_object($data)){
            $customer = Customer::where('id',$input['customer_id'])->first();
            $stripe = new Stripe();
            $charge = $stripe->charges()->create([
                'customer' => $customer->stripe_token,
                'currency' => AppSetting::where('id',1)->value('default_currency'),
                'amount'   => $input['amount'],
            ]);
        
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
            "message" => 'Sorry no cards found',
            "status" => 0
        ]);
        }
    }
    
}

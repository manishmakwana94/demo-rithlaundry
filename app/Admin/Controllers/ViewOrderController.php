<?php

namespace App\Admin\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Address;
use App\Models\DeliveryBoy;
use App\Models\AppSetting;
use App\Models\Label;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;
class ViewOrderController extends Controller
{
    public function index($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('Order Details');
            $content->description('View');
            $order_details = Order::where('id',$id)->first();
            $app_setting = AppSetting::first();
            $data = array();
            $data['order_id'] = $order_details->order_id;
            $data['customer_name'] = Customer::where('id',$order_details->customer_id)->value('customer_name');
            $data['customer_phone'] = Customer::where('id',$order_details->customer_id)->value('phone_number');
            $data['address'] = Address::where('id',$order_details->address_id)->value('address');
            $data['pickup_date'] = date('d M-Y',strtotime($order_details->pickup_date));
            $data['delivery_date'] = date('d M-Y',strtotime($order_details->delivery_date));
			$data['pickup_time'] = $order_details->pickup_time;
            $data['delivery_time'] = $order_details->delivery_time;
            $data['collected_by'] = (DeliveryBoy::where('id',$order_details->collected_by)->value('delivery_boy_name') != '' ) ? DeliveryBoy::where('id',$order_details->collected_by)->value('delivery_boy_name') : "---" ;
            $data['delivered_by'] = (DeliveryBoy::where('id',$order_details->delivered_by)->value('delivery_boy_name') != '' ) ? DeliveryBoy::where('id',$order_details->delivered_by)->value('delivery_boy_name') : "---" ;
            $data['payment_mode'] = PaymentMethod::where('id',$order_details->payment_mode)->value('payment_mode');
            $data['sub_total'] = $app_setting->default_currency.$order_details->sub_total;
            $data['discount'] =  $app_setting->default_currency.$order_details->discount;
            $data['total'] =  $app_setting->default_currency.$order_details->total;
            $data['status'] =  Label::where('id',$order_details->status)->value('label_name');
            $order_items = DB::table('order_items')
            ->join('services', 'services.id', '=', 'order_items.service_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('services.service_name','products.product_name','order_items.qty')
            ->where('order_items.order_id',$order_details->id)
            ->orderBy('order_items.created_at', 'asc')
            ->get();
            $data['order_items'] = $order_items;
            $content->body(view('admin.view_orders', $data));
        });

    }
}

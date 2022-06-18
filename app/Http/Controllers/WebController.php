<?php
 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMode;
use App\Models\Service;
use App\Models\CustomerCard;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\AppSetting;
use App\Models\Product;
use App\Models\FareManagement;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\PrivacyPolicy;
use Redirect;
use Auth;
use Validator;
use Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use DateTime;
use DateInterval;
use DatePeriod;  
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Cartalyst\Stripe\Stripe;
use Mail;
class WebController extends Controller
{
    public function index(){
        $services = Service::where('status',1)->get();
        return view('home',[ 'data' => $services]);
    }

    public function services(){
        $services = Service::where('status',1)->get();
        return view('services',[ 'data' => $services ]);
    }

    public function pricing(){
        $services = Service::where('status',1)->get();
        $currency = AppSetting::where('id',1)->value('default_currency');
        $data = array();
        foreach ($services as $key => $value) {
           $data[$value->service_name] = $this->get_products($value->id);
        }
        return view('pricing',[ 'data' => $data, 'currency' => $currency ]);
    }

    public function pricing_mobile(){
        $services = Service::where('status',1)->get();
        $currency = AppSetting::where('id',1)->value('default_currency');
        $data = array();
        foreach ($services as $key => $value) {
           $data[$value->service_name] = $this->get_products($value->id);
        }
        return view('pricing_mobile',[ 'data' => $data, 'currency' => $currency ]);
    }

    public function get_products($service_id)
    {  
        $products = array();

        $all_categories = Category::select('id','service_id')->where('status',1)->get();
        $find_ids = array();
        foreach ($all_categories as $key => $value) {
            if(is_array($value->service_id) && in_array($service_id, $value->service_id)){
                array_push($find_ids, $value->id);
            }
        }
        $categories = Category::select('id','category_name')->where('status',1)->whereIn('id',$find_ids)->get();
        
        foreach ($categories as $key => $value) {
            $categories[$key]['product'] = Product::where('status',1)->where('category_id',$value->id)->get();
            foreach ( $categories[$key]['product'] as $key1 => $value1) {
                $categories[$key]['product'][$key1]['price'] = FareManagement::where('service_id',$service_id)->where('category_id',$value->id)->where('product_id',$value1->id)->value('amount');
                array_push($products,$categories[$key]['product'][$key1]);
            }
        }

       return $products;

    }

    public function faq(){
        $faq = Faq::where('status',1)->get();
        return view('faq',[ 'data' => $faq]);
    }
    
    public function privacy_policy(){
        $privacy_policy = PrivacyPolicy::where('status',1)->get();
        return view('privacy_policy',[ 'data' => $privacy_policy]);
    }

    public function products($service_id)
    {
   
        $all_categories = Category::select('id','service_id')->where('status',1)->get();
        $currency = AppSetting::where('id',1)->value('default_currency');
        $find_ids = array();
        foreach($all_categories as $key => $value) {
            if(is_array($value->service_id) && in_array($service_id, $value->service_id)){
                array_push($find_ids, $value->id);
            }
        }
        $categories = Category::select('id','category_name')->where('status',1)->whereIn('id',$find_ids)->get();
        
        foreach ($categories as $key => $value) {
            $categories[$key]['product'] = Product::where('status',1)->where('category_id',$value->id)->get();
            foreach ( $categories[$key]['product'] as $key1 => $value1) {
                $categories[$key]['product'][$key1]['price'] = FareManagement::where('service_id',$service_id)->where('category_id',$value->id)->where('product_id',$value1->id)->value('amount');
            }
        }
 
        return view('products',[ 'data' => $categories, 'currency' => $currency, 'service_id' => $service_id]);

    }

    public function profile($page)
    {
       
        $profile = Customer::where('id',Auth::user()->id)->first();
        $currency = AppSetting::where('id',1)->value('default_currency');
        $addresses = Address::where('customer_id',Auth::user()->id)->get();
        
        $my_orders = DB::table('orders')
            ->join('addresses', 'addresses.id', '=', 'orders.address_id')
            ->join('labels', 'labels.id', '=', 'orders.status')
            ->select('orders.id','orders.order_id','orders.payment_mode','addresses.address','orders.delivery_date','orders.total','orders.discount','orders.sub_total','orders.status','orders.items','labels.label_name','orders.created_at','orders.updated_at')
            ->where('orders.customer_id',Auth::user()->id) 
            ->orderBy('orders.created_at', 'desc')
            ->get();
        return view('profile',[ 'profile' => $profile, 'my_orders' => $my_orders , 'currency' => $currency, 'addresses' => $addresses, 'page' => $page ]);
    }
    
    public function my_cards(){
        $my_cards = CustomerCard::where('customer_id',Auth::user()->id)->get();
        return view('cards',[ 'my_cards' => $my_cards ]);
    }
    public function show_order_detail($order_id)
    {
        $currency = AppSetting::where('id', 1)->value('default_currency');
        $addresses = Address::where('customer_id', Auth::user()->id)
            ->get();
        $my_orders = DB::table('orders')
            ->leftjoin('addresses', 'addresses.id', '=', 'orders.address_id')
            ->leftjoin('payment_methods', 'payment_methods.id', '=', 'orders.payment_mode')
            ->join('labels', 'labels.id', '=', 'orders.status')
            ->select('orders.id','orders.order_id','addresses.address as address','orders.pickup_date','orders.pickup_time','orders.delivery_date','orders.delivery_cost','orders.delivery_time','orders.total','orders.discount','orders.sub_total','orders.status','orders.items','labels.label_name','orders.created_at','orders.updated_at','payment_methods.payment_mode as payment_mode')
            ->where('orders.id',$order_id) 
            ->first();
        
        return view('order_detail', ['my_orders' => $my_orders, 'currency' => $currency, 'addresses' => $addresses]);
    }

    public function update_date(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'delivery_date' => 'required',
            'delivery_time' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $order = Order::where('id',$input['order_id'])->first();
        $serviceAccount = ServiceAccount::fromJsonFile(config_path().'/'.env('FIREBASE_FILE'));
        $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->withDatabaseUri(env('FIREBASE_DB'))
        ->create();
        $database = $firebase->getDatabase();
        $database->getReference('delivery_partners/'.$order->delivered_by.'/orders/'.$order->id.'/delivery_date')
            ->set($input['delivery_date']);
        $database->getReference('delivery_partners/'.$order->delivered_by.'/orders/'.$order->id.'/delivery_time')
            ->set($input['delivery_time']);
        $update = Order::where('id',$input['order_id'])->update([ 'delivery_date' => $input['delivery_date'],'delivery_time' => $input['delivery_time'] ]);

        return 1;
      
    }

    public function showLogin()
    {
        return view('login');
    }

    public function showRegister()
    {
        return view('register');
    }

    public function doLogout()
    {
        Auth::logout(); // logging out user
         Session::put('cart', []);
            Session::put('promo', '');
            Session::put('sub_total', 0);
        return Redirect::to('login'); // redirection to login screen
    }

    public function doRegister(Request $request)
    {   
        $input = $request->all();
        $validator = Validator::make($input, [
          'customer_name' => 'required', // make sure the email is an actual email
          'phone_number' => 'required|numeric|digits_between:9,20|unique:customers,phone_number',
          'email' => 'required|email|regex:/^[a-zA-Z]{1}/|unique:customers,email',
          'password' => 'required'
        ]);
        $w_password =  $input['password'];
        if ($validator->fails()) {
            return Redirect::to('register')->withErrors($validator)
            ->withInput(Input::except('password'));
        }
        else
        {
            $options = [
                'cost' => 12,
            ];
            $password = $input["password"];
            $input['password'] = password_hash($password, PASSWORD_DEFAULT, $options);
            $input['status'] = 1;
            $stripe = new Stripe();
            $stripe_token = $stripe->customers()->create([
                'email' => $input['email'],
            ]);
            $input['stripe_token'] = $stripe_token['id'];
            $customer = Customer::create($input);

            if(is_object($customer)) {
                //$this->register_mail($customer->id,$w_password);
                $userdata = array(
                  'email' => $input['email'],
                  'password' => $password
                );
                // attempt to do the login
                if (Auth::attempt($userdata))
                {
                  return Redirect::to('profile/orders');
                }
                else
                {
                  return view('login');
                }
            }
        }
    }

    public function doLogin(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
          'email' => 'required|email', // make sure the email is an actual email
          'password' => 'required'
        ]);
        if ($validator->fails()) {
            return Redirect::to('login')->withErrors($validator)
            ->withInput(Input::except('password'));
        }
        else
        {
            $userdata = array(
              'email' => $input['email'],
              'password' => $input['password']
            );
            // attempt to do the login
            if (Auth::attempt($userdata))
            {
              return Redirect::to('profile/orders');
            }
            else
            {
              return view('login',['message' => 'Invalid email or password']);
            }
        }
    }

    public function cart()
    {
       $payment_modes = PaymentMethod::where('status',1)->get();
       $promo_codes = PromoCode::where('status',1)->get();
       $currency = AppSetting::where('id',1)->value('default_currency');
       $delivery_cost = AppSetting::where('id',1)->value('delivery_cost');
       $addresses = Address::where('customer_id',Auth::user()->id)->get();
       $sub_total = Session::get('sub_total', 0);
       $promo = Session::get('promo', '');
       $total = 0;
       $promo_amount = 0;
       $error = '';
       $promo_id = 0;
       $order_count = Order::where('customer_id',Auth::id())->count();
       $order_count = 1;
     
        if($promo == ''){
            $total = $sub_total + $delivery_cost;
        }else{
            $promo_details = PromoCode::where('promo_code',$promo)->first();
            $promo_id = $promo_details->id;
            if($promo_details->minimum_amount == 0){
                if($promo_details->promo_type == 1){
                    $discount = $sub_total - $promo_details->discount;
                    if($discount > 1){
                        $promo_amount = $promo_details->discount;
                        $total = $discount + $delivery_cost;
                    }else{
                        $promo_amount = 0;
                        $total = $sub_total + $delivery_cost;
                        $error = 'Sorry this promo code not applicable';
                    }
                }else{
                    $discount = ($promo_details->discount /100) * $sub_total;
                    $promo_amount = $discount;
                    $total = ($sub_total - $discount) + $delivery_cost;
                }
            }else{
                if($sub_total >= $promo_details->minimum_amount){
                    if($promo_details->promo_type == 1){
                      $discount = $sub_total - $discount;
                      if($discount >= 1){
                        $promo_amount = $promo_details->discount;
                        $total = $discount + $delivery_cost;
                      }else{
                        $promo = '';
                        $error = 'Sorry this promo code not applicable';
                      }
                    }else{
                      $discount = ($promo_details->discount / 100) * $sub_total;
                      $promo_amount = $discount;
                      $total = ($sub_total - $discount) + $delivery_cost;
                    }
                }else{
                    $promo_amount = 0;
                    $total = $sub_total + $delivery_cost;
                    $error = 'Sorry you are not eligible for this offer';
                    $promo = '';
                }
            }
       }
       // dump($payment_modes);
       // die();
       return view('cart',[ 'currency' => $currency, 'sub_total' => $sub_total, 'delivery_cost' => $delivery_cost, 'total'=>$total, 'promo_codes'=>$promo_codes, 'promo_amount' => $promo_amount, 'error' => $error, 'promo' => $promo, 'promo_id' => $promo_id, 'addresses' => $addresses, 'payment_modes' => $payment_modes ]);
    }

    public function add_to_cart(Request $request)
    {
        $product = Product::where('id',$request->product_id)->first();
        $service_name = Service::where('id',$request->service_id)->value('service_name');
        $data = array();
        $cart = Session::get('cart', []);
        if($request->qty > 0){
            $data['service_id'] = $request->service_id;
            $data['product_id'] = $request->product_id;
            $data['product_name'] = $product->product_name;
            $data['service_name'] = $service_name;
            $data['image'] = $product->image;
            $data['qty'] = $request->qty;
            $data['price'] = $request->qty * $request->price;
            $cart[$request->service_id.'-'.$request->product_id] = $data;
        }else{
            unset($cart[$request->service_id.'-'.$request->product_id]);
        }
        Session::put('cart', $cart);
        $sub_total = 0;
        foreach ($cart as $key => $value) {
            $sub_total = $sub_total + $value['price'];
        }
        Session::put('sub_total', $sub_total);
        $cart_count = count(Session::get('cart', []));

        return $cart_count;
        
    }

    public function apply_promo(Request $request)
    {
        Session::put('promo', $request->promo_code);
        $data = $this->coupon_apply_ajax();
        return json_encode($data);
    }

    public function remove_promo(Request $request)
    {
        Session::put('promo','');
        $data = $this->coupon_apply_ajax();
        return json_encode($data);
        //return 1;
    }

   
    public function checkout(Request $request)
    {
        $input = $request->all();
        
        $input['pickup_date'] = date('Y-m-d', strtotime($input['pickup_date']));
        $input['delivery_date'] = date('Y-m-d', strtotime($input['delivery_date']));
        $input['delivery_cost'] = AppSetting::where('id',1)->value('delivery_cost');
        /*if($input['payment_mode'] == 2){
            Session::put('total_amount', $input['total']);
            $input['order_ref_id'] = $input['customer_id'].'/'.time();
            Session::put('order_details', $input);
            return 0;
        }*/
        $items_data = array();
        $i=0;
        foreach (Session::get('cart', []) as $key => $value) {
            $items_data[$i] = $value;
            $i++;
        }
        $input['items'] = json_encode($items_data,true);

        $items = json_decode($input['items'], true);
        // dump($input); die();
        $order = Order::create($input);
        $order_id = str_pad($order->id, 5, "0", STR_PAD_LEFT);
        Order::where('id',$order->id)->update([ 'order_id'=>$order_id]);
        $customer_details = Customer::where('id',$input['customer_id'])->first();
        if(is_object($order)) {
            // $this->order_registers($order->id);
            foreach ($items as $key => $value) {
                unset($value['image']);
                $value['order_id'] = $order->id;
                OrderItem::create($value);
            }
            Session::put('cart', []);
            Session::put('promo', '');
            Session::put('sub_total', 0);
            return 1;
        } else {
            return 0;
        }
    }

    public function generate_token($data){

        $app_setting = AppSetting::where('id',1)->first();
        $post_data = array();
        $post_data['merchant_mobile_no'] = $app_setting->merchant_id;
        $post_data['store_password'] = $app_setting->store_password;
        $post_data['order_id'] = $data['order_ref_id'];
        $post_data['bill_amount'] = $data['total'];
        $post_data['success_url'] = env('APP_URL')."/payment_success";
        $post_data['fail_url'] = env('APP_URL')."/payment_failed";
        $post_data['cancel_url'] = env('APP_URL')."/payment_cancelled";

        # SENT REQUEST TO FASTPAY
        $direct_api_url = "https://secure.fast-pay.cash/merchant/generate-payment-token";

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $direct_api_url );
        curl_setopt($handle, CURLOPT_TIMEOUT, 10);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($handle, CURLOPT_POST, 1 );
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);


        $content = curl_exec($handle );

        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if($code == 200 && !( curl_errno($handle))) {
            curl_close( $handle);
            $response = $content;
        } else {
            curl_close( $handle);
            echo "FAILED TO CONNECT WITH FastPay  API";
            exit;
        }

        # PARSE THE JSON RESPONSE
        $decodedResponse = json_decode($response, true );
        return $decodedResponse;
    }

    public function order_registers($id){
        //$customer = Customer::where('id',$id)->first(); 
        $data = array();
        $orders = Order::where('id',$id)->first();
        $customer = Customer::where('id',$orders->customer_id)->first();
        $data['order_id'] = $orders->order_id;
        $data['delivery_date'] = date('d M-Y',strtotime($orders->delivery_date));
        $data['delivery_time'] = $orders->delivery_time;
        $data['pickup_date'] = date('d M-Y',strtotime($orders->pickup_date));
        $data['pickup_time'] = $orders->pickup_time;
        $data['delivery_address'] = Address::where('id',$orders->address_id)->value('address');
        $data['items'] = json_decode($orders->items, TRUE);
        $data['total'] = $orders->total;
        $data['discount'] = $orders->discount;
        $data['sub_total'] = $orders->sub_total;
        $data['payment_mode'] = PaymentMethod::where('id',$orders->payment_mode)->value('payment_mode');
        $mail_header = array("data" => $data);
        $this->order_register($mail_header,'Order Placed Successfully',$customer->email);
    }

    public function profile_update(Request $request)
    {
        $input = $request->all();
        $id = $input['customer_id'];
        unset($input['customer_id']);
        unset($input['_token']);
        if($request->password){
            $options = [
                'cost' => 12,
            ];
            $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
            $input['status'] = 1;
        }else{
            unset($input['password']);
        }
        $update = Customer::where('id',$id)->update($input);
        return $update;
    }

    public function profile_image(Request $request)
    {
        $input = $request->all();
        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/images');
            $image->move($destinationPath, $name);
            $image_path = 'images/'.$name;

            $update = Customer::where('id',$input['id'])->update([ 'profile_picture' => $image_path ]);
            return $update;
        }
    }

    public function save_address(Request $request)
    {   
        $input = $request->all();
        unset($input['_token']);
        $url = 'https://maps.googleapis.com/maps/api/staticmap?center='.$input['latitude'].','.$input['longitude'].'&zoom=16&size=600x300&maptype=roadmap&markers=color:red%7Clabel:L%7C'.$input['latitude'].','.$input['longitude'].'&key='.env('MAP_KEY');
            $img = 'static_map/'.md5(time()).'.png';
            file_put_contents('uploads/'.$img, file_get_contents($url));

        $input['static_map'] = $img;
        $input['status'] = 1;

        if ($address = Address::create($input)) {
            return json_encode($address);
        } else {
            return 0;
        }
    }
    
    public function get_pickup_time_slot(Request $request)
    {
    $input = $request->all();
    
    $current_date = date('d-m-Y');
    $input_date = date('d-m-Y',strtotime($input['date']));
    
    $data = AppSetting::first();
        /*foreach ($variable as $key => $value) {
            # code...
        }*/ 
        if($input_date != $current_date){
            $begin = new DateTime($data->opening_time);
        }else{
            $timestamp = time() + 120*60;
            $time = date('H', $timestamp);
            $begin = new DateTime($time.':00:00');
            //$begin = new DateTime($time.':00:00');
            $settings_time = new DateTime($data->opening_time);
            if($begin > $settings_time){
                $begin = new DateTime($time.':00:00');
            }else{
             
            }
        }
        $end   = new DateTime($data->closing_time);
        $interval = DateInterval::createFromDateString('60 min');
        $times    = new DatePeriod($begin, $interval, $end);
        $result = array();
        foreach ($times as $time) {
            array_push($result,$time->format('h:i A').'-'.$time->add($interval)->format('h:i A'));
        }
        $html_data="";
        foreach ($result as $value) {
            $str = preg_replace('/\s+/', '', $value);
            $html_data .="<button type='button' class='btn btn-small' style='margin:10px; background:red; color:white!important' onClick=add_pickup_time('".$str."');>".$value."</button>";
        }        
        return $html_data;
    }
    
    public function get_delivery_time_slot(Request $request)
    {
        $input = $request->all();
        
        $current_date = date('d-m-Y');
        $input_date = date('d-m-Y',strtotime($input['date']));
        
        $data = AppSetting::first();
        /*foreach ($variable as $key => $value) {
            # code...
        }*/ 
        if($input_date != $current_date){
            $begin = new DateTime($data->opening_time);
        }else{
            $timestamp = time() + 120*60;
            $time = date('H', $timestamp);
            $begin = new DateTime($time.':00:00');
            //$begin = new DateTime($time.':00:00');
            $settings_time = new DateTime($data->opening_time);
            if($begin > $settings_time){
                $begin = new DateTime($time.':00:00');
            }else{
             
            }
        }
        $end   = new DateTime($data->closing_time);
        $interval = DateInterval::createFromDateString('60 min');
        $times    = new DatePeriod($begin, $interval, $end);
        $result = array();
        foreach ($times as $time) {
            array_push($result,$time->format('h:i A').'-'.$time->add($interval)->format('h:i A'));
        }
        $html_data="";
        foreach ($result as $value) {
          $str = preg_replace('/\s+/', '', $value);
          $html_data .="<button type='button' class='btn btn-icon-only btn-default' style='margin:10px; background:red; color:white!important'  onClick=add_delivery_time('".$str."');>".$value."</button>";
      // $html_data .="<button type='button' class='btn btn-icon-only btn-default' onClick='add_delivery_time($value)';>".$value."</button>";
      }        return $html_data;
  }

    public function edit_address(Request $request)
    {
        $input = $request->all();
        $id = $input['address_id'];
        unset($input['address_id']);
        unset($input['_token']);

        $url = 'https://maps.googleapis.com/maps/api/staticmap?center='.$input['latitude'].','.$input['longitude'].'&zoom=16&size=600x300&maptype=roadmap&markers=color:red%7Clabel:S%7C'.$input['latitude'].','.$input['longitude'].'&key='.env('MAP_KEY');
            $img = 'static_map/'.md5(time()).'.png';
            file_put_contents('uploads/'.$img, file_get_contents($url));

        $input['static_map'] = $img;
        $input['status'] = 1;

        if (Address::where('id',$id)->update($input)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function address_delete(Request $request)
    {
        $input = $request->all();

        $res = Address::where('id',$input['address_id'])->delete();
        if ($res) {
            return 1;
        } else {
            return 0;
        }
    }

    public function coupon_apply_ajax()
    {
       $currency = AppSetting::where('id',1)->value('default_currency');
       $sub_total = Session::get('sub_total', 0);
       $promo = Session::get('promo', '');
       $total = 0;
       $promo_amount = 0;
       $promo_id = 0;
       $order_count = Order::where('customer_id',Auth::id())->count();
       $order_count = 1;
        $data['sub_total'] = $sub_total;
        $data['total'] = $total;
        $data['promo_amount'] = $promo_amount;
        $data['promo_id'] = '';
        $data['error'] = 0;
        $data['currency'] = $currency;
        if($promo == ''){
            $data['total'] = $sub_total;
        }else{
            $promo_details = PromoCode::where('promo_code',$promo)->first();
            $promo_id = $promo_details->id;
            $data['promo_id'] = $promo_id;
            if($promo_details->minimum_amount == 0){
                if($promo_details->promo_type == 1){
                    $discount = $sub_total - $promo_details->discount;
                    if($discount > 1){
                        $data['promo_amount'] = number_format($promo_details->discount, 2, '.', '');
                        // $data['promo_amount'] = number_format((float)$promo_details->discount, 2, '.', '');
                        $data['total'] = $discount;
                    }else{
                        $data['promo_amount'] = 0;
                        $data['total'] = $sub_total;
                        $data['error'] = 1;
                    }
                }else{
                    $discount = ($promo_details->discount /100) * $sub_total;
                    $data['promo_amount'] = number_format($discount, 2, '.', '');
                    // $data['promo_amount'] = number_format((float)$discount, 2, '.', '');
                    $data['total'] = $sub_total - $discount;
                }
            }else{
                if($sub_total >= $promo_details->minimum_amount){
                    if($promo_details->promo_type == 1){
                      $discount = $sub_total - $discount;
                      if($discount >= 1){
                        $data['promo_amount'] = number_format($promo_details->discount, 2, '.', '');
                        // $data['promo_amount'] = number_format((float)$promo_details->discount, 2, '.', '');
                        $data['total'] = $discount;
                      }else{
                        $promo = '';
                         $data['error'] = 1;
                      }
                    }else{
                      $discount = ($promo_details->discount / 100) * $sub_total;
                      $data['promo_amount'] = number_format($discount, 2, '.', '');
                      // $data['promo_amount'] = number_format((float)$discount, 2, '.', '');
                      $data['total'] = $sub_total - $discount;
                    }
                }else{
                    $data['promo_amount'] = 0;
                    $data['total'] = $sub_total;
                    $data['error'] = 1;
                    $promo = '';
                }
            }
       }
       return $data;
       /*return view('cart',[ 'currency' => $currency, 'sub_total' => $sub_total, 'total'=>$total, 'promo_codes'=>$promo_codes, 'promo_amount' => $promo_amount, 'error' => $error, 'promo' => $promo, 'promo_id' => $promo_id, 'addresses' => $addresses, 'payment_modes' =>$payment_modes ]);*/
    }

    public function register_mail($id,$password){
        $customer = Customer::where('id',$id)->first(); 
        $mail_header = array("username" => $customer->customer_name, "password" => $password);
        $this->register($mail_header,'Welcome '.$customer->customer_name,$customer->email);
    }

    public function forgot_password()
    {
        return view('forgot_password');
    }

    public function generate_otp(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
          'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return Redirect::to('forgot_password')->withErrors($validator);
        }
        $customer = Customer::where('email',$input['email'])->first();
        $app_setting = AppSetting::where('id',1)->first();
        
        $data = array();
        $data['logo'] = $app_setting->logo;
        $data['name'] = $customer->customer_name;
        //$mail_header = array("data" => $data);
        if(is_object($customer)){
            $otp = rand(1000,9999);
            $data['otp'] = $otp;
            $mail_header = array("data" => $data);
            Customer::where('id',$customer->id)->update(['otp'=> $otp ]);
            $message ="Trouble signing in?";
            $this->send_mail_to_customer($mail_header,$message,$input['email']);
            return view('otp_page', [ 'otp' => $otp, 'id' => $customer->id ]);
        }
        else{
            return view('forgot_password');
        }
     }
     public function send_mail_to_customer($mail_header,$subject,$to_mail){
    	Mail::send('mail_templates.forgot_password', $mail_header, function ($message)
		 use ($subject,$to_mail) {
			$message->from(env('MAIL_USERNAME'), env('APP_NAME'));
			$message->subject($subject);
			$message->to($to_mail);
		});
    }

    public function reset_password(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        return view('reset_password', ['id' => $input['customer_id']]);   
    }

    public function update_password(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        } 

        $options = [
            'cost' => 12,
        ];
        $customer_id = $input['customer_id'];
        unset($input['_token']);
        unset($input['con_password']);
        unset($input['customer_id']);

        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);

        if(Customer::where('id',$customer_id)->update($input)){
             return Redirect::to('login');
        }
    }

    public function stripe()
    {
        $total = Session::get('total_amount', 0);
        $total = (round($total) . '');
        return view('PaymentStripe', ['total' => $total]);
    }

    public function stripePost(Request $request)
    {
        $amount = (round($request->amount) . '') * 100;
        $currency = AppSetting::where('id', 1)->value('currency_short_code');
        $stripe = new Stripe();
        $stripe->setApiKey(env('STRIPE_SECRET'));
        $payment = $stripe->charges()->create([
                'source' => $request->stripeToken,
                'currency' => $currency,
                'amount'   =>  $amount,
                'description' => 'Test payment from ilove iorning menpanitechnologies.'
            ]);
        if ($payment)
        {
        $input = Session::get('order_details', []);
        $items_data = array();
        $i=0;   
        foreach (Session::get('cart', []) as $key => $value) {
            $items_data[$i] = $value;
            $i++;
        }
        $input['items'] = json_encode($items_data,true);

        $items = json_decode($input['items'], true);
        $order = Order::create($input);
        $order_id = str_pad($order->id, 5, "0", STR_PAD_LEFT);
        Order::where('id',$order->id)->update([ 'order_id'=>$order_id]);
        $customer_details = Customer::where('id',$input['customer_id'])->first();
        if(is_object($order)) {
            
            foreach ($items as $key => $value) {
                unset($value['image']);
                $value['order_id'] = $order->id;
                OrderItem::create($value);
            }
            // $this->order_registers($order->id);
            Session::put('cart', []);
            Session::put('order_details', []);
            Session::put('promo', '');
            Session::put('total', 0);
                return Redirect::to('/thankyou');
        } else {
            return 0;
        }
        }
        else
        {
            return back();
        }
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
		    $result['message'] = 'maximum orders reached for selected pickup slot';
		    $result['status'] = '0';
		    return json_encode($result);
		}else if($max_order_delivery_date >= $max_order_per_hour){
		    $result['message'] = 'maximum orders reached for selected delivery slot';
		    $result['status'] = '0';
		    return json_encode($result);
		}
		
		$result['message'] = 'Success';
	    $result['status'] = '1';
	    return json_encode($result);

	}
	
	public function delete_card($id){
        
        $customer_token = Customer::where('id',Auth::user()->id)->value('stripe_token');
        $card_token = CustomerCard::where('id',$id)->value('card_token');
        $stripe = new Stripe();
        if(CustomerCard::where('customer_id',Auth::user()->id)->where('id',$id)->delete()){
            $card = $stripe->cards()->delete($customer_token, $card_token);
            $default_card = CustomerCard::where('customer_id',Auth::user()->id)->where('is_default',1)->first();
            if(!is_object($default_card)){
                $last_card = CustomerCard::where('customer_id',Auth::user()->id)->orderBy('id','DESC')->first();
                if(is_object($last_card)){
                    CustomerCard::where('id',$last_card->id)->update([ 'is_default' => 1]);
                    $customer = $stripe->customers()->update($customer_token, [
                        'default_source' => $last_card->card_token
                    ]);
                }
            }
            return redirect('/my_cards');
        }else{
            return redirect('/my_cards');
        }
	}
	
	public function add_card(Request $request){
	    $input = $request->all();
	    
	    $customer_token = Customer::where('id',Auth::user()->id)->value('stripe_token');
        
        $stripe = new Stripe();
        $token = $input['stripeToken'];
        
        $card = $stripe->cards()->create($customer_token, $token);
        
        $customer = $stripe->customers()->update($customer_token, [
            'default_source' => $card['id']
        ]);
        
        $data['customer_id'] = Auth::user()->id;
        $data['card_token'] = $card['id'];
        $data['last_four'] = $card['last4'];
        $data['is_default'] = 1;
        CustomerCard::create($data);
        
        CustomerCard::where('customer_id','=',Auth::user()->id)->where('card_token','!=',$card['id'])->update([ 'is_default' => 0 ]);
        return redirect('my_cards');
	}
	
	public function check_card_availability(Request $request){
	    $input = $request->all();
        $input['customer_id'] = Auth::user()->id;
        
        $data =  CustomerCard::where('customer_id',$input['customer_id'])->where('is_default',1)->first();
      
        if(is_object($data)){
            $customer = Customer::where('id',$input['customer_id'])->first();
            $stripe = new Stripe();
            $charge = $stripe->charges()->create([
                'customer' => $customer->stripe_token,
                'currency' => AppSetting::where('id',1)->value('default_currency'),
                'amount'   => $input['amount'],
            ]);
        
            return 1;
        }else{
            return 0;
        }
	}

}

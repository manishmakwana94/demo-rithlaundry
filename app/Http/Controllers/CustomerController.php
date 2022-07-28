<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerCard;
use App\Models\CustomerWalletHistory;
use Validator;
use Illuminate\Support\Facades\Hash;
use Cartalyst\Stripe\Stripe;
class CustomerController extends Controller
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
            'customer_name' => 'required',
            'phone_with_code' => 'required',
            'phone_number' => 'required|numeric|digits_between:9,20|unique:customers,phone_number',
            'email' => 'required|email|regex:/^[a-zA-Z]{1}/|unique:customers,email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $options = [
            'cost' => 12,
        ];
        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
        $input['status'] = 1;
        
        /*$stripe = new Stripe();
        $stripe_token = $stripe->customers()->create([
            'email' => $input['email'],
        ]);
        $input['stripe_token'] = $stripe_token['id'];*/
  
        $customer = Customer::create($input);

        if (is_object($customer)) {
            return response()->json([
                "result" => $customer,
                "message" => 'Registered Successfully',
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
        $input['id'] = $id;
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $result = Customer::select('id', 'customer_name','phone_number','phone_with_code','email','profile_picture','status')->where('id',$id)->first();

        if (is_object($result)) {
            return response()->json([
                "result" => $result,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong...',
                "status" => 0
            ]);
        }
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
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_name' => 'required',
            'email' => 'required|email|unique:customers,id,'.$id
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        if($request->password){
            $options = [
                'cost' => 12,
            ];
            $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
            $input['status'] = 1;
        }else{
            unset($input['password']);
        }

        if (Customer::where('id',$id)->update($input)) {
            return response()->json([
                "result" => Customer::select('id', 'customer_name','phone_number','phone_with_code','email','profile_picture','status')->where('id',$id)->first(),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong...',
                "status" => 0
            ]);
        }

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

     public function login(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
            'password' => 'required',
            'fcm_token' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $credentials = request(['phone_with_code', 'password']);
        $customer = Customer::where('phone_with_code',$credentials['phone_with_code'])->first();

        if (!($customer)) {
            return response()->json([
                "message" => 'Invalid phone number or password',
                "status" => 0
            ]);
        }
        
        if (Hash::check($credentials['password'], $customer->password)) {
            if($customer->status == 1){
                
                Customer::where('id',$customer->id)->update([ 'fcm_token' => $input['fcm_token']]);
                $customer = Customer::where('phone_with_code',$credentials['phone_with_code'])->first();
                
                return response()->json([
                    "result" => $customer,
                    "message" => 'Success',
                    "status" => 1
                ]);   
            }else{
                return response()->json([
                    "message" => 'Your account has been blocked',
                    "status" => 0
                ]);
            }
        }else{
            return response()->json([
                "message" => 'Invalid phone number or password',
                "status" => 0
            ]);
        }

     }

    public function profile_picture(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'profile_picture' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/images');
            $image->move($destinationPath, $name);
            if(Customer::where('id',$input['customer_id'])->update([ 'profile_picture' => 'images/'.$name ])){
                return response()->json([
                    "result" => Customer::select('id', 'customer_name','phone_number','phone_with_code','email','profile_picture','status')->where('id',$input['customer_id'])->first(),
                    "message" => 'Success',
                    "status" => 1
                ]);
            }else{
                return response()->json([
                    "message" => 'Sorry something went wrong...',
                    "status" => 0
                ]);
            }
        }

    }

    public function forgot_password(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $customer = Customer::where('phone_with_code',$input['phone_with_code'])->first();
        

        if(is_object($customer)){
            $data['id'] = $customer->id;
            $data['otp'] = rand(1000,9999);
            $message = "Hi".env('APP_NAME'). "  , Your OTP code is:".$data['otp'];
            //$this->sendSms($input['phone_with_code'],$message);
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "result" => 'Please enter valid phone number',
                "status" => 0
            ]);
            
        }

    }  

    public function reset_password(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $options = [
            'cost' => 12,
        ];
        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);

        if(Customer::where('id',$input['id'])->update($input)){
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Sorry something went wrong',
                "status" => 0
            ]);
        }
    }  
        
    public function customer_wallet(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data['wallet_amount'] = Customer::where('id',$input['id'])->value('wallet');
        
        $data['wallets'] = CustomerWalletHistory::where('customer_id',$input['id'])->get();
        
        if($data){
            return response()->json([
                "result" => $data,
                "count" => count($data),
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }
    }
    
    public function add_wallet(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'amount' => 'required'
            
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        CustomerWalletHistory::create([ 'customer_id' => $input['customer_id'], 'type' => 1, 'message' => 'Successfully added to your wallet', 'message_ar' => 'تم الإضافة إلى محفظتك بنجاح','amount' => $input['amount']]);
        
        $old_wallet_amount = Customer::where('id',$input['customer_id'])->value('wallet');
        $new_wallet = $input['amount'] + $old_wallet_amount;
        Customer::where('id',$input['customer_id'])->update([ 'wallet' => $new_wallet]);
        
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
            
    }
    
    public function get_cards(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required'
        ]);
        
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data =  CustomerCard::where('customer_id',$input['customer_id'])->get();
        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function delete_card(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'card_id' => 'required'
        ]);
        
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $customer_token = Customer::where('id',$input['customer_id'])->value('stripe_token');
        $card_token = CustomerCard::where('id',$input['card_id'])->value('card_token');
        $stripe = new Stripe();
        $card = $stripe->cards()->delete($customer_token, $card_token);
        CustomerCard::where('id',$input['card_id'])->delete();
        
        $default_card = CustomerCard::where('customer_id',$input['customer_id'])->where('is_default',1)->first();
        if(!is_object($default_card)){
            $last_card = CustomerCard::where('customer_id',$input['customer_id'])->orderBy('id','DESC')->first();
            if(is_object($last_card)){
                CustomerCard::where('id',$last_card->id)->update([ 'is_default' => 1]);
                $customer = $stripe->customers()->update($customer_token, [
                    'default_source' => $last_card->card_token
                ]);
            }
        }
        
        
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function add_card(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'number' => 'required',
            'exp_month' => 'required',
            'cvc' => 'required',
            'exp_year' => 'required'
            
        ]);
        
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $customer_token = Customer::where('id',$input['customer_id'])->value('stripe_token');
        
        $stripe = new Stripe();
        $token = $stripe->tokens()->create([
            'card' => [
                'number'    => $input['number'],
                'exp_month' => $input['exp_month'],
                'cvc'       => $input['cvc'],
                'exp_year'  => $input['exp_year'],
            ],
        ]);
        
        $card = $stripe->cards()->create($customer_token, $token['id']);
        
        $customer = $stripe->customers()->update($customer_token, [
            'default_source' => $card['id']
        ]);
        
        $data['customer_id'] = $input['customer_id'];
        $data['card_token'] = $card['id'];
        $data['last_four'] = $card['last4'];
        $data['is_default'] = 1;
        CustomerCard::create($data);
        
        CustomerCard::where('customer_id','=',$input['customer_id'])->where('card_token','!=',$card['id'])->update([ 'is_default' => 0 ]);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function sendError($message) {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('',$message);
        $response['status'] = "0";
        return response()->json($response, 200);
    } 
    
    public function test_stripe(){
        //$email = "sarathkannanbsc1@gmail.com";
        $stripe = new Stripe();
        
        try {
            $charge = $stripe->charges()->create([
                'customer' => 'cus_IaSYKvrlPKks0m',
                'currency' => 'INR',
                'amount'   => '100',
            ]);
        } catch (Cartalyst\Stripe\Exception\CardErrorException $e) {
            // Get the status code
            $code = $e->getCode();
        
            // Get the error message returned by Stripe
            $message = $e->getMessage();
        
            // Get the error type returned by Stripe
            $type = $e->getErrorType();
            
            echo $message;
        }
        
        echo "<pre>";
        //print_r($charge);
    }
    
    public function check_phone(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = array();
        $customer = Customer::where('phone_with_code',$input['phone_with_code'])->first();

        if(is_object($customer)){
            $data['is_available'] = 1;
            $data['otp'] = "";
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            $data['is_available'] = 0;
            $data['otp'] = rand(1000,9999);
            $message = "Hi, from ".env('APP_NAME'). "  , Your OTP code is:".$data['otp'];
            //$this->sendSms($input['phone_with_code'],$message);
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }
    }
    public function profile_picture_update(Request $request){

      $input = $request->all();
      $validator = Validator::make($input, [
          'customer_id' => 'required',
          'profile_picture' => 'required'
      ]);

      if ($validator->fails()) {
          return $this->sendError($validator->errors());
      }

      if ($request->hasFile('profile_picture')) {
          $image = $request->file('profile_picture');
          $name = time().'.'.$image->getClientOriginalExtension();
          $destinationPath = public_path('/uploads/images');
          $image->move($destinationPath, $name);
          if(Customer::where('id',$input['customer_id'])->update([ 'profile_picture' => 'images/'.$name ])){
              return response()->json([
                  "result" => Customer::select('id', 'customer_name','phone_number','phone_with_code','email','profile_picture','status')->where('id',$input['customer_id'])->first(),
                  "message" => 'Success',
                  "status" => 1
              ]);
          }else{
              return response()->json([
                  "message" => 'Sorry something went wrong...',
                  "status" => 0
              ]);
          }
      }

  }
}

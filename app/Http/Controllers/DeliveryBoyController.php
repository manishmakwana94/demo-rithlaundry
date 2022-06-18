<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryBoy;
use App\Models\Order;
use Validator;
use Illuminate\Support\Facades\Hash;
class DeliveryBoyController extends Controller
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

     public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_name' => 'required',
            'phone_number' => 'required|numeric|digits_between:9,20|unique:delivery_boys,phone_number',
            'phone_with_code' => 'required',
            'email' => 'required|email|regex:/^[a-zA-Z]{1}/|unique:delivery_boys,email',
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

        $delivery_boy = DeliveryBoy::create($input);

        if (is_object($delivery_boy)) {
            return response()->json([
                "result" => $delivery_boy,
                "message" => 'Updated Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }

    }

     public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_name' => 'required',
            'email' => 'required|email|unique:delivery_boys,id,'.$id
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

        if (DeliveryBoy::where('id',$id)->update($input)) {
            return response()->json([
                "result" => DeliveryBoy::select('id', 'delivery_boy_name','phone_number','phone_with_code','email','profile_picture','status')->where('id',$id)->first(),
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

     public function edit($id)
    {
        $input['id'] = $id;
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $result = DeliveryBoy::select('id', 'delivery_boy_name','phone_number','phone_with_code','email','profile_picture','status')->where('id',$id)->first();

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function login(Request $request){

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
        $delivery_boy = DeliveryBoy::where('phone_with_code',$credentials['phone_with_code'])->first();

        if (!($delivery_boy)) {
            return response()->json([
                "message" => 'Invalid phone number or password',
                "status" => 0
            ]);
        }
        
        if (Hash::check($credentials['password'], $delivery_boy->password)) {
            if($delivery_boy->status == 1){
                DeliveryBoy::where('id',$delivery_boy->id)->update([ 'fcm_token' => $input['fcm_token']]);
                $delivery_boy = DeliveryBoy::where('phone_with_code',$credentials['phone_with_code'])->first();
                return response()->json([
                    "result" => $delivery_boy,
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
            'delivery_boy_id' => 'required',
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
            if(DeliveryBoy::where('id',$input['delivery_boy_id'])->update([ 'profile_picture' => 'images/'.$name ])){
                return response()->json([
                    "result" => DeliveryBoy::select('id', 'delivery_boy_name','phone_number','phone_with_code','email','profile_picture','status')->where('id',$input['delivery_boy_id'])->first(),
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

        $delivery_boy = DeliveryBoy::where('phone_with_code',$input['phone_with_code'])->first();
        

        if(is_object($delivery_boy)){
            $data['id'] = $delivery_boy->id;
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

        if(DeliveryBoy::where('id',$input['id'])->update($input)){
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Invalid email address',
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

    public function dashboard(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $result['total_bookings'] = Order::where('delivered_by',$input['id'])->count();
        $result['completed_bookings'] = Order::where('delivered_by',$input['id'])->where('status',7)->count();
        $result['today_bookings'] = Order::where('delivered_by',$input['id'])->whereDay('created_at', date('d'))->count();
        $result['pending_bookings'] = Order::where('delivered_by',$input['id'])->where('status','!=',7)->count();


        if($result){
            return response()->json([
                "result" => $result,
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
        $delivery_boy = DeliveryBoy::where('phone_with_code',$input['phone_with_code'])->first();

        if(is_object($delivery_boy)){
            $data['is_available'] = 1;
            $data['otp'] = "";
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            $data['is_available'] = 0;
            return response()->json([
                "message" => 'Your number is not registered, please contact admin',
                "status" => 2
            ]);
        }
    }

}

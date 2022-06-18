<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Address;
use App\Models\AppSetting;
use App\Models\Area;
use App\Models\Region;
use App\Models\Zone;
class AddressController extends Controller
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

        $validator =  Validator::make($input,[
            'customer_id' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $check = $this->find_in_polygon($input['latitude'],$input['longitude']);
        //print_r($check);exit;
        if(!$check){
            return response()->json([
                "message" => 'Sorry at this moment, our service is unavailable in your area',
                "status" => 0
            ]);
        }
        $url = 'https://maps.googleapis.com/maps/api/staticmap?center='.$input['latitude'].','.$input['longitude'].'&zoom=16&size=600x300&maptype=roadmap&markers=color:red%7Clabel:L%7C'.$input['latitude'].','.$input['longitude'].'&key='.env('MAP_KEY');
            $img = 'static_map/'.md5(time()).'.png';
            file_put_contents('uploads/'.$img, file_get_contents($url));

        $input['static_map'] = $img;
        $input['status'] = 1;
        $input['zone_id'] = $check;

        if (Address::create($input)) {
            return response()->json([
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

        $validator =  Validator::make($input,[
            'id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        $address = Address::where('id',$input['id'])->first();

        if ($address) {
            return response()->json([
                "result" => $address,
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

        $validator =  Validator::make($input,[
            'customer_id' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        
        $check = $this->find_in_polygon($input['latitude'],$input['longitude']);
        if(!$check){
            return response()->json([
                "message" => 'Sorry at this moment, our service is unavailable in your area',
                "status" => 0
            ]);
        }

        $url = 'https://maps.googleapis.com/maps/api/staticmap?center='.$input['latitude'].','.$input['longitude'].'&zoom=16&size=600x300&maptype=roadmap&markers=color:red%7Clabel:S%7C'.$input['latitude'].','.$input['longitude'].'&key='.env('MAP_KEY');
            $img = 'static_map/'.md5(time()).'.png';
            file_put_contents('uploads/'.$img, file_get_contents($url));

        $input['static_map'] = $img;
        $input['status'] = 1;
        $input['zone_id'] = $check;

        if (Address::where('id',$id)->update($input)) {
            return response()->json([
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $input = $request->all();

        $validator =  Validator::make($input,[
            'customer_id' => 'required',
            'address_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $res = Address::where('id',$input['address_id'])->delete();
        if ($res) {
            $addresses = Address::where('customer_id',$input['customer_id'])->orderBy('created_at', 'desc')->get();
            return response()->json([
                "result" => $addresses,
                "message" => 'Deleted Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }

    public function all_addresses(Request $request){

        $input = $request->all();

        $validator =  Validator::make($input,[
            'customer_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        $addresses = Address::where('customer_id',$input['customer_id'])->orderBy('created_at', 'desc')->get();

        if ($addresses) {
            return response()->json([
                "result" => $addresses,
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
    
    public function get_delivery_charge(Request $request){
        $input = $request->all();

        $validator =  Validator::make($input,[
            'address_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        
        $cost = AppSetting::where('id',1)->value('delivery_cost');
        
        return response()->json([
            "result" => $cost,
            "message" => 'Success',
            "status" => 1
        ]);
        
        
    }
    
    public function save_polygon(Request $request){
        $input = $request->all();
        Zone::where('id',$input['id'])->update([ 'polygon' => $input['polygon']]);
    }

    public function find_in_polygon($longitude_x,$latitude_y){
        $id = 1;
        $all_locations = Zone::all();
        foreach($all_locations as $key => $value){
            if($value->polygon){
                $polygon = explode(";",$value->polygon);
                $vertices_x = [];
                $vertices_y = [];
                foreach($polygon as $key => $value1){
                   $value1 = explode(",",$value1);
                   if(@$value1[1]){
                    $vertices_x[$key] = floatval($value1[0]);
                    $vertices_y[$key] = floatval($value1[1]);
                   }
                }

                $points_polygon = count($vertices_x); 

                if ($this->is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)){
                  return $value->id;
                } 
            }
            
        }
        return 0;
        
    }

    public function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)
    {
      $i = $j = $c = 0;
      for ($i = 0, $j = $points_polygon-1 ; $i < $points_polygon; $j = $i++) {
        if ( (($vertices_y[$i] > $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
        ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) ) 
            $c = !$c;
      }

      return $c;
    }
    public function sendError($message) {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('',$message);
        $response['status'] = "0";
        return response()->json($response, 200);
    }
}

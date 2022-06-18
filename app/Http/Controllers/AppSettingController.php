<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;
use DateTime;
use DateInterval;
use DatePeriod;
class AppSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = AppSetting::first();
        $data->stripe_key = env('STRIPE_KEY');
        $data->stripe_secret = env('STRIPE_API_KEY');
        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    
    public function get_time(Request $request)
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
                return response()->json([
                    "result" => [],
                    "message" => 'Success',
                    "status" => 1
                ]);
            }
        }
        
        $end   = new DateTime($data->closing_time);

        $interval = DateInterval::createFromDateString('60 min');

        $times    = new DatePeriod($begin, $interval, $end);
        $result = array();
        foreach ($times as $time) {
            array_push($result,$time->format('h:i A').'-'.$time->add($interval)->format('h:i A'));
        }
        
        return response()->json([
            "result" => $result,
            "message" => 'Success',
            "status" => 1
        ]);
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

<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::resource('service', 'App\Http\Controllers\ServiceController');
Route::post('faq', 'App\Http\Controllers\FaqController@index');
Route::resource('product', 'App\Http\Controllers\ProductController');
Route::resource('customer', 'App\Http\Controllers\CustomerController');
Route::resource('address', 'App\Http\Controllers\AddressController');
Route::post('address/all', 'App\Http\Controllers\AddressController@all_addresses');
Route::post('address/delete', 'App\Http\Controllers\AddressController@delete');
Route::post('customer/login', 'App\Http\Controllers\CustomerController@login');
Route::post('customer/forgot_password', 'App\Http\Controllers\CustomerController@forgot_password');
Route::post('customer/reset_password', 'App\Http\Controllers\CustomerController@reset_password');
Route::post('customer/add_card', 'App\Http\Controllers\CustomerController@add_card');
Route::post('customer/get_cards', 'App\Http\Controllers\CustomerController@get_cards');
Route::post('customer/delete_card', 'App\Http\Controllers\CustomerController@delete_card');
Route::post('promo', 'App\Http\Controllers\PromoCodeController@index');
Route::get('app_setting', 'App\Http\Controllers\AppSettingController@index');
Route::post('privacy_policy', 'App\Http\Controllers\PrivacyPolicyController@index');
Route::post('order', 'App\Http\Controllers\OrderController@store');
Route::post('get_orders', 'App\Http\Controllers\OrderController@getOrders');
Route::resource('delivery_partner', 'App\Http\Controllers\DeliveryBoyController');
Route::post('delivery_partner/login', 'App\Http\Controllers\DeliveryBoyController@login');
Route::post('delivery_partner/forgot_password', 'App\Http\Controllers\DeliveryBoyController@forgot_password');
Route::post('delivery_partner/reset_password', 'App\Http\Controllers\DeliveryBoyController@reset_password');
Route::post('order_status_change', 'App\Http\Controllers\OrderController@order_status_change');
Route::post('dashboard', 'App\Http\Controllers\DeliveryBoyController@dashboard');
Route::post('payment', 'App\Http\Controllers\PaymentMethodController@payment');
Route::post('stripe_payment', 'App\Http\Controllers\PaymentMethodController@stripe_payment');
Route::post('customer/wallet', 'App\Http\Controllers\CustomerController@customer_wallet');
Route::post('customer/add_wallet', 'App\Http\Controllers\CustomerController@add_wallet');
Route::get('get_region', 'App\Http\Controllers\AddressController@get_region');
Route::post('get_area', 'App\Http\Controllers\AddressController@get_area');
Route::post('get_time', 'App\Http\Controllers\AppSettingController@get_time');
Route::post('get_delivery_charge', 'App\Http\Controllers\AddressController@get_delivery_charge');
Route::get('get_labels', 'App\Http\Controllers\OrderController@get_labels');
Route::post('check_order_count', 'App\Http\Controllers\OrderController@check_order_count');

Route::get('test_stripe', 'App\Http\Controllers\CustomerController@test_stripe');
Route::post('check_cards', 'App\Http\Controllers\OrderController@check_cards');
Route::post('customer/check_phone', 'App\Http\Controllers\CustomerController@check_phone');
Route::post('customer/profile_picture', 'App\Http\Controllers\CustomerController@profile_picture');
Route::post('customer/profile_picture_update', 'App\Http\Controllers\CustomerController@profile_picture_update');
Route::post('delivery_partner/check_phone', 'App\Http\Controllers\DeliveryBoyController@check_phone');
Route::post('delivery_partner/profile_picture', 'App\Http\Controllers\DeliveryBoyController@profile_picture');
Route::post('delivery_partner/profile_picture_update', 'App\Http\Controllers\DeliveryBoyController@profile_picture_update');
Route::get('find_in_polygon/{lat}/{lng}', 'App\Http\Controllers\AddressController@find_in_polygon');
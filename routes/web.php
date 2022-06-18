<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'App\Http\Controllers\WebController@index')->name('home');
Route::get('/home', 'App\Http\Controllers\WebController@index')->name('home');
Route::get('/services', 'App\Http\Controllers\WebController@services');
Route::get('/faq', 'App\Http\Controllers\WebController@faq');
Route::get('/pricing', 'App\Http\Controllers\WebController@pricing');
Route::get('/pricing_mobile', 'App\Http\Controllers\WebController@pricing_mobile');
Route::get('/products/{id}', 'App\Http\Controllers\WebController@products');
Route::get('/cart', 'App\Http\Controllers\WebController@cart');
Route::get('/login', 'App\Http\Controllers\WebController@showLogin');
Route::get('/profile/{id}', 'App\Http\Controllers\WebController@profile');
Route::post('/login', 'App\Http\Controllers\WebController@doLogin');
Route::post('/register', 'App\Http\Controllers\WebController@doRegister');
Route::get('/register', 'App\Http\Controllers\WebController@showRegister');
Route::get('/logout', 'App\Http\Controllers\WebController@doLogout');
Route::post('/add_to_cart', 'App\Http\Controllers\WebController@add_to_cart');
Route::post('/apply_promo', 'App\Http\Controllers\WebController@apply_promo');
Route::post('/remove_promo', 'App\Http\Controllers\WebController@remove_promo');
Route::post('/checkout', 'App\Http\Controllers\WebController@checkout');
Route::post('/profile_update', 'App\Http\Controllers\WebController@profile_update');
Route::post('/profile_image', 'App\Http\Controllers\WebController@profile_image');
Route::post('/save_address', 'App\Http\Controllers\WebController@save_address');
Route::post('/edit_address', 'WebController@edit_address');
Route::post('/address_delete', 'App\Http\Controllers\WebController@address_delete');
Route::get('/forgot_password', 'App\Http\Controllers\WebController@forgot_password');
Route::post('/forgot_password', 'App\Http\Controllers\WebController@generate_otp');
Route::post('/reset', 'App\Http\Controllers\WebController@reset_password');
Route::post('/reset_password', 'App\Http\Controllers\WebController@update_password');
Route::post('/get_delivery_time_slot', 'App\Http\Controllers\WebController@get_delivery_time_slot');
Route::post('/get_pickup_time_slot', 'App\Http\Controllers\WebController@get_pickup_time_slot');
Route::post('check_order_count', 'App\Http\Controllers\WebController@check_order_count');
Route::get('/privacy_policy', 'WebController@privacy_policy');
Route::get('/my_cards', 'App\Http\Controllers\WebController@my_cards');
Route::get('/delete_card/{id}', 'App\Http\Controllers\WebController@delete_card');
Route::post('/add_card', 'App\Http\Controllers\WebController@add_card');
Route::post('/check_card_availability', 'App\Http\Controllers\WebController@check_card_availability');
Route::get('/thankyou', function () {
    return view('thankyou');
});
Route::get('/payment_success', function () {
    return view('payment_success');
});
Route::get('/order_success', function () {
    return view('order_success');
});
Route::post('/payment_checkout', 'App\Http\Controllers\WebController@payment_checkout');
Route::get('/payment', 'App\Http\Controllers\WebController@stripe');
Route::post('/payment', 'App\Http\Controllers\WebController@stripePost')->name('payment.post');

Route::get('/order_detail/{id}', 'App\Http\Controllers\WebController@show_order_detail');
Route::get('/pending_order_detail/{id}', 'App\Http\Controllers\WebController@show_pending_order_detail');

<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('get_products', 'GeneralController@GetProducts');
    $router->get('view_orders/{id}', 'ViewOrderController@index');
    $router->resource('labels', LabelController::class);
    $router->resource('services', ServiceController::class);
    $router->resource('faqs', FaqController::class);
    $router->resource('app_settings', AppSettingController::class);
    $router->resource('promo_codes', PromoCodeController::class);
    $router->resource('categories', CategoryController::class);
    $router->resource('customers', CustomerController::class);
    $router->resource('delivery_boys', DeliveryBoyController::class);
    $router->resource('privacy_policies', PrivacyPolicyController::class);
    $router->resource('products', ProductController::class);
    $router->resource('fare_managements', FareManagementController::class);
    $router->resource('orders', OrderController::class);
    $router->resource('payment-methods', PaymentMethodController::class);
    $router->resource('banner-images', BannerImageController::class);
    $router->resource('areas', AreaController::class);
    $router->resource('customer_wallet_histories', CustomerWalletHistoryController::class);
    $router->resource('regions', RegionController::class);
    $router->resource('zones', ZoneController::class);
    $router->resource('addresses', AddressController::class);
    $router->post('save_polygon', 'ZoneController@save_polygon')->name('admin.save_polygon');
    $router->get('create_zones/{id}', 'ZoneController@create_zones')->name('admin.create_zones');
    $router->get('view_zones/{id}', 'ZoneController@view_zones')->name('admin.view_zones');
    $router->post('get_polygon', 'ZoneController@get_polygon')->name('admin.get_polygon');

});

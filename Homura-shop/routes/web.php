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

#Route::get('/', 'PagesController@root')->name('root');

Auth::routes();
Route::get('test',function()
{
	return url('user_address');
});
Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');
Route::redirect('/', 'products')->name('root');
Route::get('products', 'ProductsController@index')->name('products.index');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('products/{product}', 'ProductsController@show')->name('products.show');
Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
Route::group(['middleware' => 'auth'], function() {
	Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');	
	Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
	Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
	Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
	Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
	Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');
	Route::get('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
    Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
    Route::get('favorites', 'ProductsController@favorites')->name('products.favorites');
    Route::post('cart', 'CartController@add')->name('cart.add');
    Route::get('cart', 'CartController@index')->name('cart.index');
    Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');
    Route::post('orders', 'OrdersController@store')->name('orders.store');
    Route::get('orders', 'OrdersController@index')->name('orders.index');
     Route::get('orders', 'OrdersController@index')->name('orders.index');
     Route::get('findorder', 'OrdersController@findbyid');
     Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');
     Route::get('cancel/{order}','OrdersController@cancelorder')->name('cancel');
     Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
     Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
	Route::post('orders/{order}/received', 'OrdersController@received')->name('orders.received');


});
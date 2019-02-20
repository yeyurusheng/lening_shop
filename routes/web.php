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

Route::get('/', function () {
    echo date('Y-m-d h:i:s');
    return view('welcome');
});

Route::get('/adduser','Test\TestController@add');

//路由跳转
Route::redirect('/hello1','/world1',301);
Route::get('/world1','Test\TestController@world1');

Route::get('/hello2','Test\TestController@hello2');
Route::get('/world2','Test\TestController@world2');

Route::get('/wei','Test\TestController@wei');
Route::get('/ting','Test\TestController@ting');

//路由参数
Route::get('/user/{uid}','User\UserController@user');
Route::get('/month/{m}/date/{d}','Test\TestController@md');
Route::get('/name/{str?}','Test\TestController@showName');

// View视图路由
Route::view('/mvc','mvc');
Route::view('/error','error',['code'=>403]);


// Query Builder
Route::get('/query/get','Test\TestController@query1');
Route::get('/query/where','Test\TestController@query2');

//view
Route::get('/test/test','Test\TestController@test');
Route::get('/test/child','Test\TestController@child');

//注册
Route::get('/register','User\UserController@register');
Route::post('/register','User\UserController@doreg');

// 登录
Route::get('/melogin','User\UserController@login');
Route::post('/melogin','User\UserController@dologin');

//退出
Route::get('/quit','User\UserController@quit');
//mvc
Route::get('/mvc/test','Mvc\mvcController@mvc');

//展示
Route::get('/show','User\UserController@show');

//测试
Route::get('/cookie','Test\TestController@cookieTest');   //cookie
Route::get('/cookie2','Test\TestController@cookieTest2');  //cookie
Route::get('/order/pay/show','Test\TestController@test');  //guzzlehttp

//购物车 Cart
Route::get('/cart/cart','Cart\CartController@cart')->middleware('check.login'); //购物车展示
Route::get('/cart/add/{g_id}','Cart\CartController@cartAdd');  //购物车添加
Route::get('/cart/del/{g_id}','Cart\CartController@cartDel');  //购物车删除
Route::get('/cart/add2','Cart\CartController@goodsAdd');
Route::post('/cart/add2','Cart\CartController@goodsAdd');   //购物车数据库添加
Route::get('/cart/del2/{g_id}','Cart\CartController@del2');   //购物车数据库删除

//商品展示 Goods
Route::get('/goods/show/{g_id}','Goods\GoodsController@show');  //商品单个展示
Route::get('/goods/list','Goods\GoodsController@goodsList');    //商品列表展示

//订单 Order
Route::get('/order/add','Order\OrderController@add');//订单添加
Route::get('/order/show','Order\OrderController@show'); //订单展示
Route::get('/order/cancel/{order_sn}','Order\OrderController@cancel'); //订单取消
Route::get('/order/detail','Order\OrderController@detailShow'); //订单详情展示

//支付 Pay
Route::get('pay/show/{order_sn}','Pay\PayController@show');  //支付
Route::get('/pay/alipay/test','Pay\AlipayController@test');         //测试
Route::get('/pay/order/{o_id}','Pay\AlipayController@pay');        //订单支付
Route::post('/pay/alipay/notify','Pay\AlipayController@aliNotify');        //支付宝支付 异步通知回调
Route::get('/pay/alipay/returnpay','Pay\AlipayController@aliReturn');        //支付宝支付 同步通知回调
Route::get('/pay/alipay/del','Pay\AlipayController@del');

//用户登录  考试
Route::get('exam/login','Login\LoginController@login');
Route::post('exam/login','Login\LoginController@loginTest');
Route::get('redis','Login\LoginController@redis');
Route::get('/update','Login\LoginController@pwd');
Route::post('/update','Login\LoginController@pwdUpdate');


//微信
Route::get('weixin/token','Weixin\WeixinController@refreshToken');  //刷新token
Route::get('weixin/valid','Weixin\WeixinController@validToken');
Route::post('weixin/valid','Weixin\WeixinController@validToken');
Route::get('weixin/valid1','Weixin\WeixinController@validToken1');   //接受微信服务器推送事件
Route::post('weixin/valid1','Weixin\WeixinController@wxEvent');
Route::get('weixin/create_menu','Weixin\WeixinController@createMenu');    //创建菜单










Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

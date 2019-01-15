<?php

namespace App\Http\Controllers\Pay;

use App\Model\OrderModel;
use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    //

    public function index(){

    }

    /**
     * 订单支付
     *
     */
    public function order($order_sn){
        //查询订单
        $order_info = OrderModel::where(['order_sn'=>$order_sn])->first();
        if(!$order_info){
            die("订单 ".$order_sn. "不存在！");
        }
        //检查订单状态 是否已支付 已过期 已删除
        if($order_info->pay_time > 0){
            die("此订单已被支付，无法再次支付");
        }

        //调起支付宝支付


        //支付成功 修改支付时间
        OrderModel::where(['order_sn'=>$order_sn])->update(['pay_time'=>time(),'pay_amount'=>rand(1111,9999),'is_pay'=>1]);

        //增加消费积分 ...

        header('Refresh:2;url=/user/center');
        echo '支付成功，正在跳转';

    }
}

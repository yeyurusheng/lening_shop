<?php

namespace App\Http\Controllers\Weixin;

use App\Model\WeixinUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class WeixinController extends Controller{
    protected $redis_weixin_access_token = 'str:weixin_access_token';

    public function test()
    {
        //echo __METHOD__;
        //$this->getWXAccessToken();
        $this->getUserInfo(1);
    }

    /**
     * 首次接入
     */
    public function validToken1()
    {
        echo $_GET['echostr'];
    }

    /**
     * 接收事件推送
     */
    public function validToken(){
        $data = file_get_contents("php://input");
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
    }

    /**
     * 刷新token
     */
    public function refreshToken(){
        Redis::del($this->redis_weixin_access_token);
        echo $this->getWXAccessToken();
    }

    /**
     * 下载图片文件
     */
    public function dwImage($media_id){
        $client = new GuzzleHttp\Client();
        //echo '<pre>';var_dump($client);echo '</pre>';
        //获取access_token
        $access_token = $this->getWXAccessToken();
        //拼接下载图片的URL
        $url='https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id='.$media_id;
        //使用GuzzleHttp下载文件
        $response = $client->get($url);
        //获取文件名称
        $file_info=$response->getHeader('Content-disposition');
        $file_name=substr(rtrim($file_info[0],'"'),-20);
        $WxImageSavePath='wx/image/'.$file_name;
        //保存路径/home/wwwroot/shop/storage/app/wx/images
        //保存图片
        $res=Storage::disk('local')->put($WxImageSavePath,$response->getBody());
        if($res){
            //保存成功
            echo '保存图片成功';
            return true;
        }else{
            //保存失败
            echo '保存图片失败';
            return false;
        }
    }

    /**
     * 下载语音文件
     */
    public function dlVoice($media_id){
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getWXAccessToken().'&media_id='.$media_id;
        //保存图片
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);
        $wx_image_path = 'wx/voice/'.$file_name;
        //保存语音
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){   //保存成功
            return true;
        }else{   //保存失败
            return false;
        }

    }

    /**
     * 接收微信服务器事件推送
     */
    public function wxEvent(){
        $data = file_get_contents("php://input");
        //解析XML
        $xml = simplexml_load_string($data);        //将 xml字符串 转换成对象
        //var_dump($xml);exit;
        $event = $xml->Event;               //事件类型
        $openid = $xml->FromUserName;               //用户openid

        //处理用户发送信息
        if(isset($xml->MsgType)){
            if($xml->MsgType=='text'){   //用户发送文本信息
                $msg = $xml->Content;
                $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. $msg. date('Y-m-d H:i:s') .']]></Content></xml>';
                echo $xml_response;
            }elseif($xml->MsgType=='image'){   //用户发送图片信息
                //判断是否需要保存图片信息
                if(1){   //下载图片信息
                    $this->dwImage($xml->MediaId);
                    $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. date('Y-m-d H:i:s') .']]></Content></xml>';
                    echo $xml_response;
                }
            }elseif($xml->MsgType=='voice'){   //处理语音文件
                $this->dlVoice($xml->MediaId);
            }elseif($xml->MsgType=='event'){   //处理事件类型
                if($event=='subscribe'){       //扫码关注事件
                    $sub_time = $xml->CreateTime;    //扫码关注时间
                    //获取用户信息
                    $user_info = $this->getUserInfo($openid);
                    //保存用户信息
                    $u = WeixinUser::where(['openid'=>$openid])->first();
                    if($u){     //用户不存在
                        echo '用户不存在';
                    }else{
                        $user_data = [
                            'openid' => $openid,
                            'add_time' => time(),
                            "nickname"=>$user_info["nickname"],
                            'sex' => $user_info['sex'],
                            'headimgurl' => $user_info['headimgurl'],
                            'subscribe_time' => $sub_time,
                        ];
                    }
                }elseif($event=='CLICK'){
                    if($xml->EventKey=='kefu'){
                        $this->kefu($openid,$xml->ToUserName);
                    }
                }
            }
        }


//        $log_str=date('Y-m-d H:i:s')."\n".$data."\n<<<<<<";
//        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
    }



    /**
     * 客服处理
     */
    public function kefu($openid,$from){
        //文本信息
        $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$from.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. '欢迎来到这里, 现在是北京时间'. date('Y-m-d H:i:s') .']]></Content></xml>';
        echo $xml_response;
    }

    /**
     * 获取微信access_token
     * @return mixed
     */
    public function getWXAccessToken(){
        $token=Redis::get($this->redis_weixin_access_token);
        if(!$token){
            //无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
            $data=json_decode(file_get_contents($url),true);
            //记录缓存
            $token=$data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);

        }

        return $token;
    }
    /**
     * 获取用户信息
     */
    public function getUserInfo($openid){
        //$openid='wxc28bbb4fa8c63d2b';
        $access_token = $this->getWXAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $data = json_decode(file_get_contents($url),true);
        //echo '<pre>';print_r($data);echo'</pre>';
        return $data;
    }
    /**
     * 创建服务号菜单
     */
    public function createMenu(){
        //获取access_token拼接请求接口
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getWXAccessToken();

        //请求微信接口
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $data=[
            "button"    =>[
                [   "type"=>"view",      //view类型 跳转指定
                    "name"=>"wei",
                    "url" =>"https://www.baidu.com"
                ],
                [
                    "type"  =>  "click",
                    "name"  =>  "客服",
                    "key"   =>  "kefu"
                ]
            ]
        ];
        $body = json_encode($data,JSON_UNESCAPED_UNICODE);    //处理中文编码
        $r = $client->request('POST', $url, [
            'body' => $body
        ]);
        // 解析微信接口返回信息
        $response_arr=json_decode($r->getBody(),true);
        if($response_arr['errcode']==0){
            echo "菜单创建成功";
        }else{
            echo "菜单创建失败，请重试";echo '<br>';
            echo $response_arr['errmsg'];
        }
    }
}

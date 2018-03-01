<?php
namespace app\wechat\controller;
use think\Controller;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Factory;
use EasyWeChat\Message\News;
use api\Shopinfo;
class Wechat extends Controller
{
    //变量定义
    public $app;
    public $userinfo;
    /**
     * 初始化
     * @return void
     */
    public function __construct(){
        parent::__construct();

        $this->app = new Application(config('wechat_config'));
        // dump(config('wechat_config'));die;
    }

    public function index(){
        // 从项目实例中得到服务端应用实例。
        $server = $this->app->server;

        $server->setMessageHandler(function ($message) {
            // $message->FromUserName // 用户的 openid
            // $message->MsgType // 消息类型：event, text....
            
            $result = '';
            //获取用户信息
            $userService = $this->app->user;
            //所有操作均判断用户信息是否存在、不存在则插入用户信息
            $user_info = model('CustomerAuth')->getCustomerInfo('', $message->FromUserName, 'xarwx');
            if(!$user_info){
                $user_info = $userService->get($message->FromUserName);
                $user_info = controller('api/Customer')->addCustomer($user_info, 1, 'xarwx');
            }
            // file_put_contents('user.txt', json_encode($user_info));
            switch ($message->MsgType) {
                case 'event':
                    $result = $this->eventHandle($message, $user_info);
                    break;
                case 'location':
                    $result = $this->locationHandle($message);
                    break;
                default:
                    break;
            }
            if(!$result){
                $messageHandle = new messageHandle($message, $user_info);
                $result = $messageHandle->responseMessage();
            }
            return $result;
        });

        $response = $server->serve();

        $response->send(); // Laravel 里请使用：return $response;
    }

    public function eventHandle($message, $userinfo){
        $event_str = $x_key_str = '';
        switch ($message->Event) {
            case 'subscribe':
                $event_str = '';
                //这里需判断 否则未关注用户进入后无推送
                $x_key_str = $message->EventKey;

                if($x_key_str){
                    //扫参数二维码回复参数二维码消息
                    $x_key_str = str_replace('qrscene_', '', $x_key_str);
                    $event_str = controller('Scanwxqr')->analyze_qr($x_key_str,$userinfo);
                }else{
                    //普通关注渠道，回复正常关注消息
                }

                break;
            case 'SCAN':
                $x_tmp_str1 = $x_tmp_str2 = $x_tmp_str3 = '';
                $x_key_str = $message->EventKey;
                if($x_key_str){
                    $event_str = controller('Scanwxqr')->analyze_qr($x_key_str,$userinfo);
                    // dump($event_str);
                    //$event_str = print_r($user_info,true).'----';
                }
                break;
            default:
                $event_str = '';
                break;
        }
        return $event_str;
    }

    public function locationHandle($message){

        $shop_info = model('shopInfo')->getNearOneShop($message->Location_X, $message->Location_Y);
        $news = new News();
        $news->title = '您附近总共有'.$shop_info['shop_num'].'家店、离您最近的店是：'.$shop_info['shop_name'];
        $news->description = '距离您：'.$shop_info['juli'].'米
地址：'.$shop_info['shop_address'].'
联系电话:'.$shop_info['shop_tel'];
        $news->url = 'http://mall.gotomore.cn/index.html?#/tmap';
        $news->image = $shop_info['shop_img'];
        return $news;
    }

    public function test(){
        // $message =(object) [
        //     'EventKey' => 'product|x|51|x|23',
        //     'Event' => 'text',
        //     'Content' => 'id'
        // ];
        // $messageHandle = new messageHandle($message, ['customer_id'=>1]);
        // $result = $messageHandle->responseMessage();
        $image_path = download_img('https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEw7zwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAydEJmRXdLc1FibC0xekE4Z3hxY2sAAgTke2haAwQAjScA');
        $res = $this->app->material_temporary->uploadImage($image_path);
        dump($res->media_id);
    }


}
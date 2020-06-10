<?php
namespace Home\Controller;
use Common\Controller\BaseController;
use Home\Tool\HJCTool;
use Home\Tool\Secret;
class VerifyController extends BaseController
{
    //arr: code safeword software_id user_id token computer_uid
    private $arr = array();
    //statu str expiretime
    private $returnarr = array();
    private $key;
    private $iv;
    private $safeword;
    public function __construct()
    {
        parent::__construct();
        $this->key = 'resunoon';
        $this->iv = '3364946464643364';
        $this->safeword = 'imsaferightnow';
    }

    public function signin() {
        //检查数据合法性(传入形式:json('data' => '密文'))
        if(!isset($_POST['data'])){
            echo "<h3 style='text-align:center;'>非法访问该页面<br>你的IP为<br>". HJCTool::getRealIP() . '</h3>';
             die();
        }
        //有data就尝试解密
        $this->arr = json_decode($this->process($_POST['data']), true) ;
        //验证安全信息防止别人搞事情
        if(!isset($this->arr['safeword']) && $this->safeword != $this->arr['safeword']){
            $this->returnjson(0, '你在尝试破解，不如来加我QQ2497732985');
        }
        if($this->arr['code'] == ''){
            $this->returnjson(0, '注册码为空');
        }

        //连上服务器
        $sql = M('regcode');
        //防SQL注入
        $endsql = "WHERE software_id = '" . $this->arr['software_id'] . "' AND user_id = '" . $this->arr['user_id'] . "' AND code = '". $this->arr['code'] ."'";
        $ret = $sql->query("SELECT * FROM cloud_regcode ". $endsql );
        if(!$ret){
            $this->returnjson(0, '卡密不存在');
        }

        //都好了之后去检查一下能不能登录
        $this->check($ret);
        //检查完就登录了
        $this->login($ret);
    }
    
    private function check($ret){
        // 连接服务器之前先进行检查
        //验证卡密是否存在
        if ($ret[0]['overdue'] == 1) {
            $this->offline();
            $this->returnJson('404', '注册码已过期');
        }
        if ($ret[0]['expire_time']) {
            if (strtotime(date('Y-m-d H:i:s', time())) - strtotime($ret[0]['expire_time']) > 0) {
                $sql = M('regcode');
                $sql->execute("UPDATE cloud_regcode SET overdue = 1 WHERE code = '{$this->arr->code}'");
                $this->offline();
                $this->returnJson('0', '注册码已过期');
            }
        }
        if($ret[0]['frozen'] == '1'){
            $this->offline();
            $this->returnjson(0,'被冻结了');
        }
        //短路机制，token不存在就不处理了，存在的话如果和服务器不一样就说明不能登录
        //token生成方法:md5(ip)
        if($ret[0]['token'] != '' && $ret[0]['token'] != $this->arr['token']){
            $this->returnjson(0,'卡密无效');
        }
    }


    private function login($ret){
        // overdue computeruid beginuse_time expire_time last_time last_ip token
        $code = $ret[0]['code'];
        //第一次登录的情况， use_count == 0
        if($ret[0]['use_count'] == 0){
            $all_minutes = $ret[0]['all_minutes'];

            $computer_uid = $this->arr['computer_uid'];
            $beginuse_time = date('Y-m-d H:i:s', time());
            $expire_time = date('Y-m-d H:i:s', strtotime("+{$all_minutes} minute"));
            
            $last_time = date('Y-m-d H:i:s', time());
            $last_ip = HJCTool::getRealIP();
            //@TODO 计算出token
            $token = substr(md5($computer_uid), 0, 5);

            $sql = M('regcode');
            $exesql = $sql->execute("UPDATE cloud_regcode SET beginuse_time = '{$beginuse_time}', expire_time = '{$expire_time}', computer_uid = '{$computer_uid}', last_time = '{$last_time}', last_ip = '{$last_ip}', use_count = 1, isonline = 1, token = '{$token}' WHERE code = '{$code}'");
        }else{

            if($this->arr['token'] != $ret[0]['token']){
                $this->returnjson(0,'注册码已被使用');
            }

            $last_time = date('Y-m-d H:i:s', time());
            $last_ip = HJCTool::getRealIP();
            $use_count = $ret[0]['use_count'] = 1;

            $sql = M('regcode');
            $exesql = $sql->execute("UPDATE cloud_regcode SET last_time = '{$last_time}', last_ip = '{$last_ip}', use_count='{$use_count}', isonline = 1 WHERE code = '{$code}'");
        }

        if(!$exesql){
            $this->returnJson('0', '数据库错误');
        }

        $this->returnarr['expire_time'] = $ret[0]['expire_time']?$ret[0]['expire_time']:$expire_time;
        
        $this->returnjson(1,'欢迎使用');
    }

    private function offline()
    {
        $mysql = M('Regcode');
        $code = $this->arr['code'];
        $mysql->execute("UPDATE cloud_regcode SET isonline = 0 WHERE code = '{$code}'");
    }

    private function process($data){
        return openssl_decrypt($data,'AES-128-CBC', $this->key, 0, $this->iv);
    }

/**
 * @param int $statu 登陆状态,0不登录，1登陆
 * @param string $str 返回的消息
 */
    private function returnjson($statu, $str){
        $this->returnarr['statu'] = "{$statu}";
        $this->returnarr['str'] = $str;
        echo openssl_encrypt(json_encode($this->returnarr), 'AES-128-CBC', $this->key, 0, $this->iv); 
        die();
    }


}


    
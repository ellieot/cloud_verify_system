<?php
namespace Home\Controller;

use Common\Controller\BaseController;
use Home\Tool\HJCTool;
use Home\Tool\Secret;
require getcwd() . '/Application/Home/Common/Rsa/BigInteger.php';
require getcwd() . '/Application/Home/Common/Rsa/rsa.php';
require getcwd() . '/Application/Home/Common/Des/DES.php';
class AuthController extends BaseController
{
    private $_jsonArr;
    private $_param;
    private $key;
    public function __construct()
    {
        parent::__construct();
    //    $this->key = substr(md5($_SERVER['SERVER_NAME']),0,8);
        $this->key = 'Resunoon';
    }
    public function getnotice()
    {
            $mysql1 = M('Notice');
            $ret1 = $mysql1->query("SELECT * FROM cloud_notice ");
            echo json_encode($ret1);
            exit;
    }
    public function login_user()
    {
        header('Content-Type:application/json');//var result = $.parseJSON(data);
        //UserName=admin123&PassWord=123456
        $username = $_POST['UserName'];
        $password = HJCTool::secret( $_POST['PassWord']);
        $mysql = M('user');
        $user_ret = $mysql->query("SELECT * FROM cloud_user WHERE username='" . $username . "'");
        if (!$user_ret) {
             $ret = ["Code" => 404,"Msg" => '用户不存在'];
             echo json_encode($ret);
             exit;
        } else {
            if ($user_ret[0]['password'] != $password) {
                 $ret = ["Code" => 404,"Msg" => '密码不正确'];
                 echo json_encode($ret);
                 exit;
            }
        }
        $ret = ["Code" => 200,"Msg" => '请求成功'];
        $ret["UserName"] = $user_ret[0]['username'];
        $ret["Reg_Time"] = $user_ret[0]['reg_time'];
        $ret["Login_Count"] = $user_ret[0]['login_count'];
        $ret["Login_Time"] = $user_ret[0]['currentlogin_time'];
        if($user_ret[0]['vip']==1){
        $ret["Vip"] = true;
        }else{
        $ret["Vip"] = false;
        }
        echo json_encode($ret);
        exit;
    }
    //楼上是端接口




    /*
    Op=操作符
    Mac=机器码
    Appid=应用id
    Card=注册码
    Ip=请求ip
    Ver=版本号
    Time=请求时间戳
    Token=返回token
    Type=试用 正式
    
    Code码 200成功 404失败 300更新
    */
    public function signin()
    {
        $this->beforeHandle();
        // Op=操作 -1初始化,0登录,1解绑,2试用,3机器码取卡密
        switch ($this->_jsonArr->Op) {
            case -1:
                $this->init();
                break;
            case 0:
                $this->login();
                break;
            case 1:
                $this->unbind();
                break;
            case 2:
                $this->sy();
                break;
            case 3:
                $this->getcard();
                break;
            case 4:
                $this->ApiLogin();
                break;
        }
    }
    public function check()
    {
        $this->beforeHandle();
        $softret = $this->isSoftwareFrozen();
        // formal正式,trial试用
        if ($this->_jsonArr->Type == 'formal') {
            $ret = $this->isCodeValid();
            if ($ret[0]['computer_uid'] == '') {
                $this->offline();
                $this->returnJson('404', '验证不通过,请重新登录!');
            }
            if ($ret[0]['computer_uid'] != $this->_jsonArr->Mac) {
                $this->offline();
                $this->returnJson('404', '验证不通过,机器码被改变!');
            }
            if ($ret[0]['token'] != $this->_jsonArr->Token) {
                $this->returnJson('404', '验证不通过,禁止多开!');
            }
            $mysql = M('Regcode');
            $code = $this->_jsonArr->Card;
            $update_ret = $mysql->execute("UPDATE cloud_regcode SET isonline = 1 WHERE code = '{$code}'");
            $this->returnJson('200', '验证通过');
        } else {
            if ($this->_jsonArr->Type == 'trial') {
                $computer_uid = $this->_jsonArr->Mac;
                $softid = $softret[0]['id'];
                $trialsql = M('Trial');
                $trialret = $trialsql->query("SELECT * FROM cloud_trial WHERE computer_uid = '{$computer_uid}' AND software_id = '{$softid}'");
                if (!$trialret) {
                    $this->returnJson('404', '验证不通过!');
                }
                // 时间差
                $cle = time() - strtotime($trialret[0]['last_time']);
                $m = $cle / 60;
                // 如果大于软件最大使用时间
                if ($m > $softret[0]['try_minutes']) {
                    $this->returnJson('404', '验证不通过,试用到期!');
                } else {
                    // token是否改变
                    if ($trialret[0]['Token'] != $this->_jsonArr->Token) {
                        $this->returnJson('404', '验证不通过,禁止多开!');
                    } else {
                        $this->returnJson('200', '验证通过');
                    }
                }
            } else {
                $ret = $this->isCodeValid();
                if ($ret[0]['computer_uid'] == '') {
                    $this->offline();
                    $this->returnJson('404', '验证不通过,请重新登录!');
                }
                if ($ret[0]['computer_uid'] != $this->_jsonArr->Mac) {
                    $this->offline();
                    $this->returnJson('404', '验证不通过,机器码被改变!');
                }
                if ($ret[0]['token'] != $this->_jsonArr->Token) {
                    $this->returnJson('404', '验证不通过,禁止多开!');
                }
                $mysql = M('Regcode');
                $code = $this->_jsonArr->Card;
                $update_ret = $mysql->execute("UPDATE cloud_regcode SET isonline = 1 WHERE code = '{$code}'");
                $this->returnJson('200', '验证通过');
            }
        }
    }
    //解绑
    private function unbind()
    {
        $softret = $this->isSoftwareFrozen();
        $ret = $this->isCodeValid();
        if ($softret[0]['unbindmode'] == '1') {
            $this->returnJson('404', '解绑失败,该软件不允许用户解绑!');
        }
        if ($ret[0]['computer_uid'] != '') {
            if ($ret[0]['computer_uid'] != $this->_jsonArr->Mac) {
                $this->returnJson('404', '解绑失败,只能在原设备解绑!');
            }
        } else {
            $this->returnJson('404', '解绑失败,已经解绑过了!');
        }
        $mysql = M('Regcode');
        $code = $this->_jsonArr->Card;
        $update_ret = $mysql->execute("UPDATE cloud_regcode SET computer_uid = '', isonline = 0 WHERE code = '{$code}'");
        if (!$update_ret) {
            $this->returnJson('404', '解绑失败!');
        } else {
            $this->returnJson('200', '解绑成功!');
        }
    }
    private function ApiLogin()
    {
        $this->beforeHandle();
        $username = $this->_jsonArr->User;
        $password = HJCTool::secret($this->_jsonArr->Pass);
        $mysql = M('user');
        $user_ret = $mysql->query("SELECT * FROM cloud_user WHERE username='" . $username . "'");
        if (!$user_ret) {
            $this->returnJson('404', '用户不存在!');
        } else {
            if ($user_ret[0]['password'] != $password) {
                $this->returnJson('404', '密码不正确!');
            } else {
                if ($user_ret[0]['vip'] == 0) {
                    $this->returnJson('404', '未审核');
                }
            }
        }
        $id = $user_ret[0]['id'];
        $lastlogin_time = $user_ret[0]['currentlogin_time'] ?: date('Y-m-d H:i:s', time());
        $lastlogin_ip = $user_ret[0]['currentlogin_ip'];
        $currentlogin_ip = HJCTool::getRealIP();
        $currentlogin_time = date('Y-m-d H:i:s', time());
        $login_count = $user_ret[0]['login_count'] + 1;
        $ret = $mysql->execute("UPDATE cloud_user SET lastlogin_time = '{$lastlogin_time}', lastlogin_ip = '{$lastlogin_ip}', currentlogin_time = '{$currentlogin_time}', currentlogin_ip = '{$currentlogin_ip}', login_count = '{$login_count}' WHERE id = '{$id}'");
        if ($ret) {
            $_SESSION['user'] = $username;
            $_SESSION['last_logn_time'] = time();
            $this->returnJson('200', '登录成功');
        } else {
            $this->returnJson('404', '未知异常!');
        }
    }
    private function sy()
    {
        $softret = $this->isSoftwareFrozen();
        $computer_uid = $this->_jsonArr->Mac;
        $softid = $this->_jsonArr->Appid;
        $last_ip = HJCTool::getRealIP();
        $last_time = date('Y-m-d H:i:s', time());
        $mysql = M('Software');
        $ret = $mysql->query("SELECT * FROM cloud_software WHERE id = {$softid}");
        if (!$ret) {
            $this->returnJson('404', '试用失败,软件没找到!');
        }
        $try_minutes = $ret[0]['try_minutes'];
        $try_count = $ret[0]['try_count'];
        if ($try_minutes <= 0 || $try_count <= 0) {
            $this->returnJson('404', '试用失败,软件不支持试用!');
        }
        $token = substr(md5($this->_jsonArr->Mac . time()), 0, 5);
        $trialsql = M('Trial');
        $trialret = $trialsql->query("SELECT * FROM cloud_trial WHERE computer_uid = '{$computer_uid}' AND software_id = '{$softid}'");
        // 机器码没找到,第一次试用
        if (!$trialret) {
            $update_ret = $trialsql->execute("INSERT INTO cloud_trial (computer_uid, software_id, last_ip, last_time, token) VALUES ('{$computer_uid}', '{$softid}', '{$last_ip}', '{$last_time}', '{$token}')");
            if ($update_ret) {
                $hours = $ret[0]['try_minutes'];
                $expire_time = date('Y-m-d H:i:s', strtotime("+{$hours} minute"));
                $this->_param['Expire_Time'] = $expire_time;
                $this->_param['Code'] = '200';
                $this->_param['Key'] = $softret[0]['jmkey'];
                $this->_param['Msg'] = '试用成功!还可以试用' . ($ret[0]['try_count'] - 1) . '次';
                $this->_param['Token'] = $token;
                $this->_param['Time'] = $this->_jsonArr->Time;
                $this->secretReturn($this->_param);
            }
            exit;
        }
        $remainder_count = $ret[0]['try_count'] - $trialret[0]['has_try_count'];
        if ($remainder_count > 0) {
            $has_try_count = $trialret[0]['has_try_count'] + 1;
            $update_ret = $trialsql->execute("UPDATE cloud_trial SET has_try_count = '{$has_try_count}', last_ip = '{$last_ip}', last_time = '{$last_time}', token = '{$token}' WHERE computer_uid = '{$computer_uid}' AND software_id = '{$softid}'");
            if ($update_ret) {
                $hours = $ret[0]['try_minutes'];
                $expire_time = date('Y-m-d H:i:s', strtotime("+{$hours} minute"));
                $this->_param['Expire_Time'] = $expire_time;
                $this->_param['Code'] = '200';
                $this->_param['Key'] = $softret[0]['jmkey'];
                $this->_param['Msg'] = '试用成功!还可以试用' . ($remainder_count - 1) . '次';
                $this->_param['Token'] = $token;
                $this->_param['Time'] = $this->_jsonArr->Time;
                $this->secretReturn($this->_param);
            }
        } else {
            $this->returnJson('404', '试用失败,试用次数已用完!');
        }
    }
    //初始化
    private function init()
    {
        $softid = $this->_jsonArr->Appid;
        $mysql = M('Software');
        $softret = $mysql->query("SELECT * FROM cloud_software WHERE id = {$softid}");
        if (!$softret) {
            $this->returnJson('404', '验证不通过,Appid不正确,没有该软件!');
        }
        if ($softret[0]['frozen'] == '1') {
            $this->returnJson('404', '验证不通过,软件被禁用!');
        }
        $this->_param['AuthMode'] = $softret[0]['authmode'];
        if($softret[0]['authmode'] == '0') {
        //网络验证模式
        if ($this->_jsonArr->Ver != '' && $this->_jsonArr->Ver != $softret[0]['version']) {
            $this->_param['Code'] = '300';
            $this->_param['Hint'] = $softret[0]['hint'];
            $this->_param['Edit'] = $softret[0]['edit'];
            //公告
            $this->_param['Msg'] = $softret[0]['gg'];
            //标题
            $this->_param['Title'] = $softret[0]['title'];
            //模式
            $this->_param['Model'] = $softret[0]['model'];
            //更新方式
            if ($softret[0]['updatemode'] == '1') {
                $this->_param['UpdateMode'] = true;
            } else {
                $this->_param['UpdateMode'] = false;
            }
            //更新提示内容
            $this->_param['UpdataMsg'] = $softret[0]['updatamsg'];
            //更新地址
            $this->_param['Url'] = $softret[0]['update_url'];
            //是否开启试用
            if ($softret[0]['sy'] == 1) {
                $this->_param['Sy'] = false;
            } else {
                $this->_param['Sy'] = true;
            }
            //是否开启发卡网
            if ($softret[0]['web'] == 1) {
                $this->_param['Web'] = false;
            } else {
                $this->_param['Web'] = true;
                //发卡网
                $this->_param['WebUrl'] = $softret[0]['weburl'];
            }
            //请求时间戳
            $this->_param['Time'] = $this->_jsonArr->Time;
            $this->secretReturn($this->_param);
        } else {
            //返回公告 标题 试用 发卡网
            $this->_param['Code'] = '200';
            $this->_param['Hint'] = $softret[0]['hint'];
            $this->_param['Edit'] = $softret[0]['edit'];
            //公告
            $this->_param['Msg'] = $softret[0]['gg'];
            //标题
            $this->_param['Title'] = $softret[0]['title'];
            //模式
            $this->_param['Model'] = $softret[0]['model'];
            //是否开启试用
            if ($softret[0]['sy'] == 1) {
                $this->_param['Sy'] = false;
            } else {
                $this->_param['Sy'] = true;
            }
            //是否开启发卡网
            if ($softret[0]['web'] == 1) {
                $this->_param['Web'] = false;
            } else {
                $this->_param['Web'] = true;
                //发卡网
                $this->_param['WebUrl'] = $softret[0]['weburl'];
            }
            //请求时间戳
            $this->_param['Time'] = $this->_jsonArr->Time;
            $this->secretReturn($this->_param);
        }
        }
    }
    // 注册:登录注册码修改数据库信息
    private function login()
    {
        $this->isComputerIdValid();
        $softret = $this->isSoftwareFrozen();
        $ret = $this->isCodeValid();
        if ($ret[0]['computer_uid'] != '') {
            // 只有绑机单开模式才需要判断机器码相不相同
            if ($softret[0]['bindmode'] == '0') {
                if ($ret[0]['computer_uid'] != $this->_jsonArr->Mac) {
                    $this->returnJson('404', '登录失败,该注册码已绑定其他设备!');
                }
            }
        }
        // 判断注册码和软件编号是否对应
        if ($ret[0]['software_id'] != $this->_jsonArr->Appid) {
            $this->returnJson('404', '登录失败,注册码不属于该软件!');
        }
        // 修改注册码的到期时间、机器码、登录时间、登录ip、登录次数、在线
        $hours = $ret[0]['all_minutes'] / 60;
        $expire_time = $ret[0]['expire_time'];
        $computer_uid = $this->_jsonArr->Mac;
        $last_time = date('Y-m-d H:i:s', time());
        $last_ip = HJCTool::getRealIP();
        $use_count = $ret[0]['use_count'] + 1;
        // 绑机单开:机器码+注册码+请求时间戳
        // 绑机多开:机器码+注册码
        // 不绑机多开:注册码
        switch ($softret[0]['bindmode']) {
            case '0':
                $token = substr(md5($computer_uid . $this->_jsonArr->Card . time()), 0, 5);
                break;
            case '1':
                $token = substr(md5($computer_uid . $this->_jsonArr->Card), 0, 5);
                break;
            case '2':
                $token = substr(md5($this->_jsonArr->Card), 0, 5);
                break;
        }
        $mysql = M('Software');
        $code = $this->_jsonArr->Card;
        if ($ret[0]['beginuse_time'] == '') {
            $beginuse_time = date('Y-m-d H:i:s', time());
            $expire_time = date('Y-m-d H:i:s', strtotime("+{$hours} hour"));
            $update_ret = $mysql->execute("UPDATE cloud_regcode SET beginuse_time = '{$beginuse_time}', expire_time = '{$expire_time}', computer_uid = '{$computer_uid}', last_time = '{$last_time}', last_ip = '{$last_ip}', use_count = 1, isonline = 1, token = '{$token}' WHERE code = '{$code}'");
        } else {
            $update_ret = $mysql->execute("UPDATE cloud_regcode SET computer_uid = '{$computer_uid}', last_time = '{$last_time}', last_ip = '{$last_ip}', use_count = '{$use_count}', isonline = 1, token = '{$token}' WHERE code = '{$code}'");
        }
        if (!$update_ret) {
            $this->returnJson('404', '登录异常!');
        }
        $this->_param['Code'] = '200';
        $this->_param['Msg'] = '校验成功';
        $this->_param['Token'] = $token;
        $this->_param['Key'] = $softret[0]['jmkey'];
        $this->_param['Expire_Time'] = $expire_time;
        $this->_param['Time'] = $this->_jsonArr->Time;
        $this->secretReturn($this->_param);
    }
    // 下线
    private function offline()
    {
        $mysql = M('Regcode');
        $code = $this->_jsonArr->Card;
        $mysql->execute("UPDATE cloud_regcode SET isonline = 0 WHERE code = '{$code}'");
    }
    //检测参数

    
    private function beforeHandle()
    {
        if (!isset($_POST['data'])) {
            $this->returnJson('404', '非法参数!');
        }
        //decrypt
        $data = $_POST['data'];
        $des = \DES::decrypt($this->key, base64_decode($data));
        $this->_jsonArr = json_decode($des);
    }
    //检测机器码
    private function isComputerIdValid()
    {
        if ($this->_jsonArr->Mac == '') {
            $this->returnJson('404', '验证不通过,机器码为空!');
        }
    }
    // 软件是否禁用
    private function isSoftwareFrozen()
    {
        // 软件是否禁用
        $softid = $this->_jsonArr->Appid;
        $mysql = M('Software');
        $ret = $mysql->query("SELECT * FROM cloud_software WHERE id = {$softid}");
        // 软件没找到
        if (!$ret) {
            $this->offline();
            $this->returnJson('404', '验证不通过,Appid不正确,没有该软件!');
        }
        if ($ret[0]['frozen'] == '1') {
            $this->offline();
            $this->returnJson('404', '验证不通过,软件被禁用!');
        }
        return $ret;
    }
    // 注册码是否可用
    private function isCodeValid()
    {
        $code = $this->_jsonArr->Card;
        if ($code == '') {
            $this->returnJson('404', '注册码不能为空!');
        }
        $mysql = M('Regcode');
        $ret = $mysql->query("SELECT * FROM cloud_regcode WHERE code = '{$code}'");
        // 注册码没找到
        if (!$ret) {
            $this->returnJson('404', '注册码不存在!');
        }
        // 注册码过期
        if ($ret[0]['overdue'] == 1) {
            $this->offline();
            $this->returnJson('404', '注册码已过期!');
        }
        if ($ret[0]['expire_time']) {
            if (strtotime(date('Y-m-d H:i:s', time())) - strtotime($ret[0]['expire_time']) > 0) {
                $mysql->execute("UPDATE cloud_regcode SET overdue = 1 WHERE code = '{$code}'");
                $this->offline();
                $this->returnJson('404', '注册码已过期!');
            }
        }
        // 注册码冻结
        if ($ret[0]['frozen'] == 1) {
            $this->offline();
            $this->returnJson('404', '注册码被冻结!');
        }
        return $ret;
    }
    //机器码取卡密
    private function getcard()
    {
        $code = $this->_jsonArr->Mac;
        if ($code == '') {
            $this->returnJson('404', '机器码不能为空!');
        }
        $mysql = M('Regcode');
        $ret = $mysql->query("SELECT * FROM cloud_regcode WHERE computer_uid = '{$code}'");
        // 机器码没找到
        if (!$ret) {
            $this->returnJson('404', '机器码不存在!');
        }
        $this->_param['Code'] = '200';
        $this->_param['Msg'] = $ret[0]['code'];
        $this->_param['Time'] = $this->_jsonArr->Time;
        $this->secretReturn($this->_param);
    }
    // 保存上次的error编号并返回json
    private function returnJson($error, $msg)
    {
        $this->_param['Code'] = $error;
        $this->_param['Msg'] = $msg;
        $this->secretReturn($this->_param);
        exit;
    }
    // 返回json
    private function secretReturn($data)
    {
        $data['Time'] = $this->_jsonArr->Time;
        //$data['Anti'] = 'MTJlODc3YmQ0YjcxOTUyZjEzMjgyOTU5NGZkYjdiYmU=';
        $data['AntiMsg'] = "安全信息校验失败\r\n请联系QQ2497732985";
        $anti = array();
        $anti[0] = ["Anti"=>"M2E1ZDQ2NTY0YjFlM2Y0MGY3MDEyYWZjZDJlMTdhNmY="];
        $anti[1] = ["Anti"=>"NmRmOWIxMDQzMDAzZjI2NTk4MGEyMTk1MDZmNTViZmY="];
        $anti[2] = ["Anti"=>""];
        $anti[3] = ["Anti"=>""];
        $data['AntiList'] = $anti;
        header('Content-Type:application/json; charset=utf-8');
        $des = \DES::encrypt($this->key, json_encode($data));
        $out = base64_encode($des);
        echo $out;
    }
}
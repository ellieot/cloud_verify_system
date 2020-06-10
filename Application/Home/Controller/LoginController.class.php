<?php
namespace Home\Controller;

use Common\Controller\BaseController;
use hdphp\page\Page;
use Home\Tool\HJCTool;

class LoginController extends BaseController {
    public function index() {
        $this->init();
        $this->display();
    }

    private function init() {
        if (!isset($_POST['login'])) {
            return;
        }
        if (I('post.valid') == '') {
            HJCTool::alertBack('请输入验证码');
            return;
        }
        if (!$this->check_verify(I('post.valid'))) {
            HJCTool::alertBack('验证码错误');
        }
        if ($this->isEmpty(I('post.username', '', '/^[a-z0-9A-Z]{6,16}$/'))) {
            HJCTool::alertBack('用户名要6-16位英文字母或数字');
        }
        if ($this->isEmpty(I('post.password', '', '/^[a-z0-9A-Z]{6,16}$/'))) {
            HJCTool::alertBack('密码要6-16位英文字母或数字');
        }
        $username = I('post.username');
        $password = HJCTool::secret(I('post.password'));

        $mysql = M('user');
        $user_ret = $mysql->query("SELECT * FROM cloud_user WHERE username='" . $username . "'");
        if (!$user_ret) {
            HJCTool::alertBack('用户名不存在');
        } else {
            if ($user_ret[0]['password'] != $password) {
                HJCTool::alertBack('密码不正确');
            }
            //else if ($user_ret[0]['vip'] == 0) {
            //    HJCTool::alertBack('商户未审核 请联系QQ2497732985进行审核');
            //}
        }

        $id = $user_ret[0]['id'];
        $lastlogin_time = $user_ret[0]['currentlogin_time'] ? : date('Y-m-d H:i:s', time());  //登陆了就不刷了
        $lastlogin_ip = $user_ret[0]['currentlogin_ip'];    //更换上次登陆的ip
        $currentlogin_ip = HJCTool::getRealIP();            //更新当前ip地址
        $currentlogin_time = date('Y-m-d H:i:s', time());   //更新当前登陆时间
        $login_count = $user_ret[0]['login_count'] + 1;
        
        $ret = $mysql->execute("UPDATE cloud_user SET lastlogin_time = '$lastlogin_time', lastlogin_ip = '$lastlogin_ip', currentlogin_time = '$currentlogin_time', currentlogin_ip = '$currentlogin_ip', login_count = '$login_count' WHERE id = '$id'");
        if ($ret) {
            $_SESSION['user'] = $username;
            $_SESSION['last_logn_time'] = time();
            HJCTool::alertToLocation(null, '/Home');
        } else {
            HJCTool::alertBack('数据库异常');
        }
    }

}
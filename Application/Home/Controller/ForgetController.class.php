<?php
namespace Home\Controller;

use Common\Controller\BaseController;
use hdphp\page\Page;
use Home\Tool\HJCTool;

require getcwd() . '/Application/Home/Common/Email/email.class.php';

class ForgetController extends BaseController {
    public function index() {
        $this->init();
        $this->display();
    }

    private function init() {
        //HJCTool::alertToLocation('暂时不支持找回密码', '/Home/login');
        if (!isset($_POST['forget'])) {
            return;
        }
        if (I('post.valid') == '') {
            // 这里不用alertBack因为验证码需要刷新页面
            HJCTool::alertToLocation('请输入验证码', '/Home/forget');
            return;
        }
        if (!$this->check_verify(I('post.valid'))) {
            HJCTool::alertToLocation('验证码错误', '/Home/forget');
        }
        if ($this->isEmpty(I('post.username', '', '/^[a-z0-9A-Z]{6,16}$/'))) {
            HJCTool::alertToLocation('账号格式错误: 请输入6-16位英文字母或数字', '/Home/forget');
        }
        if (I('post.email', '', FILTER_VALIDATE_EMAIL) == '') {
            HJCTool::alertToLocation('邮箱格式错误', '/Home/forget');
        }
        // 先看邮箱和用户名是不是对应
        $username = I('post.username');
        $email = I('post.email');
        $mysql = M();
        $ret = $mysql->query("SELECT * FROM cloud_user WHERE username = '$username' AND email = '$email'");
        if (!$ret) {
            HJCTool::alertToLocation('用户名或邮箱不正确', '/Home/forget');
        }
        // 如果有时间就判断是否小于xx时间 小于就返回
        $time = date('Y-m-d H:i:s', time());
        if ($ret[0]['forget_time']) {
            $second = strtotime($time) - strtotime($ret[0]['forget_time']);
            if ($second < 30 * 60) {
                HJCTool::alertToLocation('30分钟内请勿重新提交', '/Home/forget');
            }
        }

        // 生成随机密码 修改数据库 并发送邮件
        $newPwd = $this->rand();
        $secretPwd = HJCTool::secret($newPwd);
        $updateret = $mysql->execute("UPDATE cloud_user SET forget_time = '$time', password = '$secretPwd' WHERE username = '$username'");
        if ($updateret) {
            $this->sendEmail($username, $newPwd, $email);
        } else {
            HJCTool::alertToLocation('未知错误', '/Home/forget');
        }

    }

    //获取随机数
    private function rand( $len = 6 ) {
        $data = '0123456789';
        $str  = '';
        while ( strlen( $str ) < $len ) {
            $str .= substr( $data, mt_rand( 0, strlen( $data ) - 1 ), 1 );
        }
        return $str;
    }

    private function sendEmail($username, $password, $smtpemailto) {

    $mail = new \PHPMailer();                           //PHPMailer对象
    $mail->CharSet = 'UTF-8';                         //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                                   // 设定使用SMTP服务
    $mail->SMTPDebug = 0;                             // 关闭SMTP调试功能 1 = errors and messages2 = messages only
    $mail->SMTPAuth = true;                           // 启用 SMTP 验证功能
    $mail->Host = 'smtp.qq.com';                // SMTP 服务器
    $mail->Port = '25';                // SMTP服务器的端口号(腾讯默认是25)
    $mail->Username = '2497732985@qq.com';           // SMTP服务器用户名
    $mail->Password = 'fcbxkktpeqlxdidf';           // SMTP服务器密码
    $mail->SetFrom('2497732985@qq.com', 'Resunoon');
    $mail->AddReplyTo(null, null);
    $mail->Subject = '新密码';
    $mail->MsgHTML($username . "你好，已经给你重置了密码 ：" . $password . "。");
    $mail->AddAddress($smtpemailto, null);
    $rs =  $mail->Send() ? true : $mail->ErrorInfo;
    if(!$rs){
            HJCTool::alertToLocation('出错了，联系我qq:2497732985', '/Home/forget');
        } else {
            HJCTool::alertToLocation('邮件已发送,请及时查收邮件', '/Home/login');
        }
    }
}
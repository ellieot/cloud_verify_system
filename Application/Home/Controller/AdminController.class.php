<?php
namespace Home\Controller;
use Common\Controller\BaseController;
use Home\Tool\HJCTool;
use Home\Tool\Secret;

class AdminController extends BaseController {
    public function index(){
        $this->assign('admin_selected', 'selected');
        parent::showTableAndPage2('user',null);
        $this->init();
        $this->display();
    }
    private function init() {
        if($this->getUser()[0]['username'] != 'admin123'){
              HJCTool::alertToLocation('你不是系统管理员,你无权操作', '/Home');
              return;
         }
        // 删除注册码
        if (I('novip') != '') {
            $this->novip();
        }
        if (I('vip') != '') {
            $this->vip();
        }
        if (I('del') != '') {
            $this->del();
        }
        if (I('xgpass') != '') {
            $this->xgpass();
        }
    }
    private function xgpass(){
        if (I('newpass', '', '/^[a-z0-9A-Z]{6,16}$/') == '') {
            HJCTool::alertToLocation('密码格式错误: 请输入6-16位英文字母或数字','Admin');
        }
        
        $user = I('xgpass');
        $pass = HJCTool::secret(I('newpass'));
        $mysql = M('User');
        $updateret = $mysql->execute("UPDATE cloud_user SET password = '$pass' WHERE username = '$user'");
        if ($updateret) {
            HJCTool::alertToLocation('修改成功 ' . $user,'Admin');
        } else {
            HJCTool::alertToLocation('修改失败 ' . $user,'Admin');
        }
    }
    private function del(){
        $user = I('del');
        $mysql = M('User');
        $updateret = $mysql->execute("DELETE FROM cloud_user WHERE username = '$user'");
        if ($updateret) {
            HJCTool::alertToLocation('删除成功 ' . $user,'Admin');
        } else {
            HJCTool::alertToLocation('删除失败 ' . $user,'Admin');
        }
    }
    private function novip(){
        $user = I('novip');
        $mysql = M('User');
        $updateret = $mysql->execute("UPDATE cloud_user SET vip = '0' WHERE username = '$user'");
        if ($updateret) {
            HJCTool::alertToLocation('修改成功 ' . $user,'Admin');
        } else {
            HJCTool::alertToLocation('修改失败 ' . $user,'Admin');
        }
    }
    private function vip(){
        $user = I('vip');
        $mysql = M('User');
        $updateret = $mysql->execute("UPDATE cloud_user SET vip = '1' WHERE username = '$user'");
        if ($updateret) {
            HJCTool::alertToLocation('修改成功 ' . $user,'Admin');
        } else {
            HJCTool::alertToLocation('修改失败 ' . $user,'Admin');
        }
    }
}
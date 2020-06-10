<?php
namespace Home\Controller;
use Common\Controller\BaseController;
use Home\Tool\HJCTool;
use Home\Tool\Secret;

class GgController extends BaseController {

    public function index(){
        $this->assign('gg_selected', 'selected');
        $this->init();
        $this->display();
    }

    private function init() {
        if($this->getUser()[0]['username'] != 'admin123'){
              HJCTool::alertToLocation('你不是系统管理员,你无权操作', '/Home');
              return;
         }
    
        if (!I('content')) {
            return;
        }
        if (!I('title')) {
            return;
        }

        $submitTime = date('Y-m-d H:i:s', time());
        $content = I('content');
        $title = I('title');

        $mysql = M();
        $mysql->execute("INSERT INTO cloud_notice SET creat_time = '$submitTime', content = '$content',title = '$title'");
        HJCTool::alertToLocation('发布成功', '/Home/Gg');
    }

}
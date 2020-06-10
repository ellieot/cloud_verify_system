<?php
namespace Home\Controller;
use Common\Controller\BaseController;
use Home\Tool\HJCTool;
use Home\Tool\Secret;

class VipController extends BaseController {
    public function index(){
        $this->assign('vip_selected', 'selected');
        $this->display();
    }
}
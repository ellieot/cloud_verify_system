<?php
return array(//'配置项'=>'配置值'
    'MODULE_ALLOW_LIST'    =>    array('Home','Index'),
    'DEFAULT_MODULE'       =>    'Index',
    'URL_MODEL' => 2, 
    
    'DB_TYPE' => 'mysql', 'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'cloud', // 数据库名
    'DB_USER' => 'Resunoon', // 用户名
    'DB_PWD' => 'Mynameisdxm3364', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'cloud_', // 数据库表前缀
    'DB_CHARSET' => 'utf8', // 字符集
    'DB_DEBUG' => false, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
    'SHOW_PAGE_TRACE'=>false, // 调试工具显示
    'SESSION_AUTO_START' => true, // 开启SESSION
);
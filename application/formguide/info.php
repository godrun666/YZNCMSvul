<?php
// +----------------------------------------------------------------------
// | Yzncms [ 御宅男工作室 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://yzncms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 御宅男 <530765310@qq.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 表单模块信息文件
// +----------------------------------------------------------------------
return array(
    //模块名称[必填]
    'name' => '表单模块',
    //模块简介[选填]
    'introduce' => '这是一个cms必备的表单提交模块！',
    //模块作者[选填]
    'author' => 'yzncms',
    //作者地址[选填]
    'authorsite' => 'http://www.yzncms.com',
    //作者邮箱[选填]
    'authoremail' => '530765310@qq.com',
    //版本号，请不要带除数字外的其他字符[必填]
    'version' => '1.0.0',
    //适配最低yzncms版本[必填]
    'adaptation' => '1.0.0',
    //签名[必填]
    'sign' => '1fa2d9a6f16e75616918c57ce3b88440',
    //依赖模块
    'need_module' => [
        ['cms', '1.0.0'],
    ],
    //依赖插件
    'need_plugin' => [],
    //行为注册
    'tags' => [],
    //缓存，格式：缓存key=>array('module','model','action')
    'cache' => [
        'Model_form' => [
            'name' => '自定义表单模型',
            'model' => 'Formguide',
            'action' => 'formguide_cache',
        ],
    ],
    // 数据表，请加表前缀yzn[有数据库表时必填]
    'tables' => [],
);

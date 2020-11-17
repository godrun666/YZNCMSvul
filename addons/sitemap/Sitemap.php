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
// | 网站地图插件
// +----------------------------------------------------------------------
namespace addons\sitemap;

use think\Addons;

class Sitemap extends Addons
{
    //插件信息
    public $info = [
        'name'          => 'sitemap',
        'title'         => '网站地图',
        'description'   => 'sitemap网站地图让搜索引擎对您网站的更快、更完整地进行索引，为您进行网站推广带来极大的方便',
        'status'        => 1,
        'author'        => '御宅男',
        'version'       => '1.0.0',
        'has_adminlist' => 1,
    ];

    //安装
    public function install()
    {
        return true;
    }

    //卸载
    public function uninstall()
    {
        return true;
    }

}

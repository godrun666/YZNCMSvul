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
// | 表单模型
// +----------------------------------------------------------------------
namespace app\formguide\model;

use app\cms\model\Cms as Cms_Model;
use think\Db;

class Formguide extends Cms_Model
{
    protected $name = 'ModelField';
    protected $autoWriteTimestamp = false;

    //添加模型内容
    public function addFormguideData($formid, $data, $dataExt = [])
    {
        //完整表名获取
        $tablename = $this->getModelTableName($formid);
        if (!$this->table_exists($tablename)) {
            throw new \Exception('数据表不存在！');
        }
        $uid = 0;
        $username = "游客";
        if (isModuleInstall('member')) {
            $uid = \app\admin\service\User::instance()->id ?: 0;
            $username = \app\admin\service\User::instance()->username ?: '游客';
        }
        $data['uid'] = $uid;
        $data['username'] = $username;
        //处理数据
        $dataAll = $this->dealModelPostData($formid, $data, $dataExt);
        list($data, $dataExt) = $dataAll;
        $data['inputtime'] = request()->time();
        $data['ip'] = request()->ip(1);
        try {
            //主表
            $id = Db::name($tablename)->insertGetId($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $id;
    }

    //
    public function getFieldList($modelId, $id = null)
    {
        $list = self::where('modelid', $modelId)->where('status', 1)->order('listorder asc,id asc')->column("name,title,remark,type,isadd,iscore,ifsystem,ifrequire,setting");
        if (!empty($list)) {
            //编辑信息时查询出已有信息
            if ($id) {
                $modelInfo = Db::name('Model')->where('id', $modelId)->field('tablename,type')->find();
                $dataInfo = Db::name($modelInfo['tablename'])->where('id', $id)->find();
            }
            foreach ($list as $key => &$value) {
                if ($value['iscore']) {
                    unset($list[$key]);
                }

                if ($value['ifsystem']) {
                    $value['fieldArr'] = 'modelField';
                    if (isset($dataInfo[$value['name']])) {
                        $value['value'] = $dataInfo[$value['name']];
                    }
                } else {
                    $value['fieldArr'] = 'modelFieldExt';
                    if (isset($dataInfoExt[$value['name']])) {
                        $value['value'] = $dataInfoExt[$value['name']];
                    }
                }

                //扩展配置
                $value['setting'] = unserialize($value['setting']);
                $value['options'] = $value['setting']['options'];

                if ('' != $value['options']) {
                    $value['options'] = parse_attr($value['options']);
                }
                if ($value['type'] == 'select') {
                    $value['value'] = empty($value['value']) ? '' : $value['options'][$value['value']];
                }
                if ($value['type'] == 'checkbox') {
                    $value['value'] = empty($value['value']) ? [] : explode(',', $value['value']);
                }
                if ($value['type'] == 'datetime') {
                    $value['value'] = empty($value['value']) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $value['value']);
                }
                if ($value['type'] == 'date') {
                    $value['value'] = empty($value['value']) ? '' : date('Y-m-d', $value['value']);
                }

                if ($value['type'] == 'Ueditor') {
                    $value['value'] = htmlspecialchars_decode($value['value']);

                }
            }
        }
        return $list;
    }

    //缓存生成
    public function formguide_cache()
    {
        $formguide_cache = model('admin/Models')->getModelAll(null, 'formguide');
        if (!empty($formguide_cache)) {
            cache('Model_form', $formguide_cache);
        }
        return $formguide_cache;
    }

}

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
// | 字段模型
// +----------------------------------------------------------------------
namespace app\cms\model;

use app\common\model\Modelbase;
use think\Db;

/**
 * 字段模型
 */
class ModelField extends Modelbase
{
    protected $autoWriteTimestamp = true;
    protected $insert = ['status' => 1];

    //添加字段
    public function addField($data = null)
    {
        $data['name'] = strtolower($data['name']);
        $data['ifsystem'] = isset($data['ifsystem']) ? intval($data['ifsystem']) : 0;
        //模型id
        $modelid = $data['modelid'];
        //完整表名获取 判断主表 还是副表
        $tablename = $this->getModelTableName($modelid, $data['ifsystem']);
        if (!$this->table_exists($tablename)) {
            $this->error = '数据表不存在！';
            return false;
        }
        $tablename = config('database.prefix') . $tablename;
        //判断字段名唯一性
        if ($this->where('name', $data['name'])->where('modelid', $modelid)->value('id')) {
            $this->error = "字段'" . $data['name'] . "`已经存在";
            return false;
        }

        $data['ifeditable'] = isset($data['ifeditable']) ? intval($data['ifeditable']) : 0;
        $data['ifrequire'] = isset($data['ifrequire']) ? intval($data['ifrequire']) : 0;
        if ($data['ifrequire'] && !$data['ifeditable']) {
            $this->error = '必填字段不可以隐藏！';
            return false;
        }
        //先将字段存在设置的主表或附表里面 再将数据存入ModelField
        $sql = <<<EOF
            ALTER TABLE `{$tablename}`
            ADD COLUMN `{$data['name']}` {$data['define']} COMMENT '{$data['title']}';
EOF;
        try {
            $res = Db::execute($sql);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        $fieldInfo = Db::name('field_type')->where('name', $data['type'])->field('ifoption,ifstring')->find();
        //只有主表文本类字段才可支持搜索
        $data['ifsearch'] = isset($data['ifsearch']) ? ($fieldInfo['ifstring'] && $data['ifsystem'] ? intval($data['ifsearch']) : 0) : 0;
        $data['status'] = isset($data['status']) ? intval($data['status']) : 0;
        $data['iffixed'] = 0;
        $data['options'] = $fieldInfo['ifoption'] ? $data['options'] : '';
        $fieldid = self::create($data, true);
        if ($fieldid) {
            //清理缓存
            cache('ModelField', null);
            return true;
        } else {
            $this->error = '字段信息入库失败！';
            //回滚
            Db::execute("ALTER TABLE  `{$tablename}` DROP  `{$data['name']}`");
            return false;
        }
        return true;
    }

    /**
     *  编辑字段
     * @param type $data 编辑字段数据
     * @param type $fieldid 字段id
     * @return boolean
     */
    public function editField($data, $fieldid = 0)
    {
        $data['name'] = strtolower($data['name']);
        $data['ifsystem'] = isset($data['ifsystem']) ? intval($data['ifsystem']) : 0;
        if (!$fieldid && !isset($data['fieldid'])) {
            $this->error = '缺少字段id！';
            return false;
        } else {
            $fieldid = $fieldid ? $fieldid : (int) $data['fieldid'];
        }
        //原字段信息
        $info = self::where(array("id" => $fieldid))->find();
        if (empty($info)) {
            $this->error = '该字段不存在！';
            return false;
        }
        //模型id
        $data['modelid'] = $modelid = $info['modelid'];
        //完整表名获取 判断主表 还是副表
        $tablename = $this->getModelTableName($modelid, $data['ifsystem']);
        if (!$this->table_exists($tablename)) {
            $this->error = '数据表不存在！';
            return false;
        }
        $tablename = config('database.prefix') . $tablename;
        //判断字段名唯一性
        if ($this->where('name', $data['name'])->where('modelid', $modelid)->where('id', '<>', $fieldid)->value('id')) {
            $this->error = "字段'" . $data['name'] . "`已经存在";
            return false;
        }
        $data['ifeditable'] = isset($data['ifeditable']) ? intval($data['ifeditable']) : 0;
        $data['ifrequire'] = isset($data['ifrequire']) ? intval($data['ifrequire']) : 0;
        if ($data['ifrequire'] && !$data['ifeditable']) {
            $this->error = '必填字段不可以隐藏！';
            return false;
        }
        $sql = <<<EOF
            ALTER TABLE `{$tablename}`
            CHANGE COLUMN `{$info['name']}` `{$data['name']}` {$data['define']} COMMENT '{$data['title']}';
EOF;
        try {
            Db::execute($sql);
        } catch (\Exception $e) {
            $this->addField($data);
        }
        $fieldInfo = Db::name('field_type')->where('name', $data['type'])->field('ifoption,ifstring')->find();
        //只有主表文本类字段才可支持搜索
        $data['ifsearch'] = isset($data['ifsearch']) ? ($fieldInfo['ifstring'] && $data['ifsystem'] ? intval($data['ifsearch']) : 0) : 0;
        $data['status'] = isset($data['status']) ? intval($data['status']) : 0;
        $data['options'] = $fieldInfo['ifoption'] ? $data['options'] : '';
        //清理缓存
        cache('ModelField', null);
        self::update($data, ['id' => $fieldid], true);
        return true;
    }

    /**
     * 删除字段
     * @param type $fieldid 字段id
     * @return boolean
     */
    public function deleteField($fieldid)
    {

        //原字段信息
        $info = self::where(array("id" => $fieldid))->find();
        if (empty($info)) {
            $this->error = '该字段不存在！';
            return false;
        }
        //模型id
        $modelid = $info['modelid'];
        //完整表名获取 判断主表 还是副表
        $tablename = $this->getModelTableName($modelid, $info['ifsystem']);
        if (!$this->table_exists($tablename)) {
            $this->error = '数据表不存在！';
            return false;
        }
        $tablename = config('database.prefix') . $tablename;

        //判断是否允许删除
        $sql = <<<EOF
            ALTER TABLE `{$tablename}`
            DROP COLUMN `{$info['name']}`;
EOF;
        try {
            $res = Db::execute($sql);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        self::get($fieldid)->delete();
        return true;
    }

    //查询解析模型数据用以构造from表单
    public function getFieldList($modelId, $id = null)
    {

        $list = self::where('modelid', $modelId)->where('status', 1)->order('listorder asc,id asc')->column("name,title,remark,type,value,options,ifsystem,ifeditable,ifrequire,jsonrule");
        if (!empty($list)) {
            //编辑信息时查询出已有信息
            if ($id) {
                $modelInfo = Db::name('Model')->where('id', $modelId)->field('table,type')->find();
                $dataInfo = Db::name($modelInfo['table'])->where('cname', $id)->find();
                //查询附表信息
                if ($modelInfo['type'] == 2 && !empty($dataInfo)) {
                    $dataInfoExt = Db::name($modelInfo['table'] . $this->ext_table)->where('did', $dataInfo['id'])->find();
                }
            }
            foreach ($list as &$value) {
                if (isset($value['ifsystem'])) {
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
                }
                //解析字段关联规则
                $dataRule = [];
                /*if ('' != $value['jsonrule']) {
                $dataRule = json_decode($value['jsonrule'], true);
                }*/
                if ('' != $value['options']) {
                    $value['options'] = parse_attr($value['options']);
                } elseif (isset($dataRule['choose'])) {
                    $value['options'] = Db::name($dataRule['choose']['table'])->where($dataRule['choose']['where'])->limit($dataRule['choose']['limit'])->order($dataRule['choose']['order'])->column($dataRule['choose']['key'] . ',' . $dataRule['choose']['value']);
                }
                if ('' == $value['value'] && isset($dataRule['string'])) {
                    $stringArray = Db::name($dataRule['string']['table'])->where($dataRule['string']['where'])->limit($dataRule['string']['limit'])->order($dataRule['string']['order'])->column($dataRule['string']['key']);
                    if (!empty($stringArray)) {
                        $value['value'] = implode($dataRule['string']['delimiter'], $stringArray);
                    }
                }
                if ($value['type'] == 'checkbox') {
                    $value['value'] = (strlen($value['value']) > 2) ? substr($value['value'], 1, -1) : '';
                    $value['value'] = empty($value['value']) ? [] : explode(',', $value['value']);
                }
                if ($value['type'] == 'datetime') {
                    $value['value'] = empty($value['value']) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $value['value']);
                }
                if ($value['type'] == 'date') {
                    $value['value'] = empty($value['value']) ? '' : date('Y-m-d', $value['value']);
                }
                if ($value['type'] == 'image') {
                    $value['param'] = ['dir' => 'images', 'module' => 'admin'];
                    if (isset($dataRule['thumb']['ifon'])) {
                        $value['param']['thumb'] = 1;
                        $value['param']['thumbsize'] = $dataRule['thumb']['size'];
                        $value['param']['thumbtype'] = $dataRule['thumb']['type'];
                    }
                }
                if ($value['type'] == 'images') {
                    $value['param'] = ['dir' => 'images', 'module' => 'admin'];
                    if (isset($dataRule['thumb']['ifon'])) {
                        $value['param']['thumb'] = 1;
                        $value['param']['thumbsize'] = $dataRule['thumb']['size'];
                        $value['param']['thumbtype'] = $dataRule['thumb']['type'];
                    }
                    if (!empty($value['value'])) {
                        $value['value'] .= ',';
                    }
                }
                if ($value['type'] == 'files') {
                    $value['param'] = ['dir' => 'files', 'module' => 'admin'];
                    if (isset($dataRule['file']['type'])) {
                        $value['param']['sizelimit'] = $dataRule['file']['size'];
                        $value['param']['extlimit'] = $dataRule['file']['type'];
                    }
                    if (!empty($value['value'])) {
                        $value['value'] .= ',';
                    }
                }
                if ($value['type'] == 'Ueditor') {
                    $value['value'] = htmlspecialchars_decode($value['value']);
                }
                if ($value['type'] == 'summernote') {
                    $value['value'] = htmlspecialchars_decode($value['value']);
                }
            }
        }
        return $list;
    }

    /**
     * 根据模型ID，返回表名
     * @param type $modelid
     * @param type $modelid
     * @return string
     */
    protected function getModelTableName($modelid, $ifsystem = 1)
    {
        //读取模型配置 以后优化缓存形式
        $model_cache = cache("Model");
        //表名获取
        $model_table = $model_cache[$modelid]['tablename'];
        //完整表名获取 判断主表 还是副表
        $tablename = $ifsystem ? $model_table : $model_table . "_data";
        return $tablename;
    }

    //生成模型字段缓存
    public function model_field_cache()
    {
        $cache = array();
        $modelList = Db::name("Model")->select();
        foreach ($modelList as $info) {
            $data = Db::name("ModelField")->where(array("modelid" => $info['id'], "status" => 1))->order("listorder ASC")->select();
            $fieldList = array();
            if (!empty($data) && is_array($data)) {
                foreach ($data as $rs) {
                    $fieldList[$rs['name']] = $rs;
                }
            }
            $cache[$info['id']] = $fieldList;
        }
        cache('ModelField', $cache);
        return $cache;
    }

}

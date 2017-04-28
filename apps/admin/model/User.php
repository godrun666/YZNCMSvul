<?php
// +----------------------------------------------------------------------
// | Yzncms [ 御宅男工作室 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2007 http://yzncms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 御宅男 <530765310@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;
use think\Model;
use think\Validate;

/**
 * 管理员模型
 */
class User extends Model {
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN__';

	/**
	 * 管理员登录检测
     * @param string $username 用户名
     * @param string $password 密码
	 */
	public function checkLogin($username = '', $password = ''){
        $condition['username'] = trim($username);
        $condition['password'] = trim($password);
        $admin_info = $this->where(['username'=>$condition['username']]) ->find();
        if($admin_info)$admin_info =$admin_info ->toArray();
        if(is_array($admin_info)){
            /* 验证用户密码 */
            if($admin_info['password']==sha1($condition['password'].$admin_info['encrypt'])){
                /* 密码正确 自动登录用户 */
                $this->autoLogin($admin_info);
                return true;
            }
        }
        $this->error ='账号密码不正确！';
        return false;
	}

    /**
     * 自动登录用户
     */
    private function autoLogin($user){
    	/* 更新登录信息 */
        $data = array(
            'last_login_time' => time(),
            'last_login_ip'   => get_client_ip(1),
        );
        $this->where(array('userid'=>$user['userid']))->update($data);
    	/* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid'             => $user['userid'],
            'username'        => $user['nickname'],
            'last_login_time' => $user['last_login_time'],
        );
        session('last_login_time',$user['last_login_time']);
        session('last_login_ip',$user['last_login_ip']);

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));//签名
    }

    /**
     * 创建管理员
     * @param type $data
     * @return boolean
     */
    public function createManager($data) {
        if (empty($data)) {
            $this->error = '没有数据！';
            return false;
        }
        //验证器
        $rule = [
            'username'  => 'unique:admin|require|alphaDash|length:3,15',
            'password'  => 'require|length:6,20|confirm',
            'email'     => 'email',
        ];
        $msg = [
            'username.unique' => '用户名已经存在！',
            'username.require' => '用户名不能为空！',
            'username.alphaDash' => '用户名格式不正确！',
            'username.length' => '用户名长度不正确！',
            'password.require'     => '密码不能为空！',
            'password.length'     => '密码长度不正确！',
            'password.confirm'        => '两次输入的密码不一样！',
            'email.email'        => '邮箱地址有误！',
        ];
        $validate = new Validate($rule,$msg);
        if (!$validate->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
        $passwordinfo = password($data['password']);//对密码进行处理
        $data['password'] = $passwordinfo['password'];
        $data['encrypt']  = $passwordinfo['encrypt'];
        $id = $this->allowField(true)->save($data);
        if ($id) {
            return $id;
        }
        $this->error = '入库失败！';
        return false;
    }















}
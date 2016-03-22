<?php
defined('ZOTOP') OR die('No direct access allowed.');
/*
* 会员 后台控制器
*
* @package		member
* @version		1.0
* @author		zotop.chenlei
* @copyright	zotop.chenlei
* @license		http://www.zotop.com
*/
class member_controller_field extends admin_controller
{
	protected $field;
	protected $model;

	/**
	 * 重载__init函数
	 */
	public function __init()
	{
		parent::__init();

		//实例化field
		$this->field = m('member.field');
		$this->model = m('member.model');
	}

	/**
	 * 字段列表页面
	 * 
	 * @param  string $modelid 模型编号
	 * @return mixed
	 */
	public function action_index($modelid='member')
    {
		if ( $post = $this->post() )
		{
			if ( $this->field->order($post['id']) )
			{
				return $this->success(t('操作成功'));
			}

			return $this->error($this->field->error());
		}

		// 获取模型字段
		$data   = $this->field->where('modelid',$modelid)->select();
		
		//获取全部模型数据
		$models = $this->model->select();
		

		$this->assign('title',t('字段管理'));
		$this->assign('data',$data);
		$this->assign('models',$models);
		$this->assign('modelid',$modelid);
		$this->assign('controls',$this->field->controls);
		$this->display();
	}

	/**
	 * 添加
	 *
	 */
	public function action_add($modelid='')
    {
		if ( $post = $this->post() )
		{
			if ( $this->field->add($post) )
			{
				return $this->success(t('保存成功'),u('member/field/index/'.$post['modelid']));
			}

			return $this->error($this->field->error());
		}

		$data = array(
			'modelid'	=> $modelid,
			'control'	=> 'text',
			'type'		=> $this->field->controls['text']['type'],
			'length'	=> $this->field->controls['text']['length']
		);

		$models = arr::hashmap($this->model->select(),'id','name');

		$this->assign('title',t('添加字段'));
		$this->assign('data',$data);
		$this->assign('models',$models);
		$this->assign('controls',$this->field->controls);
		$this->assign('control_options',$this->field->control_options());
		$this->display('member/field_post.php');
	}

	/**
	 * 编辑
	 *
	 */
	public function action_edit($id)
    {
		if ( $post = $this->post() )
		{
			if ( $this->field->edit($post, $id) )
			{
				return $this->success(t('保存成功'),u('member/field/index/'.$post['modelid']));
			}

			return $this->error($this->field->error());
		}

		$data = $this->field->get($id);

		$models = arr::hashmap($this->model->select(),'id','name');

		$this->assign('title',t('编辑字段'));
		$this->assign('data',$data);
		$this->assign('models',$models);
		$this->assign('controls',$this->field->controls);
		$this->assign('control_options',$this->field->control_options());
		$this->display('member/field_post.php');
	}

	/**
	 * 删除
	 *
	 */
	public function action_delete($id)
	{
		if( $this->post() )
		{
			if ( $this->field->delete($id) )
			{
				return $this->success(t('删除成功'),u('member/field'));
			}

			return $this->error($this->field->error());
		}
	}

    /**
     * 检查字段名称是否被占用
     *
     * @return bool
     */
	public function action_check($key, $ignore='')
	{
		$ignore = empty($ignore) ? $_GET['ignore'] : $ignore;

		if ( empty($ignore) )
		{
			$count = $this->user->where($key,$_GET[$key])->count();
		}
		else
		{
			$count = $this->user->where($key,$_GET[$key])->where($key,'!=',$ignore)->count();
		}

		exit($count ? '"'.t('已经存在，请重新输入').'"' : 'true');
	}

	// 获取控件的设置
	public function action_settings()
	{
		//debug::dump($_POST);
		//exit();

		// 获取当前控件
		$control = $_POST['control'];

		// 获取全部控件
		$controls = $this->field->controls;

		// 获取模板
		$template = $controls[$control]['settings'] ?  $controls[$control]['settings'] : A('member.path').DS.'templates'.DS.'field_settings'.DS.$control.'.php';

		if ( !file::exists($template) )
		{
			exit('');
		}

		$this->assign('data',$_POST);
		$this->display($template);
	}

	/**
	 * 设置数组
	 *
	 */
	private function settings_fields($data)
	{
		$settings_fields = array();

		foreach ( $this->settings_fields as $name => $setting )
		{
			$settings_fields[$name]['label']          = $setting['label'];
			$settings_fields[$name]['tips']           = $setting['tip'];
			$settings_fields[$name]['for']            = $name;
			$settings_fields[$name]['required']       = $setting['required'];
			
			$settings_fields[$name]['field']['id']    = "settings_fields_{$name}";
			$settings_fields[$name]['field']['name']  = "settings_fields[{$name}]";
			$settings_fields[$name]['field']['value'] = isset($data['settings_fields'][$name]) ? $data['settings_fields'][$name]  : $setting['value'];

			// 其他属性全部都设为字段属性
			foreach ( $setting as $k=>$s )
			{
				if ( in_array($k, array('label','tip','name','value','id')) ) continue;

				$settings_fields[$name]['field'][$k] = $s;
			}
		}

		return $settings_fields;
	}
}
?>
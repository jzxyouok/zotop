<?php
defined('ZOTOP') OR die('No direct access allowed.');
/*
* [app.name] api
*
* @package		[app.id]
* @version		[app.version]
* @author		[app.author]
* @copyright	[app.author]
* @license		[app.homepage]
*/
class [app.id]_hook
{
	/**
	 * HOOK [system.start] 注册快捷方式
	 *
	 * @param  array $attrs 已有快捷方式数组
	 * @return string 控件代码
	 */
	public static function start($start)
	{
		$start['[app.id]'] = array(
			'text'        => A('[app.id].name'),
			'href'        => U('[app.id]/admin'),
			'icon'        => A('[app.id].url') . '/app.png',
			'description' => A('[app.id].description')
		);

		return $start;
	}

	/**
	 * HOOK
	 * 
	 * @param  array $nav 已有快捷导航数组
	 * @return array
	 */
	public static function global_navbar($nav)
	{
		$nav['[app.id]'] = array(
			'text'        => A('[app.id].name'),
			'href'        => U('[app.id]'),
			'icon'        => A('[app.id].url').'/app.png',
			'description' => A('[app.id].description'),
			'allow'       => priv::allow('[app.id]'),
			'active'      => (ZOTOP_APP == '[app.id]')
		);

		return $nav;
	}	
}
?>
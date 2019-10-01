<?php

header("Content-Type: text/html; charset=UTF-8");

session_start();

$bufflist_258 = array(0.2, 0.5, 0.8, 1.1, 1.4);
$bufflist_246 = array(0.2, 0.4, 0.6, 0.8, 1.0);
$bufflist_015 = array(0.2 * 0.75, 0.4 * 0.75, 0.6 * 0.75, 0.8 * 0.75, 1.0 * 0.75);
$bufflist_010 = array(0.2 * 0.50, 0.4 * 0.50, 0.6 * 0.50, 0.8 * 0.50, 1.0 * 0.50);
$bufflist_005 = array(0.2 * 0.25, 0.4 * 0.25, 0.6 * 0.25, 0.8 * 0.25, 1.0 * 0.25);
$bufflist_035 = array(0.2 * 1.75, 0.4 * 1.75, 0.6 * 1.75, 0.8 * 1.75, 1.0 * 1.75);

$GLOBALS['house_buff'] = array(
	'buffs_bus' => array(
	    '媒体之声'	=> $bufflist_005,
	    '企鹅机械'	=> $bufflist_015,
	    '民食斋'	=> $bufflist_246,
	    '纺织厂'	=> $bufflist_015,
	    '人才公寓'	=> $bufflist_246,
	    '中式小楼'	=> $bufflist_246,
	    '空中别墅'	=> $bufflist_258,
	    '电厂'		=> $bufflist_258,
	),
	'buffs_ind' => array(
	    '媒体之声'	=> $bufflist_005,
	    '钢铁厂'	=> $bufflist_015,
	    '中式小楼'	=> $bufflist_246,
	    '民食斋'	=> $bufflist_246,
	    '空中别墅'	=> $bufflist_258,
	    '电厂'		=> $bufflist_258,
	    '企鹅机械'	=> $bufflist_258,
	    '人才公寓'	=> $bufflist_035,
	),
	'buffs_res' => array(
	    '媒体之声'	=> $bufflist_005,
	    '企鹅机械'	=> $bufflist_010,
	    '民食斋'	=> $bufflist_246,
	    '人才公寓'	=> $bufflist_246,
	    '平房'		=> $bufflist_246,
	    '空中别墅'	=> $bufflist_258,
	    '电厂'		=> $bufflist_258,
	    '中式小楼'	=> $bufflist_035,
	),
);


$GLOBALS['house_arr'] = array(
	// 64c6ff 蓝色
	// ea77ff 紫色
	// ff9900 橙色
	'residence' => array(
		array('quality'=>'quality_blue', 'name'=>'木屋', ),
		array('quality'=>'quality_blue', 'name'=>'居民楼', ),
		array('quality'=>'quality_blue', 'name'=>'钢结构房', ),
		array('quality'=>'quality_blue', 'name'=>'平房', ),
		array('quality'=>'quality_blue', 'name'=>'小型公寓', ),

		array('quality'=>'quality_purple', 'name'=>'人才公寓', ),
		array('quality'=>'quality_purple', 'name'=>'中式小楼', ),
		array('quality'=>'quality_purple', 'name'=>'花园洋房', ),

		array('quality'=>'quality_orange', 'name'=>'空中别墅', ),
		array('quality'=>'quality_orange', 'name'=>'复兴公馆', ),
	),
	'business' => array(
		array('quality'=>'quality_blue', 'name'=>'便利店', ),
		array('quality'=>'quality_blue', 'name'=>'菜市场', ),
		array('quality'=>'quality_blue', 'name'=>'服装店', ),
		array('quality'=>'quality_blue', 'name'=>'五金店', ),
		array('quality'=>'quality_blue', 'name'=>'学校', ),

		array('quality'=>'quality_purple', 'name'=>'图书城', ),
		array('quality'=>'quality_purple', 'name'=>'加油站', ),
		array('quality'=>'quality_purple', 'name'=>'商贸中心', ),

		array('quality'=>'quality_orange', 'name'=>'民食斋', ),
		array('quality'=>'quality_orange', 'name'=>'媒体之声', ),
	),
	'industry' => array(
		array('quality'=>'quality_blue', 'name'=>'木材厂', ),
		array('quality'=>'quality_blue', 'name'=>'食品厂', ),
		array('quality'=>'quality_blue', 'name'=>'造纸厂', ),
		array('quality'=>'quality_blue', 'name'=>'水厂', ),
		array('quality'=>'quality_blue', 'name'=>'电厂', ),

		array('quality'=>'quality_purple', 'name'=>'纺织厂', ),
		array('quality'=>'quality_purple', 'name'=>'钢铁厂', ),
		array('quality'=>'quality_purple', 'name'=>'零件厂', ),

		array('quality'=>'quality_orange', 'name'=>'企鹅机械', ),
		array('quality'=>'quality_orange', 'name'=>'人民石油', ),
	),
	'special' => array(
		'花园洋房' 	=> 1.022,
		'商贸中心' 	=> 1.022,
		'平房' 		=> 1.1,
		'电厂' 		=> 1.18,
		'小型公寓' 	=> 1.18,
		'加油站' 	=> 1.204,
		'水厂' 		=> 1.26,
		'企鹅机械' 	=> 1.33,
		'中式小楼' 	=> 1.4,
		'人才公寓' 	=> 1.4,
		'民食斋' 	=> 1.52,
		'空中别墅' 	=> 1.52,
		'媒体之声' 	=> 1.615,
	),

	// 在集合内的全部算作0
	// 媒体之声 花园洋房 加油站 人民石油 商贸中心
	'level_0_setting' => array(
		'小型公寓','复兴公馆',
	),
);

// 对指定建筑加成，前者每星对后者增加 100% 或 50%
$GLOBALS['buffs_50_to_house'] = array(
	'零件厂' =>'企鹅机械',
	'加油站' =>'人民石油',
);
$GLOBALS['buffs_100_to_house'] = array(
	'木屋' => '木材厂',
	'居民楼' => '便利店',
	'钢结构房' => '钢铁厂',
	'花园洋房' => '商贸中心',
	'空中别墅' => '民食斋',

	'便利店' => '居民楼',
	'五金店' => '零件厂',
	'服装店' => '纺织厂',
	'菜市场' => '食品厂',
	'学校' =>  '图书城',
	'图书城' => '学校',
	'图书城' => '造纸厂',
	'商贸中心' => '花园洋房',

	'木材厂' => '木屋',
	'食品厂' => '菜市场',
	'造纸厂' => '图书城',
	'钢铁厂' => '钢结构房',
	'纺织厂' => '服装店',
	'零件厂' => '五金店',
	'企鹅机械' =>'零件厂',
	'人民石油' =>'加油站',
);


$GLOBALS['house_level_to_multiple'] = array(
	1 => 1,
	2 => 2,
	3 => 6,
	4 => 24,
	5 => 120,
);

$GLOBALS['JIAGUOZHIGUANG'] = 50; //%

?>

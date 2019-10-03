<?php

ini_set('display_errors','0');
ini_set('memory_limit','1024M');
ini_set('register_globals','off');
set_time_limit(0); //调整超时时间为无限制

require_once dirname(__FILE__).'/init.php';
require_once dirname(__FILE__).'/lib/PHPExcel.php';

$GLOBALS['debug'] = 0;
$action = $_GET['action'];
$building_income_file = dirname(__FILE__).'/doc/building_upgrade_data.xls';

switch ($action) {
	case 'init_building_list':
		response_json_msg(0, $GLOBALS['building_list']);
		break;
	case 'get_combination_data':

		if (!isset($_SESSION['IP'])) {
			$_SESSION['IP'] = get_ip();
			$_SESSION['TIME'] = time();
		}else{
			$this_ip = get_ip();
			if ($_SESSION['IP'] == $this_ip) {
				$time_diff = time() - $_SESSION['TIME'];
				if ($time_diff < 60) {
					// response_json_msg(1, '','限制每次请求间隔1分钟，请等待'.(60-$time_diff).'秒');
					// echo '限制每次请求间隔1分钟，请等待'.(60-$time_diff).'秒';
				}
			}
		}


		// 建筑收益 列表
		$building_income_list = array();

		// 建筑下一级的升级消耗 列表
		$building_next_level_consume_list = array();
		// 建筑下一级的升级收益 列表
		$building_next_level_income_list = array();

		// 建筑星级 列表
		$building_star_list = array();
		foreach ($_POST['building_star'] as $building_name => $building_star) {
			$building_star_list[$building_name] = $building_star;

			// 默认全部收益 = 1
			$building_income_list[$building_name] = 1;
		}

		// 建筑等级 列表
		$building_level_list = array();
		foreach ($_POST['building_level'] as $building_name => $building_level) {
			$building_level_list[$building_name] = $building_level;
		}

		// 建筑城市任务 列表
		$building_mission_list = array();
		foreach ($_POST['building_mission'] as $building_name => $building_mission) {
			$building_mission_list[$building_name] = $building_mission;
		}

		// 锁定0级不计算过滤
		if ($GLOBALS['building_list']['level_0_setting']) {
			foreach ($building_star_list as $building_name => $building_value) {
				if (in_array($building_name, $GLOBALS['building_list']['level_0_setting'])) {
					$building_star_list[$building_name] = 0;
				}
			}
		}

		// 政策 数据
		$policy_data = array(
			'policy_data_all' => $_POST['policy_data_all'],
		    'policy_data_res' => $_POST['policy_data_res'],
		    'policy_data_bus' => $_POST['policy_data_bus'],
		    'policy_data_ind' => $_POST['policy_data_ind'],
		    'policy_data_all_ol' => $_POST['policy_data_all_ol'],
		    'policy_data_res_ol' => $_POST['policy_data_res_ol'],
		    'policy_data_bus_ol' => $_POST['policy_data_bus_ol'],
		    'policy_data_ind_ol' => $_POST['policy_data_ind_ol'],
		    'policy_data_JGZG' => $_POST['policy_data_JGZG'],
		);

		// 政策中心计算
		$policy_center_global = $GLOBALS['policy_center'];
		$policy_center_post   = $_POST['policy_center'];

		if ($policy_center_post) {
			foreach ($policy_center_post as $policy_key => $policy_value) {
				if ($policy_value == 1) {
					$global_policy_key = $policy_key - 1;
					if (isset($policy_center_global[$global_policy_key]) && is_array($policy_center_global[$global_policy_key])) {
						foreach ($policy_center_global[$global_policy_key] as $center_key => $center_value) {
							$policy_data[$center_key] += $center_value;
						}
					}
				}
			}
		}

		// 相片 数据
		$photo_data = array(
			'photo_data_all' => $_POST['photo_data_all'],
		    'photo_data_res' => $_POST['photo_data_res'],
		    'photo_data_bus' => $_POST['photo_data_bus'],
		    'photo_data_ind' => $_POST['photo_data_ind'],
		    'photo_data_all_ol' => $_POST['photo_data_all_ol'],
		    'photo_data_res_ol' => $_POST['photo_data_res_ol'],
		    'photo_data_bus_ol' => $_POST['photo_data_bus_ol'],
		    'photo_data_ind_ol' => $_POST['photo_data_ind_ol'],
		);

		// 城市任务 数据
		$mission_data = array(
			'mission_data_all' => $_POST['mission_data_all'],
		    'mission_data_res' => $_POST['mission_data_res'],
		    'mission_data_bus' => $_POST['mission_data_bus'],
		    'mission_data_ind' => $_POST['mission_data_ind'],
		    'mission_data_all_ol' => $_POST['mission_data_all_ol'],
		    'mission_data_res_ol' => $_POST['mission_data_res_ol'],
		    'mission_data_bus_ol' => $_POST['mission_data_bus_ol'],
		    'mission_data_ind_ol' => $_POST['mission_data_ind_ol'],
		);


		// 计算方式
		$calculate_type = $_POST['calculate_type'];
		if (!in_array($calculate_type, array(1,2))) {
			response_json_msg(1, '', '计算类型选择错误');
		}else{
			// 收益-消耗 设置表
			$income_and_consume_settings_data = array();

			// 读取文件数据
			$PHPReader = new PHPExcel_Reader_Excel2007();
			if(!$PHPReader->canRead($building_income_file)) {
			    $PHPReader = new PHPExcel_Reader_Excel5();
			    if(!$PHPReader->canRead($building_income_file)) {
			        response_json_msg(1, '', '加载建筑升级收益数据失败');
			    }
			}
			$PHPExcel       = $PHPReader->load($building_income_file);         	//**建立excel对象*/
			$sheet          = $PHPExcel->getSheet(0);               			//**读取excel文件中的指定工作表*/
			$highestRow     = $sheet->getHighestRow();              			//**取得一共有多少行*/
			$highestColumm  = $sheet->getHighestColumn();           			//**取得最大的列号*/
			$highestColumm  = PHPExcel_Cell::columnIndexFromString($highestColumm);     //**取得最大的列号*/

			// 循环excel文件数据
			for ($row = 2; $row <= $highestRow; $row++) {
		        $level = $sheet->getCell('A'.$row)->getValue();
		        $income = $sheet->getCell('B'.$row)->getValue();
		        $blue_consume = $sheet->getCell('C'.$row)->getValue();
		        $purple_consume = $sheet->getCell('D'.$row)->getValue();
		        $orange_consume = $sheet->getCell('E'.$row)->getValue();

		        // 保存 收益-消耗 设置数据
		        $income_and_consume_settings_data[$level] = array(
		        	'income' => $income,
		        	'blue' => $blue_consume,
		        	'purple' => $purple_consume,
		        	'orange' => $orange_consume,
		        );
			}

			foreach ($building_level_list as $building_name => $building_level) {

				// 设置当前建筑的收益数据
				$building_income_list[$building_name] = sctonum($income_and_consume_settings_data[$building_level]['income']);

				// 获取品质数据 蓝紫橙
				$building_quality = $GLOBALS['building_list']['quality'][$building_name];

				$next_level = intval($building_level)+1;

				// 设置下一级消耗数据
				if ($next_level <= 2000) {
					$building_next_level_consume_list[$building_name] = $income_and_consume_settings_data[$next_level][$building_quality];
					$building_next_level_income_list[$building_name]  = $income_and_consume_settings_data[$next_level]['income'];
				}else{
					$building_next_level_consume_list[$building_name] = 0;
				}
			}
		}

		$GLOBALS['income_and_consume_settings_data'] = $income_and_consume_settings_data;

		$post_data = array(
			'calculate_type'		=> $calculate_type,
			'building_income_list' 	=> $building_income_list,
			'building_level_list' 	=> $building_level_list,
			'building_star_list' 	=> $building_star_list,
			'building_mission_list' => $building_mission_list,
			'building_next_level_consume_list' => $building_next_level_consume_list,
			'building_next_level_income_list' => $building_next_level_income_list,
			'policy_data' 			=> $policy_data,
			'policy_center_post'	=> $policy_center_post,
			'policy_center_global'	=> $policy_center_global,
			'photo_data' 			=> $photo_data,
			'mission_data' 			=> $mission_data,
		);

		// response_json_msg(0, $post_data);

		$res = calculation_combination($post_data);

		if (is_array($res)) {
			$_SESSION['IP'] = get_ip();
			$_SESSION['TIME'] = time();
			response_json_msg(0, $res);
		}else{
			response_json_msg(1, '', $res);
		}
		break;
	default:
		# code...
		break;
}

exit;



function calculation_combination($post_data){

	$residence_arr = $GLOBALS['building_list']['residence'];
	$business_arr = $GLOBALS['building_list']['business'];
	$industry_arr = $GLOBALS['building_list']['industry'];
	$special_arr  = $GLOBALS['building_list']['special'];

	$calculate_type 	   = $post_data['calculate_type'];
	$building_income_list  = $post_data['building_income_list'];
	$building_level_list   = $post_data['building_level_list'];
	$building_star_list    = $post_data['building_star_list'];
	$building_mission_list = $post_data['building_mission_list'];
	$policy_data 		   = $post_data['policy_data'];
	$photo_data 		   = $post_data['photo_data'];
	$mission_data 		   = $post_data['mission_data'];
	$building_next_level_consume_list = $post_data['building_next_level_consume_list'];

	// 单个建筑的单体加成
	$building_multiple_data = array();

	// 住宅加成数据
	$policy_multiple_res 	= (100 + $policy_data['policy_data_all'] + $policy_data['policy_data_res'] + $policy_data['policy_data_all_ol'] + $policy_data['policy_data_res_ol'] + $policy_data['policy_data_JGZG'])/100;
	$photo_multiple_res 	= (100 + $photo_data['photo_data_all'] + $photo_data['photo_data_res'] + $photo_data['photo_data_all_ol'] + $photo_data['photo_data_res_ol'])/100;
	$mission_multiple_res 	= (100 + $mission_data['mission_data_all'] + $mission_data['mission_data_res'] + $mission_data['mission_data_all_ol'] + $mission_data['mission_data_res_ol'])/100;

	// 商业加成数据
	$policy_multiple_bus 	= (100 + $policy_data['policy_data_all'] + $policy_data['policy_data_bus'] + $policy_data['policy_data_all_ol'] + $policy_data['policy_data_bus_ol'] + $policy_data['policy_data_JGZG'])/100;
	$photo_multiple_bus 	= (100 + $photo_data['photo_data_all'] + $photo_data['photo_data_bus'] + $photo_data['photo_data_all_ol'] + $photo_data['photo_data_bus_ol'])/100;
	$mission_multiple_bus 	= (100 + $mission_data['mission_data_all'] + $mission_data['mission_data_bus'] + $mission_data['mission_data_all_ol'] + $mission_data['mission_data_bus_ol'])/100;

	// 工业加成数据
	$policy_multiple_ind 	= (100 + $policy_data['policy_data_all'] + $policy_data['policy_data_ind'] + $policy_data['policy_data_all_ol'] + $policy_data['policy_data_ind_ol'] + $policy_data['policy_data_JGZG'])/100;
	$photo_multiple_ind 	= (100 + $photo_data['photo_data_all'] + $photo_data['photo_data_ind'] + $photo_data['photo_data_all_ol'] + $photo_data['photo_data_ind_ol'])/100;
	$mission_multiple_ind 	= (100 + $mission_data['mission_data_all'] + $mission_data['mission_data_ind'] + $mission_data['mission_data_all_ol'] + $mission_data['mission_data_ind_ol'])/100;


	$multiple_detail = array(
		'ind' => $policy_multiple_ind * $photo_multiple_ind,
		'bus' => $policy_multiple_bus * $photo_multiple_bus,
		'res' => $policy_multiple_res * $photo_multiple_res,
	);
	// echo '工业总加成：'.($policy_multiple_ind * $photo_multiple_ind).PHP_EOL;
	// echo '商业总加成：'.($policy_multiple_bus * $photo_multiple_bus).PHP_EOL;
	// echo '住宅总加成：'.($policy_multiple_res * $photo_multiple_res).PHP_EOL;

	// 移除还没有获取到的建筑
	// 计算基础倍率
	// 单建筑总收入 = 无加成收入 * 星级加成       * 建筑加成  * 政策加成              * 照片加成              * 城市任务
	//                           * star_multiple              * policy_multiple_res   * photo_multiple_res    * this_mission
	foreach ($residence_arr as $key => $value) {
		$this_building_star = $building_star_list[$value['name']];
		if ($this_building_star < 1) {
			unset($residence_arr[$key]);
			continue;
		}else{
			$star_multiple 		= $GLOBALS['building_star_to_multiple'][$this_building_star];
			$special_multiple   = isset($special_arr[$value['name']]) ? $special_arr[$value['name']] : 1;
			$this_mission 		= (100 + $building_mission_list[$value['name']] + $mission_multiple_res)/100;
			$this_multiple 		= $star_multiple * $policy_multiple_res * $photo_multiple_res * $special_multiple * $this_mission;

			$building_multiple_data[$value['name']] = $this_multiple;
		}
	}
	foreach ($business_arr as $key => $value) {
		$this_building_star = $building_star_list[$value['name']];
		if ($this_building_star < 1) {
			unset($business_arr[$key]);
			continue;
		}else{
			$this_multiple 		= $GLOBALS['building_star_to_multiple'][$this_building_star];
			$special_multiple   = isset($special_arr[$value['name']]) ? $special_arr[$value['name']] : 1;
			$this_mission 		= (100 + $building_mission_list[$value['name']] + $mission_multiple_bus)/100;
			$this_multiple 		= $this_multiple * $policy_multiple_bus * $photo_multiple_bus * $special_multiple * $this_mission;

			$building_multiple_data[$value['name']] = $this_multiple;
		}
	}
	foreach ($industry_arr as $key => $value) {
		$this_building_star = $building_star_list[$value['name']];
		if ($this_building_star < 1) {
			unset($industry_arr[$key]);
			continue;
		}else{
			$this_multiple 		= $GLOBALS['building_star_to_multiple'][$this_building_star];
			$special_multiple   = isset($special_arr[$value['name']]) ? $special_arr[$value['name']] : 1;
			$this_mission 		= (100 + $building_mission_list[$value['name']] + $mission_multiple_ind)/100;
			$this_multiple 		= $this_multiple * $policy_multiple_ind * $photo_multiple_ind * $special_multiple * $this_mission;

			$building_multiple_data[$value['name']] = $this_multiple;
		}
	}

	// print_r($building_multiple_data);
	// exit;

	if (empty($residence_arr)) {
		return '已获得的住宅建筑物数量为0';
	}elseif (empty($business_arr)) {
		return '已获得的商业建筑物数量为0';
	}elseif (empty($industry_arr)) {
		return '已获得的工业建筑物数量为0';
	}

	$combination_arr_res = array();
	$combination_arr_bus = array();
	$combination_arr_ind = array();

	foreach ($residence_arr as $arr_1_key => $arr_1_value) {
		foreach ($residence_arr as $arr_2_key => $arr_2_value) {
			foreach ($residence_arr as $arr_3_key => $arr_3_value) {
				$value_1 = $arr_1_value['name'];
				$value_2 = $arr_2_value['name'];
				$value_3 = $arr_3_value['name'];

				if ($value_1 == $value_2 || $value_2 == $value_3 || $value_1 == $value_3) {
					continue;
				}else{
					$this_comb = array($value_1, $value_2, $value_3);

					if (empty($combination_arr_res)) {
						$combination_arr_res[] = $this_comb;
					}else{
						$has_same_comb = false;
						foreach ($combination_arr_res as $comb_key => $comb_value) {
							if (in_array($value_1, $comb_value) && in_array($value_2, $comb_value) && in_array($value_3, $comb_value)) {
								$has_same_comb = true;
								break;
							}
						}
						if (!$has_same_comb) {
							$combination_arr_res[] = $this_comb;
						}
					}
				}
			}
		}
	}

	foreach ($business_arr as $arr_1_key => $arr_1_value) {
		foreach ($business_arr as $arr_2_key => $arr_2_value) {
			foreach ($business_arr as $arr_3_key => $arr_3_value) {
				$value_1 = $arr_1_value['name'];
				$value_2 = $arr_2_value['name'];
				$value_3 = $arr_3_value['name'];

				if ($value_1 == $value_2 || $value_2 == $value_3 || $value_1 == $value_3) {
					continue;
				}else{
					$this_comb = array($value_1, $value_2, $value_3);

					if (empty($combination_arr_bus)) {
						$combination_arr_bus[] = $this_comb;
					}else{
						$has_same_comb = false;
						foreach ($combination_arr_bus as $comb_key => $comb_value) {
							if (in_array($value_1, $comb_value) && in_array($value_2, $comb_value) && in_array($value_3, $comb_value)) {
								$has_same_comb = true;
								break;
							}
						}
						if (!$has_same_comb) {
							$combination_arr_bus[] = $this_comb;
						}
					}
				}
			}
		}
	}

	foreach ($industry_arr as $arr_1_key => $arr_1_value) {
		foreach ($industry_arr as $arr_2_key => $arr_2_value) {
			foreach ($industry_arr as $arr_3_key => $arr_3_value) {
				$value_1 = $arr_1_value['name'];
				$value_2 = $arr_2_value['name'];
				$value_3 = $arr_3_value['name'];

				if ($value_1 == $value_2 || $value_2 == $value_3 || $value_1 == $value_3) {
					continue;
				}else{
					$this_comb = array($value_1, $value_2, $value_3);

					if (empty($combination_arr_ind)) {
						$combination_arr_ind[] = $this_comb;
					}else{
						$has_same_comb = false;
						foreach ($combination_arr_ind as $comb_key => $comb_value) {
							if (in_array($value_1, $comb_value) && in_array($value_2, $comb_value) && in_array($value_3, $comb_value)) {
								$has_same_comb = true;
								break;
							}
						}
						if (!$has_same_comb) {
							$combination_arr_ind[] = $this_comb;
						}
					}
				}
			}
		}
	}

	$combination_count = array(
		'res' => count($combination_arr_res),
		'bus' => count($combination_arr_bus),
		'ind' => count($combination_arr_ind),
	);
	// echo count($combination_arr_res) . PHP_EOL;
	// echo count($combination_arr_bus) . PHP_EOL;
	// echo count($combination_arr_ind) . PHP_EOL;

	// print_r($GLOBALS['house_buff']);exit;

	$max_comb_data = array('final_multiple_data'=>0, 'final_income_data'=>0);
	$sec_comb_data = array('final_multiple_data'=>0, 'final_income_data'=>0);
	$max_ind_comb_data = array('final_multiple_data'=>0, 'final_income_data'=>0, 'single_house_max_data'=>0);
	$max_bus_comb_data = array('final_multiple_data'=>0, 'final_income_data'=>0, 'single_house_max_data'=>0);
	$max_res_comb_data = array('final_multiple_data'=>0, 'final_income_data'=>0, 'single_house_max_data'=>0);

	$debug = $GLOBALS['debug'];
	foreach ($combination_arr_res as $res_key => $res_value) {
		if ($debug == 1) {
			if ($res_key > 0) {
				break;
			}
		}
		foreach ($combination_arr_bus as $bus_key => $bus_value) {
			if ($debug == 1) {
				if ($bus_key > 0) {
					break;
				}
			}
			foreach ($combination_arr_ind as $ind_key => $ind_value) {
				if ($debug == 1) {
					if ($ind_key > 0) {
						break;
					}
				}
				$temp_arr = array_merge($res_value, $bus_value);
				$name_combo = array_merge($temp_arr, $ind_value);

				$multiple_arr = array();

				// 初始化一下
				foreach ($name_combo as $name_key => $this_name) {
					$multiple_arr['building_list'][$this_name] = array();
					$multiple_arr['building_list'][$this_name]['multiple'] = 1;
				}

				// 9个建筑开始循环
				foreach ($name_combo as $name_key => $this_name) {

					// 判断当前建筑对哪个建筑有加成，加成值是多少
					foreach ($GLOBALS['buffs_100_to_house'] as $from_building_name => $to_building_name) {
						if ($from_building_name == $this_name) {
							if (in_array($to_building_name, $name_combo)) {
								$multiple_arr['building_list'][$to_building_name]['multiple'] += $building_star_list[$this_name];
							}
						}
					}
					foreach ($GLOBALS['buffs_50_to_house'] as $from_building_name => $to_building_name) {
						if ($from_building_name == $this_name) {
							if (in_array($to_building_name, $name_combo)) {
								$multiple_arr['building_list'][$to_building_name]['multiple'] += $building_star_list[$this_name] * 0.5;
							}
						}
					}

					// 判断当前建筑对哪个行业有加成，加成值是多少
					foreach ($GLOBALS['house_buff']['buffs_bus'] as $buff_building_name => $buff_value) {
						if ($buff_building_name == $this_name) {
							$buff_value = $buff_value[$building_star_list[$buff_building_name]-1];

							foreach ($bus_value as $temp_building_name) {
								$multiple_arr['building_list'][$temp_building_name]['multiple'] += $buff_value;
							}
						}
					}
					foreach ($GLOBALS['house_buff']['buffs_ind'] as $buff_building_name => $buff_value) {
						if ($buff_building_name == $this_name) {
							$buff_value = $buff_value[$building_star_list[$buff_building_name]-1];

							foreach ($ind_value as $temp_building_name) {
								$multiple_arr['building_list'][$temp_building_name]['multiple'] += $buff_value;
							}
						}
					}
					foreach ($GLOBALS['house_buff']['buffs_res'] as $buff_building_name => $buff_value) {
						if ($buff_building_name == $this_name) {
							$buff_value = $buff_value[$building_star_list[$buff_building_name]-1];

							foreach ($res_value as $temp_building_name) {
								$multiple_arr['building_list'][$temp_building_name]['multiple'] += $buff_value;
							}
						}
					}
				}

				$final_multiple_data = 0;
				$final_income_data = 0;
				$this_compare_data = 0;
				$this_comb_single_building_max_data_ind = 0;
				$this_comb_single_building_max_data_bus = 0;
				$this_comb_single_building_max_data_res = 0;

				foreach ($multiple_arr['building_list'] as $this_building_name => $this_building_to_building_multiple_data) {

					// 加成倍率
					$this_building_multiple_data = round($this_building_to_building_multiple_data['multiple'] * $building_multiple_data[$this_building_name], 2);
					$final_multiple_data += $this_building_multiple_data;

					// 每秒收益
					$this_building_final_income_data = round($building_income_list[$this_building_name] * $this_building_to_building_multiple_data['multiple'] * $building_multiple_data[$this_building_name], 2);
					$this_building_final_income_data = sctonum($this_building_final_income_data);
					$final_income_data += $this_building_final_income_data;

					// 保存该建筑的 加成倍率 , 每秒收益 , 消耗收益比
					$multiple_arr['building_list'][$this_building_name]['multiple'] = $this_building_multiple_data;
					$multiple_arr['building_list'][$this_building_name]['income'] = $this_building_final_income_data;

					// 计算的是哪个数据，就比较哪个数据，另外一个作为参考
					if ($calculate_type == 1) {
						$this_compare_data = $this_building_multiple_data;
					}else{
						$this_compare_data = $this_building_final_income_data;
					}

					// 这个组合里数值最大的建筑
					if (in_array($this_building_name, $ind_value)) {
						if ($this_compare_data > $this_comb_single_building_max_data_ind) {
							$this_comb_single_building_max_data_ind = $this_compare_data;
						}
					}
					if (in_array($this_building_name, $bus_value)) {
						if ($this_compare_data > $this_comb_single_building_max_data_bus) {
							$this_comb_single_building_max_data_bus = $this_compare_data;
						}
					}
					if (in_array($this_building_name, $res_value)) {
						if ($this_compare_data > $this_comb_single_building_max_data_res) {
							$this_comb_single_building_max_data_res = $this_compare_data;
						}
					}
				}
				// 该组合 求和后的 收益 和 加成倍率
				$multiple_arr['final_multiple_data'] = $final_multiple_data;
				$multiple_arr['final_income_data'] = $final_income_data;

				if ($calculate_type == 1) {
					// 比较加成倍率
					if ($final_multiple_data > $max_comb_data['final_multiple_data']) {
						$sec_comb_data = $max_comb_data;
						$max_comb_data = $multiple_arr;
					}elseif ($final_multiple_data > $sec_comb_data['final_multiple_data']) {
						$sec_comb_data = $multiple_arr;
					}

					if ($this_comb_single_building_max_data_ind > $max_ind_comb_data['single_house_max_data']) {
						$max_ind_comb_data = $multiple_arr;
						$max_ind_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_ind;
					}elseif ($this_comb_single_building_max_data_ind == $max_ind_comb_data['single_house_max_data']) {
						if ($multiple_arr['final_multiple_data'] > $max_ind_comb_data['final_multiple_data']) {
							$max_ind_comb_data = $multiple_arr;
							$max_ind_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_ind;
						}
					}
					if ($this_comb_single_building_max_data_bus > $max_bus_comb_data['single_house_max_data']) {
						$max_bus_comb_data = $multiple_arr;
						$max_bus_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_bus;
					}elseif ($this_comb_single_building_max_data_bus == $max_bus_comb_data['single_house_max_data']) {
						if ($multiple_arr['final_multiple_data'] > $max_bus_comb_data['final_multiple_data']) {
							$max_bus_comb_data = $multiple_arr;
							$max_bus_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_bus;
						}
					}
					if ($this_comb_single_building_max_data_res > $max_res_comb_data['single_house_max_data']) {
						$max_res_comb_data = $multiple_arr;
						$max_res_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_res;
					}elseif ($this_comb_single_building_max_data_res == $max_res_comb_data['single_house_max_data']) {
						if ($multiple_arr['final_multiple_data'] > $max_res_comb_data['final_multiple_data']) {
							$max_res_comb_data = $multiple_arr;
							$max_res_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_res;
						}
					}
				}else{
					// 比较每秒收益
					if ($final_income_data > $max_comb_data['final_income_data']) {
						$sec_comb_data = $max_comb_data;
						$max_comb_data = $multiple_arr;
					}elseif ($final_income_data > $sec_comb_data['final_income_data']) {
						$sec_comb_data = $multiple_arr;
					}

					if ($this_comb_single_building_max_data_ind > $max_ind_comb_data['single_house_max_data']) {
						$max_ind_comb_data = $multiple_arr;
						$max_ind_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_ind;
					}elseif ($this_comb_single_building_max_data_ind == $max_ind_comb_data['single_house_max_data']) {
						if ($multiple_arr['final_income_data'] > $max_ind_comb_data['final_income_data']) {
							$max_ind_comb_data = $multiple_arr;
							$max_ind_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_ind;
						}
					}
					if ($this_comb_single_building_max_data_bus > $max_bus_comb_data['single_house_max_data']) {
						$max_bus_comb_data = $multiple_arr;
						$max_bus_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_bus;
					}elseif ($this_comb_single_building_max_data_bus == $max_bus_comb_data['single_house_max_data']) {
						if ($multiple_arr['final_income_data'] > $max_bus_comb_data['final_income_data']) {
							$max_bus_comb_data = $multiple_arr;
							$max_bus_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_bus;
						}
					}
					if ($this_comb_single_building_max_data_res > $max_res_comb_data['single_house_max_data']) {
						$max_res_comb_data = $multiple_arr;
						$max_res_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_res;
					}elseif ($this_comb_single_building_max_data_res == $max_res_comb_data['single_house_max_data']) {
						if ($multiple_arr['final_income_data'] > $max_res_comb_data['final_income_data']) {
							$max_res_comb_data = $multiple_arr;
							$max_res_comb_data['single_house_max_data'] = $this_comb_single_building_max_data_res;
						}
					}
				}
			}
		}
	}


	// unset($max_ind_comb_data['single_house_max_data']);
	// unset($max_bus_comb_data['single_house_max_data']);
	// unset($max_res_comb_data['single_house_max_data']);

	$max_comb_data = expect_upgrade_line($max_comb_data, $post_data);
	$sec_comb_data = expect_upgrade_line($sec_comb_data, $post_data);
	$max_ind_comb_data = expect_upgrade_line($max_ind_comb_data, $post_data);
	$max_bus_comb_data = expect_upgrade_line($max_bus_comb_data, $post_data);
	$max_res_comb_data = expect_upgrade_line($max_res_comb_data, $post_data);

	$max_comb_data = calculate_consume_divide_by_income($max_comb_data, $post_data);
	$sec_comb_data = calculate_consume_divide_by_income($sec_comb_data, $post_data);
	$max_ind_comb_data = calculate_consume_divide_by_income($max_ind_comb_data, $post_data);
	$max_bus_comb_data = calculate_consume_divide_by_income($max_bus_comb_data, $post_data);
	$max_res_comb_data = calculate_consume_divide_by_income($max_res_comb_data, $post_data);

	$max_comb_data = combNumberFormat($max_comb_data, $calculate_type);
	$sec_comb_data = combNumberFormat($sec_comb_data, $calculate_type);
	$max_ind_comb_data = combNumberFormat($max_ind_comb_data, $calculate_type);
	$max_bus_comb_data = combNumberFormat($max_bus_comb_data, $calculate_type);
	$max_res_comb_data = combNumberFormat($max_res_comb_data, $calculate_type);

	return array(
		'debug' => $debug,
		'post_data' => $post_data,
		'max_comb_data'=> $max_comb_data,
		'sec_comb_data'=>$sec_comb_data ,
		'multiple_detail'=>$multiple_detail,
		'combination_count'=>$combination_count,
		'max_ind_comb_data'=>$max_ind_comb_data,
		'max_bus_comb_data'=>$max_bus_comb_data,
		'max_res_comb_data'=>$max_res_comb_data
	);
}

function calculate_consume_divide_by_income($combo_arr, $post_data){

	$building_next_level_consume_list = $post_data['building_next_level_consume_list'];
	$building_next_level_income_list  = $post_data['building_next_level_income_list'];

	foreach ($combo_arr['building_list'] as $key => $value) {
		$this_building_name = $key;

		// 下一级的消耗
		$this_building_next_level_consume = sctonum($building_next_level_consume_list[$this_building_name]);

		// 下一级原始收益
		$this_building_next_level_income  = $building_next_level_income_list[$this_building_name];

		// 加成后的收益
		$this_building_next_level_income_real = round($this_building_next_level_income * $value['multiple'], 2);

		// 较上一级增加的收益
		$this_building_next_level_income_add = $this_building_next_level_income_real - $value['income'];

		if ($this_building_next_level_income_add != 0) {
			$this_building_next_level_consume_divide_by_income = sprintf('%.4f',$this_building_next_level_consume / $this_building_next_level_income_add);
		}else{
			$this_building_next_level_consume_divide_by_income = 0;
		}

		$combo_arr['building_list'][$this_building_name]['cDi'] = $this_building_next_level_consume_divide_by_income;

	}

	return $combo_arr;

}

function get_building_next_level_income_and_consume($building_name, $building_level){

	// 收益 和 消耗 设置数据
	$income_and_consume_settings_data = $GLOBALS['income_and_consume_settings_data'];

	// 获取品质数据 蓝紫橙
	$building_quality = $GLOBALS['building_list']['quality'][$building_name];

	$next_level = intval($building_level)+1;

	// 设置下一级消耗数据
	if ($next_level <= 2000) {
		$next_consume = $income_and_consume_settings_data[$next_level][$building_quality];
		$next_income = $income_and_consume_settings_data[$next_level]['income'];
	}else{
		$next_consume = 0;
		$next_income = 0;
	}

	return array(
		'next_consume' => $next_consume,
		'next_income' => $next_income,
	);
}


// 期望升级路线
function expect_upgrade_line($combo_arr, $post_data){

	$building_level_list = $post_data['building_level_list'];

	$combo_building_list = array();

	foreach ($combo_arr['building_list'] as $key => $value) {
		// 建筑名称
		$this_building_name = $key;

		// 当前等级
		$building_level = $building_level_list[$this_building_name];

		$combo_building_list[$this_building_name] = array(
			'ori_building_level' => $building_level, 	// 当前等级
			'building_level' => $building_level, 		// 建筑等级
			'multiple' => $value['multiple'], 			// 加成倍率
			'income' => $value['income'], 				// 该建筑等级收益
		);
	}

	// print_r($combo_building_list);
	// exit;

	$upgrade_line = array();

	for ($i = 1; $i <= 100; $i++) {

		// 本次最低的 消耗 / 收益 比值
		$this_divide_value = -1;
		$this_upgrade_building = '';
		$this_building_level = 0;
		$this_buildnig_income = 0;

		foreach ($combo_building_list as $key => $value) {
			// 建筑名称
			$this_building_name = $key;
			$building_level = $value['building_level'];

			// 下一级的消耗 收益
			$income_and_consume = get_building_next_level_income_and_consume($this_building_name, $building_level);
			$next_income = $income_and_consume['next_income'];
			$next_consume = $income_and_consume['next_consume'];

			if ($next_consume == 0 || $next_income == 0) {
				continue;
			}
			$building_next_level = $building_level+1;

			$next_income_real = round($next_income * $value['multiple'], 2);
			$next_income_add = $next_income_real - $value['income'];
			if ($next_income_add != 0) {
				$consume_divide_by_income = sprintf('%.4f',$next_consume / $next_income_add);
			}else{
				continue;
			}
			$combo_building_list[$key]['cDi'] = $consume_divide_by_income;

			if ($this_divide_value == -1) {
				$this_divide_value = $consume_divide_by_income;
				$this_upgrade_building = $this_building_name;
				$this_buildnig_income = $next_income_real;
				$this_building_level = $building_next_level;
			}else{
				if ($consume_divide_by_income < $this_divide_value && $consume_divide_by_income > 0) {
					$this_divide_value = $consume_divide_by_income;
					$this_upgrade_building = $this_building_name;
					$this_buildnig_income = $next_income_real;
					$this_building_level = $building_next_level;
				}
			}
		}

		$combo_building_list[$this_upgrade_building]['building_level'] = $this_building_level;
		$combo_building_list[$this_upgrade_building]['income'] = $this_buildnig_income;

		$upgrade_line[] = array(
			'building_name' => $this_upgrade_building,
			'divide_value' => $this_divide_value,
			'new_level' => $this_building_level,
		);
	}

	$combo_arr['upgrade_line'] = $upgrade_line;

	return $combo_arr;
}


function combNumberFormat($combo_arr, $calculate_type){
	foreach ($combo_arr['building_list'] as $building_name => $building_value) {
		$combo_arr['building_list'][$building_name]['income'] = number2unit($building_value['income']);
		$combo_arr['building_list'][$building_name]['multiple'] = sprintf('%.2f',$building_value['multiple']);
	}

	$combo_arr['final_income_data'] = number2unit($combo_arr['final_income_data']);
	$combo_arr['final_multiple_data'] = sprintf('%.2f',$combo_arr['final_multiple_data']);

	if ($calculate_type == 1) {
		$combo_arr['single_house_max_data'] = sprintf('%.2f',$combo_arr['single_house_max_data']);
	}else{
		$combo_arr['single_house_max_data'] = number2unit($combo_arr['single_house_max_data']);
	}

	return $combo_arr;
}

function number2unit($number){
	$unit_arr = array('','K','M','B','T','aa','bb','cc','dd','ee','ff','gg','hh','ii','jj','kk','ll','mm','nn','oo','pp','qq','rr','ss','tt','uu','vv','ww','xx','yy','zz');

	$do_statue = true;
	$this_unit_key = 0;
	while ($do_statue) {
		$new_number = $number / 1000;
		if ($new_number > 1) {
			$number = $new_number;
			$this_unit_key++;
		}else{
			$number = round($number, 2) . $unit_arr[$this_unit_key];
			$do_statue = false;
		}
	}
	return $number;
}


function response_json_msg($error, $data, $msg = ''){
	$arr = array(
		'error' => $error,
		'data' => $data,
		'msg' => $msg
	);
	echo json_encode($arr);
	exit;
}

function sctonum($num, $double = 0){
	if(false !== stripos($num, "e")){
		$a = explode("e",strtolower($num));
		return bcmul($a[0], bcpow(10, $a[1], $double), $double);
	}else{
		return $num;
	}
}



function get_ip(){
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $realip = $_SERVER['REMOTE_ADDR'];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $realip = getenv( "HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else {
            $realip = getenv("REMOTE_ADDR");
        }
    }
    return $realip;
}

?>

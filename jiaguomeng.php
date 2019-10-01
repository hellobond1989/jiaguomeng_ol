<?php

ini_set('display_errors','0');

ini_set('memory_limit','1024M');

set_time_limit(0); //调整超时时间为无限制

require_once dirname(__FILE__).'/init.php';


$action = $_GET['action'];

switch ($action) {
	case 'house_data_init':
		response_json_msg(0, $GLOBALS['house_arr']);
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


		$house_level_data = array(
			'木屋' => $_POST['木屋'],
			'居民楼' => $_POST['居民楼'],
			'钢结构房' => $_POST['钢结构房'],
			'平房' => $_POST['平房'],
			'小型公寓' => $_POST['小型公寓'],
			'人才公寓' => $_POST['人才公寓'],
			'中式小楼' => $_POST['中式小楼'],
			'花园洋房' => $_POST['花园洋房'],
			'空中别墅' => $_POST['空中别墅'],
			'复兴公馆' => $_POST['复兴公馆'],
			'便利店' => $_POST['便利店'],
			'菜市场' => $_POST['菜市场'],
			'服装店' => $_POST['服装店'],
			'五金店' => $_POST['五金店'],
			'学校' => $_POST['学校'],
			'图书城' => $_POST['图书城'],
			'加油站' => $_POST['加油站'],
			'商贸中心' => $_POST['商贸中心'],
			'民食斋' => $_POST['民食斋'],
			'媒体之声' => $_POST['媒体之声'],
			'木材厂' => $_POST['木材厂'],
			'食品厂' => $_POST['食品厂'],
			'造纸厂' => $_POST['造纸厂'],
			'水厂' => $_POST['水厂'],
			'电厂' => $_POST['电厂'],
			'纺织厂' => $_POST['纺织厂'],
			'钢铁厂' => $_POST['钢铁厂'],
			'零件厂' => $_POST['零件厂'],
			'企鹅机械' => $_POST['企鹅机械'],
			'人民石油' => $_POST['人民石油'],
		);

		if ($GLOBALS['house_arr']['level_0_setting']) {
			foreach ($house_level_data as $house_key => $house_value) {
				if (in_array($house_key, $GLOBALS['house_arr']['level_0_setting'])) {
					$house_level_data[$house_key] = 0;
				}
			}
		}

		$house_mission_data = $_POST['mission'];

		$policy_data = array(
			'policy_data_all' => $_POST['policy_data_all'],
		    'policy_data_res' => $_POST['policy_data_res'],
		    'policy_data_bus' => $_POST['policy_data_bus'],
		    'policy_data_ind' => $_POST['policy_data_ind'],
		    'policy_data_all_ol' => $_POST['policy_data_all_ol'],
		    'policy_data_res_ol' => $_POST['policy_data_res_ol'],
		    'policy_data_bus_ol' => $_POST['policy_data_bus_ol'],
		    'policy_data_ind_ol' => $_POST['policy_data_ind_ol'],
		);

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

		$post_data = array(
			'house_level_data' 		=> $house_level_data,
			'house_mission_data' 	=> $house_mission_data,
			'policy_data' 			=> $policy_data,
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

	$residence_arr = $GLOBALS['house_arr']['residence'];
	$business_arr = $GLOBALS['house_arr']['business'];
	$industry_arr = $GLOBALS['house_arr']['industry'];
	$special_arr  = $GLOBALS['house_arr']['special'];

	$house_level_data 	= $post_data['house_level_data'];
	$house_mission_data = $post_data['house_mission_data'];
	$policy_data 		= $post_data['policy_data'];
	$photo_data 		= $post_data['photo_data'];
	$mission_data 		= $post_data['mission_data'];

	// print_r($house_mission_data);

	$house_multiple_data = array();
	$policy_multiple_res 	= (100 + $policy_data['policy_data_all'] + $policy_data['policy_data_res'] + $policy_data['policy_data_all_ol'] + $policy_data['policy_data_res_ol'] + $GLOBALS['JIAGUOZHIGUANG'])/100;
	$photo_multiple_res 	= (100 + $photo_data['photo_data_all'] + $photo_data['photo_data_res'] + $photo_data['photo_data_all_ol'] + $photo_data['photo_data_res_ol'])/100;
	$mission_multiple_res 	= (100 + $mission_data['mission_data_all'] + $mission_data['mission_data_res'] + $mission_data['mission_data_all_ol'] + $mission_data['mission_data_res_ol'])/100;

	$policy_multiple_bus 	= (100 + $policy_data['policy_data_all'] + $policy_data['policy_data_bus'] + $policy_data['policy_data_all_ol'] + $policy_data['policy_data_bus_ol'] + $GLOBALS['JIAGUOZHIGUANG'])/100;
	$photo_multiple_bus 	= (100 + $photo_data['photo_data_all'] + $photo_data['photo_data_bus'] + $photo_data['photo_data_all_ol'] + $photo_data['photo_data_bus_ol'])/100;
	$mission_multiple_bus 	= (100 + $mission_data['mission_data_all'] + $mission_data['mission_data_bus'] + $mission_data['mission_data_all_ol'] + $mission_data['mission_data_bus_ol'])/100;

	$policy_multiple_ind 	= (100 + $policy_data['policy_data_all'] + $policy_data['policy_data_ind'] + $policy_data['policy_data_all_ol'] + $policy_data['policy_data_ind_ol'] + $GLOBALS['JIAGUOZHIGUANG'])/100;
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
		$this_level = $house_level_data[$value['name']];
		if ($this_level < 1) {
			unset($residence_arr[$key]);
			continue;
		}else{
			$star_multiple 		= $GLOBALS['house_level_to_multiple'][$this_level];
			$special_multiple   = isset($special_arr[$value['name']]) ? $special_arr[$value['name']] : 1;
			$this_mission 		= (100 + $house_mission_data[$value['name']])/100;
			$this_multiple 		= $star_multiple * $policy_multiple_res * $photo_multiple_res * $mission_multiple_res * $special_multiple * $this_mission;

			$house_multiple_data[$value['name']] = $this_multiple;
		}
	}
	foreach ($business_arr as $key => $value) {
		$this_level = $house_level_data[$value['name']];
		if ($this_level < 1) {
			unset($business_arr[$key]);
			continue;
		}else{
			$this_multiple 		= $GLOBALS['house_level_to_multiple'][$this_level];
			$special_multiple   = isset($special_arr[$value['name']]) ? $special_arr[$value['name']] : 1;
			$this_mission 		= (100 + $house_mission_data[$value['name']])/100;
			$this_multiple 		= $this_multiple * $policy_multiple_bus * $photo_multiple_bus * $mission_multiple_bus * $special_multiple * $this_mission;

			$house_multiple_data[$value['name']] = $this_multiple;
		}
	}
	foreach ($industry_arr as $key => $value) {
		$this_level = $house_level_data[$value['name']];
		if ($this_level < 1) {
			unset($industry_arr[$key]);
			continue;
		}else{
			$this_multiple 		= $GLOBALS['house_level_to_multiple'][$this_level];
			$special_multiple   = isset($special_arr[$value['name']]) ? $special_arr[$value['name']] : 1;
			$this_mission 		= (100 + $house_mission_data[$value['name']])/100;
			$this_multiple 		= $this_multiple * $policy_multiple_ind * $photo_multiple_ind * $mission_multiple_ind * $special_multiple * $this_mission;

			$house_multiple_data[$value['name']] = $this_multiple;
		}
	}

	// print_r($house_multiple_data);
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

	$max_comb_data = array('final_multiple_data'=>0);
	$sec_comb_data = array('final_multiple_data'=>0);
	$max_ind_comb_data = array('final_multiple_data'=>0, 'single_house_max_data'=>0);
	$max_bus_comb_data = array('final_multiple_data'=>0, 'single_house_max_data'=>0);
	$max_res_comb_data = array('final_multiple_data'=>0, 'single_house_max_data'=>0);
	foreach ($combination_arr_res as $res_key => $res_value) {
		foreach ($combination_arr_bus as $bus_key => $bus_value) {
			foreach ($combination_arr_ind as $ind_key => $ind_value) {
				$temp_arr = array_merge($res_value, $bus_value);
				$comb_name = array_merge($temp_arr, $ind_value);

				$total_coin = 0;
				$multiple_arr = array();

				// 初始化一下
				foreach ($comb_name as $name_key => $this_name) {
					$multiple_arr[$this_name] = 1;
				}

				// 9个建筑开始循环
				foreach ($comb_name as $name_key => $this_name) {

					// 判断当前建筑对哪个建筑有加成，加成值是多少
					foreach ($GLOBALS['buffs_100_to_house'] as $from_house_name => $to_house_name) {
						if ($from_house_name == $this_name) {
							if (in_array($to_house_name, $comb_name)) {
								$multiple_arr[$to_house_name] += $house_level_data[$this_name];
							}
						}
					}
					foreach ($GLOBALS['buffs_50_to_house'] as $from_house_name => $to_house_name) {
						if ($from_house_name == $this_name) {
							if (in_array($to_house_name, $comb_name)) {
								$multiple_arr[$to_house_name] += $house_level_data[$this_name] * 0.5;
							}
						}
					}

					// 判断当前建筑对哪个行业有加成，加成值是多少
					foreach ($GLOBALS['house_buff']['buffs_bus'] as $buff_house_name => $buff_value) {
						if ($buff_house_name == $this_name) {
							$buff_value = $buff_value[$house_level_data[$buff_house_name]-1];

							foreach ($bus_value as $house_name) {
								$multiple_arr[$house_name] += $buff_value;
							}
						}
					}
					foreach ($GLOBALS['house_buff']['buffs_ind'] as $buff_house_name => $buff_value) {
						if ($buff_house_name == $this_name) {
							$buff_value = $buff_value[$house_level_data[$buff_house_name]-1];

							foreach ($ind_value as $house_name) {
								$multiple_arr[$house_name] += $buff_value;
							}
						}
					}
					foreach ($GLOBALS['house_buff']['buffs_res'] as $buff_house_name => $buff_value) {
						if ($buff_house_name == $this_name) {
							$buff_value = $buff_value[$house_level_data[$buff_house_name]-1];

							foreach ($res_value as $house_name) {
								$multiple_arr[$house_name] += $buff_value;
							}
						}
					}
				}

				$final_multiple_data = 0;
				$replace_to_max_ind_comb_data = false;
				$replace_to_max_bus_comb_data = false;
				$replace_to_max_res_comb_data = false;
				$this_comb_single_house_max_data_ind = 0;
				$this_comb_single_house_max_data_bus = 0;
				$this_comb_single_house_max_data_res = 0;
				foreach ($multiple_arr as $this_house_name => $this_house_multiple_data) {
					$this_final_multiple_data 		= round($this_house_multiple_data * $house_multiple_data[$this_house_name], 2);
					$this_final_multiple_data		= sprintf("%1\$.2f", $this_final_multiple_data);
					$multiple_arr[$this_house_name] = $this_final_multiple_data;
					$final_multiple_data 			+= $this_final_multiple_data;


					// 这个组合里最大的加成建筑
					if (in_array($this_house_name, $ind_value)) {
						if ($this_final_multiple_data > $this_comb_single_house_max_data_ind) {
							$this_comb_single_house_max_data_ind = $this_final_multiple_data;
						}
					}
					if (in_array($this_house_name, $bus_value)) {
						if ($this_final_multiple_data > $this_comb_single_house_max_data_bus) {
							$this_comb_single_house_max_data_bus = $this_final_multiple_data;
						}
					}
					if (in_array($this_house_name, $res_value)) {
						if ($this_final_multiple_data > $this_comb_single_house_max_data_res) {
							$this_comb_single_house_max_data_res = $this_final_multiple_data;
						}
					}
				}
				$multiple_arr['final_multiple_data'] = round($final_multiple_data,2);

				if ($final_multiple_data > $max_comb_data['final_multiple_data']) {
					$sec_comb_data = $max_comb_data;
					$max_comb_data = $multiple_arr;
				}elseif ($final_multiple_data > $sec_comb_data['final_multiple_data']) {
					$sec_comb_data = $multiple_arr;
				}

				if ($this_comb_single_house_max_data_ind > $max_ind_comb_data['single_house_max_data']) {
					$max_ind_comb_data = $multiple_arr;
					$max_ind_comb_data['single_house_max_data'] = $this_comb_single_house_max_data_ind;
				}elseif ($this_comb_single_house_max_data_ind == $max_ind_comb_data['single_house_max_data']) {
					if ($multiple_arr['final_multiple_data'] > $max_ind_comb_data['final_multiple_data']) {
						$max_ind_comb_data = $multiple_arr;
						$max_ind_comb_data['single_house_max_data'] = $this_comb_single_house_max_data_ind;
					}
				}
				if ($this_comb_single_house_max_data_bus > $max_bus_comb_data['single_house_max_data']) {
					$max_bus_comb_data = $multiple_arr;
					$max_bus_comb_data['single_house_max_data'] = $this_comb_single_house_max_data_bus;
				}elseif ($this_comb_single_house_max_data_bus == $max_bus_comb_data['single_house_max_data']) {
					if ($multiple_arr['final_multiple_data'] > $max_bus_comb_data['final_multiple_data']) {
						$max_bus_comb_data = $multiple_arr;
						$max_bus_comb_data['single_house_max_data'] = $this_comb_single_house_max_data_bus;
					}
				}
				if ($this_comb_single_house_max_data_res > $max_res_comb_data['single_house_max_data']) {
					$max_res_comb_data = $multiple_arr;
					$max_res_comb_data['single_house_max_data'] = $this_comb_single_house_max_data_res;
				}elseif ($this_comb_single_house_max_data_res == $max_res_comb_data['single_house_max_data']) {
					if ($multiple_arr['final_multiple_data'] > $max_res_comb_data['final_multiple_data']) {
						$max_res_comb_data = $multiple_arr;
						$max_res_comb_data['single_house_max_data'] = $this_comb_single_house_max_data_res;
					}
				}
			}
		}
	}


	// unset($max_ind_comb_data['single_house_max_data']);
	// unset($max_bus_comb_data['single_house_max_data']);
	// unset($max_res_comb_data['single_house_max_data']);

	return array(
		'max_comb_data'=> $max_comb_data,
		'sec_comb_data'=>$sec_comb_data ,
		'multiple_detail'=>$multiple_detail,
		'combination_count'=>$combination_count,
		'max_ind_comb_data'=>$max_ind_comb_data,
		'max_bus_comb_data'=>$max_bus_comb_data,
		'max_res_comb_data'=>$max_res_comb_data
	);
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

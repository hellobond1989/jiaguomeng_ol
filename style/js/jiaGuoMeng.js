
// 激活的存档
var default_archive_name = '_default_archive';
var default_archive_list = '_active_list';
var need_click_all_input = false;

// 页面加载完毕后，初始化各种数据
$(document).ready(function(){

	// 存档初始化
	init_default_archive();

	// 建筑列表初始化
	init_building_list();

});

var jump_house = new Array();

function init_default_archive(){
	archive_json = localStorage.getItem(default_archive_name);
	if (!archive_json) {
		archive_json = '{"building_star_list":{},"building_level_list":{},"building_mission_list":{},"multiple_policy_center":{},"multiple_policy_list":{},"multiple_photo_list":{},"multiple_mission_list":{},"comb_result":{}}';
		localStorage.setItem(default_archive_name, archive_json);
		need_click_all_input = true;
	}
}

function init_building_list(){

	var time = new Date().getTime();
	$.ajax({
		type: "GET",
		url: "./jiaguomeng.php?action=init_building_list&t="+time,
		dataType: "json",
		success: function(json){

			$.each(json.data.level_0_setting, function(index, value){
				// jump_house.push(value);
			});

			var output = '';
			output += '<tr><td colspan="4" style="text-align: center;">住宅</td><tr>';
			output += '<tr><td style="text-align: center;">名称</td><td style="text-align: center;">等级</td><td style="text-align: center;">星级</td><td style="text-align: center;">城市任务加成</td><tr>';
			$.each(json.data.residence, function(index, value){
				output += '<tr class="'+value.quality+'"><td>'+value.name+'</td><td>'+get_building_level(value.name)+'</td><td>'+get_building_star(value.name)+'</td><td class="city_mission_td">'+get_building_mission(value.name)+'</td><tr>';
			});
			$('#house_data_table_residence').html(output);

			var output = '';
			output += '<tr><td colspan="4" style="text-align: center;">商业</td><tr>';
			output += '<tr><td style="text-align: center;">名称</td><td style="text-align: center;">等级</td><td style="text-align: center;">星级</td><td style="text-align: center;">城市任务加成</td><tr>';
			$.each(json.data.business, function(index, value){
				output += '<tr class="'+value.quality+'"><td>'+value.name+'</td><td>'+get_building_level(value.name)+'</td><td>'+get_building_star(value.name)+'</td><td class="city_mission_td">'+get_building_mission(value.name)+'</td><tr>';
			});
			$('#house_data_table_business').html(output);

			var output = '';
			output += '<tr><td colspan="4" style="text-align: center;">工业</td><tr>';
			output += '<tr><td style="text-align: center;">名称</td><td style="text-align: center;">等级</td><td style="text-align: center;">星级</td><td style="text-align: center;">城市任务加成</td><tr>';
			$.each(json.data.industry, function(index, value){
				output += '<tr class="'+value.quality+'"><td>'+value.name+'</td><td>'+get_building_level(value.name)+'</td><td>'+get_building_star(value.name)+'</td><td class="city_mission_td">'+get_building_mission(value.name)+'</td><tr>';
			});
			$('#house_data_table_industry').html(output);


			// 建筑信息修改事件绑定
			building_info_bind_event();

			// 政策&相片&城市任务修改事件绑定
			policy_photo_mission_bind_event();

			if (need_click_all_input) {
				click_all_input();
			}

			// 加载存档数据
			show_archive_data();

			// 显示存档列表
			show_archive_list('show');
		},
		error : function (){
			alert('数据初始失败');
		}
	});
}

// 建筑等级元素
function get_building_level(name){
	var output = '<input type="number" class="building_level" building-name="'+name+'" name="building_level['+name+']" value="0" >';
	return output;
}
// 建筑星级元素
function get_building_star(name){
	var output =
		'<label class="active"><input type="radio" class="building_star" building-name="'+name+'" name="building_star['+name+']" value="0" checked="checked"><span>0</span></label>'+
		'<label ><input type="radio" class="building_star" building-name="'+name+'" name="building_star['+name+']" value="1"><span>1</span></label>'+
		'<label ><input type="radio" class="building_star" building-name="'+name+'" name="building_star['+name+']" value="2"><span>2</span></label>'+
		'<label ><input type="radio" class="building_star" building-name="'+name+'" name="building_star['+name+']" value="3"><span>3</span></label>'+
		'<label ><input type="radio" class="building_star" building-name="'+name+'" name="building_star['+name+']" value="4"><span>4</span></label>'+
		'<label ><input type="radio" class="building_star" building-name="'+name+'" name="building_star['+name+']" value="5"><span>5</span></label>';
	return output;
}
// 建筑城市任务元素
function get_building_mission(name){
	var output = '<input type="text" class="building_mission" building-name="'+name+'" name="building_mission['+name+']">%';
	return output;
}


// 读取存档内容
function get_archive_data(){
	var archive_json = localStorage.getItem(default_archive_name);
	var archive_data = JSON.parse(archive_json);
	return archive_data;
}
// 保存存档内容
function set_archive_data(archive_data){
	var archive_json = JSON.stringify(archive_data);
	localStorage.setItem(default_archive_name, archive_json);
}

// 读取存档列表
function get_archive_list(){
	var archive_list_json = localStorage.getItem(default_archive_list);
	if (!archive_list_json) {
		archive_list_json = '[]';
		localStorage.setItem(default_archive_list, archive_list_json);
	}
	var archive_list_data = JSON.parse(archive_list_json);
	return archive_list_data;
}
// 保存存档列表
function set_archive_list(archive_list_data){
	var archive_list_json = JSON.stringify(archive_list_data);
	localStorage.setItem(default_archive_list, archive_list_json);
}

// 自动保存事件
function building_info_bind_event(){
	$('.building_level').change(function(){
		var input_name = $(this).attr('name');
		var building_name = $(this).attr('building-name');
		var building_level = $(this).val();

		if (jump_house.indexOf(building_name) > -1) {
			$(this).val(0);
		}else{
			var archive_data = get_archive_data();

			archive_data['building_level_list'][building_name] = building_level;

			set_archive_data(archive_data);
		}
	});
	$('.building_star').click(function(){
		var input_name = $(this).attr('name');
		var building_name = $(this).attr('building-name');
		var building_star = $(this).val();

		if (jump_house.indexOf(building_name) > -1) {
			$("input[name='"+input_name+"']").get(0).checked=true;
		}else{
			var archive_data = get_archive_data();

			archive_data['building_star_list'][building_name] = building_star;

			set_archive_data(archive_data);

			$(this).parent().parent().children().removeClass('active');
			$(this).parent().addClass('active');
		}
	});
	$('.building_mission').change(function(){
		var input_name = $(this).attr('name');
		var building_name = $(this).attr('building-name');
		var building_mission = $(this).val();

		if (jump_house.indexOf(building_name) > -1) {
			$(this).val(0);
		}else{
			var archive_data = get_archive_data();

			archive_data['building_mission_list'][building_name] = building_mission;

			set_archive_data(archive_data);
		}
	});
}
function policy_photo_mission_bind_event(){
	// 修改后保存
	$('.addition_data_table input').change(function(){
		var multiple_name = $(this).attr('data-name');
		var addition_name = $(this).attr('name');
		var addition_value = $(this).val();

		var archive_data = get_archive_data();
		// 不是政策中心选择
		if (multiple_name != 'multiple_policy_center') {
			archive_data[multiple_name][addition_name] = addition_value;
			set_archive_data(archive_data);
		}else{
			var isChecked = $(this).prop('checked');
			var data_value = parseInt($(this).attr('data-value'));
			var prev_value = data_value - 1;
			var next_value = data_value + 1;

			if (isChecked) {
				archive_data[multiple_name][addition_name] = addition_value;
				set_archive_data(archive_data);

				if (prev_value >= 1) {
					$('input[data-name="multiple_policy_center"][data-value="'+prev_value+'"]').prop("checked",true);
					$('input[data-name="multiple_policy_center"][data-value="'+prev_value+'"]').change();
				}
			}else{
				archive_data[multiple_name][addition_name] = 0;
				set_archive_data(archive_data);

				if (next_value <= 10) {
					$('input[data-name="multiple_policy_center"][data-value="'+next_value+'"]').prop("checked",false);
					$('input[data-name="multiple_policy_center"][data-value="'+next_value+'"]').change();
				}
			}
		}
	});
}

function click_all_input(){
	$('input.building_level').change();
	$('input.building_star[value="0"]').click();
	$('input.building_mission').change();
	$('input[data-name="multiple_policy_list"]').change();
	$('input[data-name="multiple_photo_list"]').change();
	$('input[data-name="multiple_mission_list"]').change();
	$('input[data-name="multiple_policy_center"]').change();
}

// 显示存档数据
function show_archive_data(){
	var archive_data = get_archive_data();

	// 建筑等级数据读取并显示
	$.each(archive_data['building_level_list'], function(index, value){
		$('input[name="building_level['+index+']"]').val(value);
	});

	// 建筑星级数据读取并显示
	$.each(archive_data['building_star_list'], function(index, value){
		$('input[name="building_star['+index+']"][value="'+value+'"]').click();
	});

	// 建筑城市任务数据读取并显示
	$.each(archive_data['building_mission_list'], function(index, value){
		$('input[name="building_mission['+index+']"]').val(value);
	});


	// 政策数据读取并显示
	$.each(archive_data['multiple_policy_list'], function(index, value){
		$('input[name="'+index+'"]').val(value);
	});
	// 相册数据读取并显示
	$.each(archive_data['multiple_photo_list'], function(index, value){
		$('input[name="'+index+'"]').val(value);
	});
	// 城市任务数据读取并显示
	$.each(archive_data['multiple_mission_list'], function(index, value){
		$('input[name="'+index+'"]').val(value);
	});


	// 政策中心选择
	$.each(archive_data['multiple_policy_center'], function(index, value){
		if (parseInt(value) == 1) {
			$('input[name="'+index+'"]').prop("checked",true);
		}else{
			$('input[name="'+index+'"]').prop("checked",false);
		}
	});

	comb_result_show();
}

// 显示存档列表
function show_archive_list(display){
	$('#calculate_archive tbody td').html("&nbsp;&nbsp;");

	var archive_list_data = get_archive_list();
	$.each(archive_list_data, function(index, value){
		var output =
			'<td>'+value+'</td>'+
			'<td><a href="javascript:;" onclick="active_archive(\''+value+'\')">读取</a>'+
			'<span style="display: inline-block; width:10px; text-align:center;">|</span>'+
			'<a href="javascript:;" onclick="delete_archive(\''+value+'\')">删除</a></td>';
		$('#calculate_archive tbody tr').eq(index).html(output);
	})
	if (display == 'show') {
		$('#calculate_archive').show();
	}
}

// 保存存档操作
function save_calculate_result(){
	var archive_name = $('#archive_name').val();
	if (archive_name == '') {
		alert('需要输入保存的名称');
		return;
	}

	var archive_list_data = get_archive_list();

	if (archive_list_data.length == 0) {
		archive_list_data.push(archive_name);
	}else if (archive_list_data.length >= 9) {
		alert('存档已满，删除一个吧');
		return;
	}else{
		var has_same_name_archive = false;
		$.each(archive_list_data, function(index, value){
			if (value == archive_name) {
				has_same_name_archive = true;
			}
		})
		if (!has_same_name_archive) {
			archive_list_data.push(archive_name);
		}
	}

	// 保存数据
	add_archive_data(archive_name);

	// 加入到存档列表
	set_archive_list(archive_list_data);

	// 更新存档列表
	show_archive_list();
}

// 保存数据
function add_archive_data(archive_name){
	var archive_data = get_archive_data();
	var archive_json = JSON.stringify(archive_data);
	localStorage.setItem(archive_name, archive_json);
}

// 删除存档
function delete_archive(archive_name){
	var archive_list_data = get_archive_list();
	var archive_list_data_new = new Array();

	$.each(archive_list_data, function(index, value){
		if (value != archive_name) {
			archive_list_data_new.push(value);
		}
	})


	// 删除存档
	localStorage.removeItem(archive_name);

	// 更新存档列表
	set_archive_list(archive_list_data_new);

	// 显示存档列表
	show_archive_list();
}

// 加载旧存档
function active_archive(archive_name){
	if (ajax_check == true) {
		alert('计算中，请稍候');
		return;
	}
	var old_archive_json = localStorage.getItem(archive_name);
	var archive_data = JSON.parse(old_archive_json);
	set_archive_data(archive_data);
	show_archive_data();
}


function set_calculate_type(obj){
	var type = $(obj).val();
	$('input[name="calculate_type"]').val(type);
}










var ajax_check = false;
function submit_form(obj){
	if (ajax_check) {
		return false;
	}
	$(obj).html('计算中，请稍候...');

	$('#max_comb_table thead span.final_income').html('&nbsp;');
	$('#sec_comb_table thead span.final_income').html('&nbsp;');
	$('#max_ind_comb_table thead span.final_income').html('&nbsp;');
	$('#max_bus_comb_table thead span.final_income').html('&nbsp;');
	$('#max_res_comb_table thead span.final_income').html('&nbsp;');

	$('#max_comb_table thead span.final_multiple').html('&nbsp;');
	$('#sec_comb_table thead span.final_multiple').html('&nbsp;');
	$('#max_ind_comb_table thead span.final_multiple').html('&nbsp;');
	$('#max_bus_comb_table thead span.final_multiple').html('&nbsp;');
	$('#max_res_comb_table thead span.final_multiple').html('&nbsp;');

	$('#max_comb_table tbody td').html('&nbsp;');
	$('#sec_comb_table tbody td').html('&nbsp;');
	$('#max_ind_comb_table tbody td').html('&nbsp;');
	$('#max_bus_comb_table tbody td').html('&nbsp;');
	$('#max_res_comb_table tbody td').html('&nbsp;');

	ajax_check = true;
	var time = new Date().getTime();

	$.ajax({
		type: "POST",
		url: "./jiaguomeng.php?action=get_combination_data&t="+time,
		data: $('#data_form').serialize(),
		dataType: "json",
		success: function(json){
			ajax_check = false;
			$(obj).html('计算最优组合');

			if (json.error == 1) {
				alert(json.msg);
			}else{

				archive_data = get_archive_data();
				archive_data['comb_result'] = json.data;
				set_archive_data(archive_data);
				comb_result_show();
			}
		},
		error : function (){
			alert('计算超时了/(ㄒoㄒ)/~~');
			ajax_check = false;
			$(obj).html('计算最优组合');
		}
	});
}

function comb_result_td_output(name, income, multiple, consumeCompareIncome, color){
	if (color == 'red') {
		return '<tr style="color:red;"><td>'+name+'</td><td align="right">'+income+'</td><td>'+multiple+'</td><td>'+consumeCompareIncome+'</td></tr>'
	}else{
		return '<tr><td>'+name+'</td><td align="right">'+income+'</td><td>'+multiple+'</td><td>'+consumeCompareIncome+'</td></tr>'
	}
}

function comb_result_show(){

	var archive_data = get_archive_data();
	comb_result = archive_data['comb_result'];

	if (comb_result.hasOwnProperty('max_comb_data')) {
		var output = '';
		$.each(comb_result.max_comb_data.building_list, function(index, value){
			output += comb_result_td_output(index, value.income, value.multiple, value.cDi, '');
		});
		$('#max_comb_table tbody').html(output)

		$('#max_comb_table thead .final_multiple').html(comb_result.max_comb_data.final_multiple_data);
		$('#max_comb_table thead .final_income').html(comb_result.max_comb_data.final_income_data);
	}

	if (comb_result.hasOwnProperty('sec_comb_data')) {
		var output = '';

		$.each(comb_result.sec_comb_data.building_list, function(index, value){
			output += comb_result_td_output(index, value.income, value.multiple, value.cDi, '');
		});
		$('#sec_comb_table tbody').html(output)

		$('#sec_comb_table thead .final_multiple').html(comb_result.sec_comb_data.final_multiple_data);
		$('#sec_comb_table thead .final_income').html(comb_result.sec_comb_data.final_income_data);
	}

	if (comb_result.hasOwnProperty('max_ind_comb_data')) {
		var output = '';

		$.each(comb_result.max_ind_comb_data.building_list, function(index, value){
			var single_house_max_data = comb_result.max_ind_comb_data['single_house_max_data'];
			if (value.multiple == single_house_max_data || value.income == single_house_max_data) {
				output += comb_result_td_output(index, value.income, value.multiple, value.cDi, 'red');
			}else{
				output += comb_result_td_output(index, value.income, value.multiple, value.cDi, '');
			}
		});
		$('#max_ind_comb_table tbody').html(output)

		$('#max_ind_comb_table thead .final_multiple').html(comb_result.max_ind_comb_data.final_multiple_data);
		$('#max_ind_comb_table thead .final_income').html(comb_result.max_ind_comb_data.final_income_data);
	}

	if (comb_result.hasOwnProperty('max_bus_comb_data')) {
		var output = '';

		$.each(comb_result.max_bus_comb_data.building_list, function(index, value){
			var single_house_max_data = comb_result.max_bus_comb_data['single_house_max_data'];
			if (value.multiple == single_house_max_data || value.income == single_house_max_data) {
				output += comb_result_td_output(index, value.income, value.multiple, value.cDi, 'red');
			}else{
				output += comb_result_td_output(index, value.income, value.multiple, value.cDi, '');
			}
		});
		$('#max_bus_comb_table tbody').html(output)

		$('#max_bus_comb_table thead .final_multiple').html(comb_result.max_bus_comb_data.final_multiple_data);
		$('#max_bus_comb_table thead .final_income').html(comb_result.max_bus_comb_data.final_income_data);
	}

	if (comb_result.hasOwnProperty('max_res_comb_data')) {
		var output = '';

		$.each(comb_result.max_res_comb_data.building_list, function(index, value){
			var single_house_max_data = comb_result.max_res_comb_data['single_house_max_data'];
			if (value.multiple == single_house_max_data || value.income == single_house_max_data) {
				output += comb_result_td_output(index, value.income, value.multiple, value.cDi, 'red');
			}else{
				output += comb_result_td_output(index, value.income, value.multiple, value.cDi, '');
			}
		});
		$('#max_res_comb_table tbody').html(output)

		$('#max_res_comb_table thead .final_multiple').html(comb_result.max_res_comb_data.final_multiple_data);
		$('#max_res_comb_table thead .final_income').html(comb_result.max_res_comb_data.final_income_data);
	}
}


function pop_upgrade_line(type){
	var archive_data = get_archive_data();
	comb_result = archive_data['comb_result'];

	if (!comb_result.hasOwnProperty(type)){
		alert('没有该升级路线数据');
		return;
	}
	var data;
	var output = '';
	$.each(comb_result, function(index, value){
		if (index == type) {
			data = value;
		}
	});

	$.each(data.upgrade_line, function(index, value){
		output += '<tr><td>'+(parseInt(index)+1)+'</td><td>'+value.building_name+'</td><td>'+value.new_level+'</td><td>'+value.divide_value+'</td></tr>';
	});

	$('#pop_window .pop_window_info tbody').html(output)

	WinPop.Open('pop_window');
}


// WinPop.Close('order_tips')
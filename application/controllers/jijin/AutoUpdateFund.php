<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
class AutoUpdateFund extends MY_Controller
{
    private $logfile_suffix;
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array("comfunction"));
        $this->logfile_suffix = '('.date('Y-m',time()).').txt';
    }
    
	function index($type = 0)
	{
		set_time_limit(1800);
		$fundCodes = $this->db->select('fundcode,fundtype')->get('jz_fundlist')->result_array();
		$fundDb = $this->load->database('fundInfo',true);
		$HSCodes = $fundDb->select('InnerCode,SecuCode')->where_in('SecuCode',array_column($fundCodes,'fundcode'))->where(array('SecuCategory'=>8))->get('SecuMain')->result_array();
		$InnerCodes = array_column($HSCodes,'InnerCode');
		$FundNature = $fundDb->select('InnerCode,FundNature')->where_in('InnerCode',$InnerCodes)->get('MF_FundArchives')->result_array();
		$HSCodes = setkey($HSCodes,'SecuCode');
		$unEXistFund = '';
		foreach ($fundCodes as $val){
			if (isset($HSCodes[$val['fundcode']])){
				$HSCodes[$val['fundcode']]['fundtype'] = $val['fundtype'];
			}else{
				$unEXistFund .= $val['fundcode'].', ';
			}
		}
		$HSCodes = setkey($HSCodes,'InnerCode');
		foreach ($FundNature as $val){						//获取基金性质 1-常规基金；2-QDII基金；3-互认基金
			if (isset($HSCodes[$val['InnerCode']])){
				$HSCodes[$val['InnerCode']]['FundNature'] = $val['FundNature'];
			}
		}
		if (!empty($unEXistFund)){
			file_put_contents('log/AutoUpdateFund'.$this->logfile_suffix,date('Y-m-d H:i:s',time())."自动更新基金数据时，从恒生聚源的数据库找不到以下代码的基金:".$unEXistFund."\r\n\r\n",FILE_APPEND);
		}

//更新基金净值表fund_netvalue_*
		foreach ($HSCodes as $key => $val){
			$systableName = 'fund_netvalue_'.$val['SecuCode'];
			if (!$this->db->table_exists($systableName)){
				$this->creatFundNetValue($systableName);
			}
			$maxXGRQ = $this->db->select_max('XGRQ')->get($systableName)->row_array()['XGRQ'];
			$maxXGRQ = (empty($maxXGRQ) || $type == 1) ? '1900-01-01 00:00:00' : $maxXGRQ;
			$netValue = $fundDb->select('NV,EndDate net_date,UnitNV net_unit,AccumulatedUnitNV net_sum,NVDailyGrowthRate net_day_growth,XGRQ')->where(array('InnerCode'=>$val['InnerCode'],'XGRQ>'=>$maxXGRQ))->get('MF_NetValue')->result_array();
			if (!empty($netValue)){
				foreach ($netValue as $k => $v){
					if (!empty($v['NV'])){
						$HSCodes[$key]['total_assets'] = $v['NV'];
						$HSCodes[$key]['total_scale'] = $v['NV']/$v['net_unit'];
						$HSCodes[$key]['end_date'] = $v['XGRQ'];
					}
					unset($netValue[$k]['NV']);
					$netValue[$k]['net_date'] = substr($v['net_date'],0,10);
				}
				$allKeys = array_column($netValue,'net_date');
				$this->db->trans_start();
				$this->db->where_in('net_date',$allKeys)->delete($systableName);
				$this->db->insert_batch($systableName, $netValue);
				$this->db->trans_complete();
			}
		} 
		unset($netValue);

//更新基金净值涨幅表fund_netvalue_growth
		foreach ($HSCodes as $key => $val){
			if ($val['fundtype'] == 2){
				$coinFund[] = $val['InnerCode'];
			}else{
				$otherFund[] = $val['InnerCode'];
			}
		}
		$netvalueGrowth = array();
		if (!empty($coinFund)){
			$netvalueGrowth = $fundDb->select('InnerCode fund_code,AnnualizedRRInSingleWeek growth_day,RRSinceThisYear growth_year,RRInSingleWeek growth_week,RRInSingleMonth growth_onemonth,RRInThreeMonth growth_threemonth,RRInSixMonth growth_sixmonth,RRSinceStart growth_setup,TradingDay XGRQ')->where_in('InnerCode',$coinFund)->get('MF_MMYieldPerformance')->result_array();
			foreach ($netvalueGrowth as $key =>$val){
				if (isset($coinNetvalueGrowth[$val['fund_code']])){
					if (strtotime($val['XGRQ']) > strtotime($coinNetvalueGrowth[$val['fund_code']]['XGRQ'])){
						$coinNetvalueGrowth[$val['fund_code']] = $val;
					}
				}else{
					$coinNetvalueGrowth[$val['fund_code']] = $val;
				}
			}
		}
		if (!empty($otherFund)){
			$netvalueGrowth = array_merge($coinNetvalueGrowth,$fundDb->select('InnerCode fund_code,NVDailyGrowthRate growth_day,RRSinceThisYear growth_year,RRInSingleWeek growth_week,RRInSingleMonth growth_onemonth,RRInThreeMonth growth_threemonth,RRInSixMonth growth_sixmonth,RRSinceStart growth_setup,UpdateTime XGRQ')->where_in('InnerCode',$otherFund)->get('MF_NetValuePerformance')->result_array());
		}
		foreach ($netvalueGrowth as $key=>$val){
			$netvalueGrowth[$key]['fund_code'] = $HSCodes[$val['fund_code']]['SecuCode'];
			$HSCodes[$val['fund_code']]['growth_week'] = $val['growth_week'];
		}
		$allKeys = array_column($netvalueGrowth,'fund_code');
		$this->db->trans_start();
		$this->db->where_in('fund_code',$allKeys)->delete('fund_netvalue_growth');
		$this->db->insert_batch('fund_netvalue_growth', $netvalueGrowth);
		$this->db->trans_complete(); 
		unset($netvalueGrowth);
		
//加上基金全称，基金简称，基金公司，托管机构，成立时间
		foreach ($HSCodes as $key => $val){
			$names = $fundDb->select('ChiName,ChiNameAbbr')->where(array('SecuCode'=>$val['SecuCode'],'SecuCategory'=>8))->get('secumain')->result_array();
			if (!empty($names)){
				foreach ($names as $k => $v){
					if (!empty($v['ChiName'])){
						$HSCodes[$key]['full_name'] = iconv("gbk","UTF-8",$v['ChiName']);
						$HSCodes[$key]['short_name'] = iconv("gbk","UTF-8",$v['ChiNameAbbr']);
					}
					
				} 
			}
			
			$otherinfo=$fundDb->query('SELECT a.EstablishmentDate,a.ListedDate,b.TrusteeName,c.InvestAdvisorName FROM mf_fundarchives a LEFT JOIN mf_trusteeoutline b ON a.TrusteeCode=b.TrusteeCode LEFT JOIN mf_investadvisoroutline c ON a.InvestAdvisorCode=c.InvestAdvisorCode WHERE a.InnerCode='.$val['InnerCode'])->result_array();
			if (!empty($otherinfo)){
				foreach ($otherinfo as $k => $v){
					if (!empty($v['EstablishmentDate'])){
						$HSCodes[$key]['build_date'] = date("Y-m-d",strtotime($v['EstablishmentDate']));
// 						$HSCodes[$key]['listed_date'] = $v['ListedDate'];
						$HSCodes[$key]['trustee'] = iconv("gbk","UTF-8",$v['TrusteeName']);//托管人
						$HSCodes[$key]['investadvisor'] = iconv("gbk","UTF-8",$v['InvestAdvisorName']);//管理人
					}
				}
			}		
		}
		unset($names);
		
//更新基金基本信息表fund_info
		foreach ($HSCodes as $key => $val){
			if (isset($val['growth_week'])){
				$fundClass[$val['fundtype']][] = array('InnerCode'=>$key,'growth_week'=>$val['growth_week']);
			}
		}
		foreach ($fundClass as $val){
			usort($val, function($a, $b) {
				$al = $a['growth_week'];
				$bl = $b['growth_week'];
				if ($al == $bl){
					return 0;
				}
				return ($al > $bl) ? -1 : 1;
			});
			foreach ($val as $k => $v){
				$HSCodes[$v['InnerCode']]['fund_ranking'] = $k+1;
				$HSCodes[$v['InnerCode']]['fund_total'] = count($val);
			}
		}
		$existCode = $this->db->select('fund_code')->get('fund_info')->result_array();
		$existCode = array_column($existCode,'fund_code');
// var_dump($HSCodes);
		foreach ($HSCodes as $val){
			$val['fund_code'] = $val['SecuCode'];
			unset($val['SecuCode'],$val['InnerCode'],$val['fundtype'],$val['growth_week'],$val['FundNature']);
			if (isset($val['fund_ranking'])){
				$newdata = array('fund_ranking'=>$val['fund_ranking']);
				if (in_array($val['fund_code'],$existCode)){
					$this->db->set($val)->where(array('fund_code'=>$val['fund_code']))->update('fund_info');
				}else{
					$this->db->set($val)->insert('fund_info');
				}
			}

		}

//基金经理表Fund_manager
		$Fund_manager = $fundDb->select('Name manger_name,InnerCode fund_code,PersonalCode,PostName,UpdateTime XGRQ')->where_in('InnerCode',$InnerCodes)->where(array('Incumbent'=>1))->get('MF_FundManagerNew')->result_array();
		foreach ($Fund_manager as $key=>$val){
			$Fund_manager[$key]['manger_name'] = iconv("gbk","UTF-8",$val['manger_name']);
			$Fund_manager[$key]['fund_code'] = $HSCodes[$val['fund_code']]['SecuCode'];
		}
 		$PersonalCode = array_column($Fund_manager,'PersonalCode');
//  		$Fund_manager = setkey($Fund_manager,'fund_code');
		$Background = $fundDb->select('PersonalCode,Background')->where_in('PersonalCode',$PersonalCode)->get('MF_PersonalInfo')->result_array();
		foreach ($Background as $key=>$val){
			foreach ($Fund_manager as $k=>$v){
				if ($v['PersonalCode'] == $val['PersonalCode']){
					$insertData[$v['fund_code']] = $v;
					$insertData[$v['fund_code']]['manger_resume'] = iconv("gbk","UTF-8",$val['Background']);
					unset($insertData[$v['fund_code']]['PersonalCode']);
				}
			}
		}
		$allKeys = array_column($insertData,'fund_code');
		$this->db->trans_start();
		$this->db->where_in('fund_code',$allKeys)->delete('fund_manager');
		$this->db->insert_batch('fund_manager', $insertData);
		$this->db->trans_complete(); 
		unset($insertData,$Fund_manager);

//更新基金资产配置明细表Fund_distribution
		$Fund_distribution = $QDIIAssetAllocation = array();
		$relateCode = $fundDb->select('InnerCode,RelatedInnerCode')->where_in('RelatedInnerCode',$InnerCodes)->get('MF_CodeRelationshipNew')->result_array();
		foreach ($relateCode as $val){
			$HSCodes[$val['RelatedInnerCode']]['InnerCode'] = $val['InnerCode'];
		}
		foreach ($HSCodes as $val){
			if (isset($val['FundNature']) && $val['FundNature'] == 2){
				$transQDIIInnerCode[] = $val['InnerCode'];
			}else{
				$transInnerCode[] = $val['InnerCode'];
			}
			$transHSCodes[$val['InnerCode']] = $val;
		}
		if (!empty($transInnerCode)){
			$select = 'InnerCode fund_code,AssetTypeCode,RatioInTotalAsset,RatioInNV,XGRQ End_date';
			$this->getLatestData($fundDb,$select,$transInnerCode,'MF_AssetAllocation',$Fund_distribution);
		}
		if (!empty($transQDIIInnerCode)){
			$select = 'InnerCode fund_code,AssetType AssetTypeCode,RatioInTotalAsset,RatioInNV,UpdateTime End_date';
			$this->getLatestData($fundDb,$select,$transQDIIInnerCode,'MF_QDIIAssetAllocation',$QDIIAssetAllocation,'UpdateTime>');
		}
		$Fund_distribution = array_merge($Fund_distribution,$QDIIAssetAllocation);
		$AssetType = array(
				'10020' => 'stock',  			//股票
				'10010' => 'bond',  			//债券
				'1000202' => 'cash',  			//银行存款（货币资金）
// 				'10090' => 'other',  			//其他资产 
// 				'10030' => '', 			//权证
// 				'10040' => '', 			//资产支持证券
// 				'10002' => '', 			//国债及货币资金
// 				'10099' => '', 			//资产总计
// 				'1000201' => 'bond', 			//国债
// 				'1000301' => 'bond', 			//企业债券
// 				'1000302' => 'bond', 			//金融债券
// 				'10089' => '', 			//银行存款和清算备付金合计
// 				'1000303' => ' ', 			//短期融资券
// 				'1000305' => '', 			//可转换债券
// 				'1000307' => '', 			//央行票据
// 				'1000309' => '', 			//其他债券
// 				'1000701' => '', 			//其他应收应付款贷方
// 				'1000702' => '', 			//其他应收应付款借方
// 				'1000706' => '', 			//期内债券回购融资余额
// 				'1000707' => '', 			//期末债券回购融资余额(卖出回购证券)
// 				'1009001' => 'cash', 			//交易保证金
// 				'1009003' => '', 			//应收股利
// 				'1009005' => 'cash', 			//应收利息
// 				'1009007' => '', 			//应收帐款
// 				'1009009' => 'cash', 			//应收申购款
// 				'1009011' => 'cash', 			//应收证券清算款
// 				'1009013' => 'cash', 			//其他应收款
// 				'1009014' => '', 			//配股权证
// 				'1009015' => '', 			//待摊费用
// 				'1009017' => '', 			//买入返售证券
// 				'1009019' => 'other', 			//其他资产-其他
// 				'100030201' => '', 			//政策性金融债券
// 				'100030903' => '', 			//剩余存续期超过397天的浮动利率债券
// 				'100070701' => '', 			//期末买断式回购融资余额
// 				'100901701' => '', 			//买断式回购买入返售证券
		);
		foreach ($Fund_distribution as $key=>$val){
			if (isset($AssetType[$val['AssetTypeCode']])){
				if (isset($classifyData[$val['fund_code']][$AssetType[$val['AssetTypeCode']]])){
					$classifyData[$val['fund_code']][$AssetType[$val['AssetTypeCode']]] += $val['RatioInTotalAsset'];
				}else{
					$classifyData[$val['fund_code']][$AssetType[$val['AssetTypeCode']]] = $val['RatioInTotalAsset'];
					$newData[$val['fund_code']] = array('stock'=>0, 'cash'=>0,'bond'=>0,'End_date'=>$val['End_date']);
				}
			}
		}
		foreach ($classifyData as $key=>$val){
			foreach ($val as $k=>$v){
				$newData[$key]['fund_code'] = $transHSCodes[$key]['SecuCode'];
				$newData[$key][$k] = $v;
			}
		}
		foreach ($HSCodes as $key =>$val){
			if (!isset($newData[$key]) && isset($newData[$val['InnerCode']])){
				$newData[$key] = $newData[$val['InnerCode']];
			}
		}
		$allKeys = array_column($newData,'fund_code');
		$this->db->trans_start();
		$this->db->where_in('fund_code',$allKeys)->delete('fund_distribution');
		$this->db->insert_batch('fund_distribution', $newData);
		$this->db->trans_complete();
		unset($Fund_distribution,$newData,$classifyData,$transHSCodes,$transInnerCode,$transQDIIInnerCode,$QDIIAssetAllocation);
		
//更新基金主要持仓表Fund_position
		$Fund_position = array();
		$select = 'InnerCode fund_code,StockInnerCode security_code,SharesHolding security_number,MarketValue security_assets,RatioInNV security_scale,ReportDate End_date';
		$this->getLatestData($fundDb,$select,$InnerCodes,'MF_KeyStockPortfolio',$Fund_position);
		$security_code = array_column($Fund_position,'security_code');
		$securityINfo = $fundDb->select('InnerCode,SecuCode,ChiName,SecuAbbr')->where_in('InnerCode',$security_code)->get('SecuMain')->result_array();
		$securityINfo = setkey($securityINfo,'InnerCode');
		foreach ($HSCodes as $key=>$val){
			$fundmapping[$val['InnerCode']][] = $key;
		}
		$i = count($Fund_position);
		foreach ($Fund_position as $key => $val){
			// 			$Fund_position[$key]['security_name'] = iconv("gbk","UTF-8",$securityINfo[$val['security_code']]['ChiName']);	//取股票全名
			// 			$HSCodes[$key]['fullname'] = iconv("gbk","UTF-8",$securityINfo[$val['security_code']]['ChiName']);
			$Fund_position[$key]['security_name'] /* = $HSCodes[$key]['abbrname']  */= iconv("gbk","UTF-8",$securityINfo[$val['security_code']]['SecuAbbr']);	//取股票简称
			$Fund_position[$key]['security_code'] = $securityINfo[$val['security_code']]['SecuCode'];
			$Fund_position[$key]['fund_code'] = $HSCodes[$val['fund_code']]['SecuCode'];
			if (count($fundmapping[$val['fund_code']]) >1){
				foreach ($fundmapping[$val['fund_code']] as $v){
					if ($v != $val['fund_code']){
						$Fund_position[$i] = $Fund_position[$key];
						$Fund_position[$i]['fund_code'] = $HSCodes[$v]['SecuCode'];
						$i++;
					}
				}
			}
		}
		$allKeys = array_column($Fund_position,'fund_code');
		$this->db->trans_start();
		$this->db->where_in('fund_code',$allKeys)->delete('fund_position');
		$this->db->insert_batch('fund_position', $Fund_position);
		$this->db->trans_complete();
		unset($Fund_position);
	}
	
	private function creatFundNetValue($tableName){
		$sql = "CREATE TABLE `".$tableName."` (
				`net_date` varchar(24) ,
				`net_unit` varchar(24) DEFAULT NULL,
				`net_sum` varchar(24) DEFAULT NULL,
				`net_day_growth` varchar(24) DEFAULT NULL,
				`XGRQ` datetime DEFAULT NULL COMMENT '更新日期',
				PRIMARY KEY (`net_date`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$flag = $this->db->query($sql);
		return $flag;
	}
	
	private function getLatestData(&$fundDb,$select,&$InnerCodes,$tableName,&$queryResult,$where='XGRQ>'){
		$queryDate = date('Y-m-d H:i:s',time()-864000);			//864000 = 10*3600*24 10天
		$queryResult = $fundDb->select($select)->where_in('InnerCode',$InnerCodes)->where($where,$queryDate)->get($tableName)->result_array();
		foreach ($queryResult as $val){
			$havingCode[$val['Fund_code']] = 1;
		}
		$reSearchCode = array();
		foreach ($InnerCodes as $val){
			if (!isset($havingCode[$val])){
				$reSearchCode[] = $val;
			}
		}
		if (!empty($reSearchCode)){
			$queryDate = date('Y-m-d H:i:s',time()-8035200-10*3600*24);	//8035200 = 93*3600*24 93天
			$reQuery = $fundDb->select($select)->where_in('InnerCode',$reSearchCode)->where($where,$queryDate)->get($tableName)->result_array();
			$queryResult = array_merge($queryResult,$reQuery);
		}
	}
	
	/* 		$AssetType = array(
	 '非国债债券' => 'bond',
	 '股票' => 'stock',
	 '国债' => 'bond',
	 '国债及货币资金' => '',
	 '交易保证金' => 'cash',
	 '金融债券' => 'bond',
	 '其它资产' => 'other',
	 '银行存款' => 'cash',
	 '应收利息' => 'cash',
	 '应收证券清算款' => 'cash',
	 '债券' => 'bond',
	 '政策性金融债券' => 'bond',
	 '资产净值' => '',
	 '资产总计' => '',
	 '待摊费用' => 'cash',
	 '其他应收款' => 'cash',
	 '买入返售证券' => 'bond',
	 '可转换债券' => 'bond',
	 '企业债券' => 'bond',
	 '其他资产-其他' => 'other',
	 '应收申购款' => 'cash',
	 '中期票据' => 'cash',
	 '短期融资券' => 'bond',
	 '其他债券' => 'bond',
	 '同业存单' => 'cash',
	 '资产支持证券' => '',
	 '剩余存续期超过397天的浮动利率债券' => '',
	 '买断式回购买入返售证券' => '',
	 '其他配置' => '',
	 '央行票据' => '',
	 '应收股利' => '',
	 '地方政府债券' => '',
	 '基金投资合计' => '',
	 '贵金属投资合计' => '',
	 '4.金融衍生品投资' => '',
	 '3.固定收益类投资' => '',
	 '5.买入返售金融资产' => '',
	 '1.权益类投资' => '',
	 );
	$res = $fundDb->distinct()->select('AssetType')->get('MF_AssetAllocation')->result_array();
	// 		$res = $fundDb->where(array('DM'=>11001))->get('CT_SystemConst')->result_array();
	foreach ($res as $key=>$val){
	foreach ($val as $k=>$v){
	$res[$key][$k] = iconv("gbk","UTF-8",$v);
	echo "'".$res[$key][$k]."' => '',</br>";
	}
	}
	var_dump(count($res)); */	
	
}

<?php

$config['branchcode'] = '386';

$config['channelid'] = array(
	'KQ01' => "快钱网点",	
);
// 基金类型：
// 0-股票型基金 1-债券型基金
// 2-货币型基金 3-混合型基金
// 4-专户基金  5-指数型基金
// 6-QDII基金
$config['fundtype'] = array(
		'0' => '股票型基金',
		'1' => '债券型基金',
		'2' => '货币型基金',
		'3' => '混合型基金',
		'4' => '专户基金',
		'5' => '指数型基金',
		'6' => 'QDII基金',
);

// 收费方式：
// ‘A’-前收费‘B’-后收费
$config['sharetype'] = array(
		'A' => '前收费',
		'B' => '后收费',
);

//证件类型
$config['certificatetype'] = array(
		'0' => '身份证',
		'1' => '护照',
		'2' => '军官证',
		'3' => '士兵证',
		'4' => '回乡证',
		'5' => '户口本',
		'6' => '外国护照',
);

$config['businesscode'] = array(
		'20'=> '认购申请',//20 认购申请
		'22'=> '申购申请',//22	申购申请
		'24'=> '赎回申请',//24	赎回申请
		'25'=> '预约赎回申请',//25	预约赎回申请
		'39'=> '定期定额申购申请',//39	定期定额申购申请
		'26'=> '转托管申请',//26	转托管申请
		'27'=> '转托管转入申请',//27	转托管转入申请
		'28'=> '转托管转出申请',//28	转托管转出申请
		'29'=> '设置分红方式申请',//29	设置分红方式申请
		'31'=> '基金份额冻结申请',//31	基金份额冻结申请
		'32'=> '基金份额解冻申请',//32	基金份额解冻申请
		'26'=> '基金转换申请',//36	基金转换申请
		'59'=> '定投协议开通申请',//59	定投协议开通申请
		'60'=> '定投协议撤销申请',//60	定投协议撤销申请
		'61'=> '定投协议变更申请',//61	定投协议变更申请
		'69'=> '定期赎回开通申请',//69	定期赎回开通申请
		'70'=> '定期赎回撤销申请',//70	定期赎回撤销申请
		'71'=> '定期赎回变更申请',//71	定期赎回变更申请
		'80'=> '基金确权申请',//80	基金确权申请
		'C2'=> '份额调增申请',//C2	份额调增申请
		'C3'=> '份额调减申请',//C3	份额调减申请
		'G1'=> '定时定额暂停申请',//G1	定时定额暂停申请
		'G2'=> '定时定额恢复申请',//G2	定时定额恢复申请
		'G3'=> '定时赎回暂停申请',//G3	定时赎回暂停申请
		'G4'=> '定时赎回恢复申请',//G4	定时赎回恢复申请
		'98'=> 'T+0快速赎回',//98	T+0快速赎回
);

//分红方式
$config['dividendmethod'] = array(
		'0' => '红利转投',
		'1' => '现金分红',
// 		'2' => '利得现金增值再投资',
// 		'3' => '增值现金利得再投资',
// 		'4' => '部分再投资',
// 		'5' => '赠送',
);

//交易手段
$config['tradingmethod'] = array(
		'0' => '柜台',
		'1' => '电话',
		'2' => '网上交易',
		'3' => '传真',
		'4' => '手机',
		'5' => '微信',
		'6' => '第三方接入',
);

//基金公司
$config['ta'] = array(
		'21' => '金鹰直销',
		'48' => '工银价值',
		'70' => '平安大华',
);


//客户风险承受能力(1:安全型 2:保守型 3:稳健型 4:积极型 5:进取型)
$config['custrisk'] = array(
		'01' => '安全型',
		'02' => '保守型',
		'03' => '稳健型',
		'04' => '积极型',
		'05' => '进取型',
);

//产品风险等级和客户风险承受能力对应
$config['productrisk'] = array(
		'01' => '低风险',
		'02' => '低中风险',
		'03' => '中风险',
		'04' => '中高风险',
		'05' => '高风险',
);

$config['bankcard_status'] = array(
		'0' => '正常',
		'1' => '销户中',
		'2' => '已销户',
		'3' => '销户中',
);



//基金状态/基金业务	认购	申购	赎回	基金转换	分红方式变更
// 0-交易			N	Y	Y	Y		Y
// 1-发行			Y	N	N	N		Y
// 2-发行成功		N	N	N	N		Y
// 3-发行失败		N	N	N	N		N
// 4-基金停止交易	N	N	N	N		N
// 5-停止申购		N	N	Y	Y		Y
// 6-停止赎回		N	Y	N	N		Y
// 7-权益登记		N	Y	Y	Y		Y
// 8-红利发放		N	Y	Y	Y		Y
// 9-基金封闭		N	N	N	N		Y
// a-基金终止		N	N	N	N		N
$config['fund_status'] = array(
		'0' => array('status' => '交易', 'pre_purchase' => 'N', 'purchase' => 'Y', 'redeem' => 'Y', 'conversion' => 'Y', 'bonus_change' => 'Y'),
		'1' => array('status' => '发行', 'pre_purchase' => 'Y', 'purchase' => 'N', 'redeem' => 'N', 'conversion' => 'N', 'bonus_change' => 'Y'),
		'2' => array('status' => '发行成功', 'pre_purchase' => 'N', 'purchase' => 'N', 'redeem' => 'N', 'conversion' => 'N', 'bonus_change' => 'Y'),
		'3' => array('status' => '发行失败', 'pre_purchase' => 'N', 'purchase' => 'N', 'redeem' => 'N', 'conversion' => 'N', 'bonus_change' => 'N'),
		'4' => array('status' => '基金停止交易', 'pre_purchase' => 'N', 'purchase' => 'N', 'redeem' => 'N', 'conversion' => 'N', 'bonus_change' =>'N'),
		'5' => array('status' => '停止申购', 'pre_purchase' => 'N', 'purchase' => 'N', 'redeem' => 'Y', 'conversion' => 'Y', 'bonus_change' => 'Y'),
		'6' => array('status' => '停止赎回', 'pre_purchase' => 'N', 'purchase' => 'Y', 'redeem' => 'N', 'conversion' => 'N', 'bonus_change' => 'Y'),
		'7' => array('status' => '权益登记', 'pre_purchase' => 'N', 'purchase' => 'Y', 'redeem' => 'Y', 'conversion' => 'Y', 'bonus_change' => 'Y'),
		'8' => array('status' => '红利发放', 'pre_purchase' => 'N', 'purchase' => 'Y', 'redeem' => 'Y', 'conversion' => 'Y', 'bonus_change' => 'Y'),
		'9' => array('status' => '基金封闭', 'pre_purchase' => 'N', 'purchase' => 'N', 'redeem' => 'N', 'conversion' => 'N', 'bonus_change' => 'Y'),
		'a' => array('status' => '基金终止', 'pre_purchase' => 'N', 'purchase' => 'N', 'redeem' => 'N', 'conversion' => 'N', 'bonus_change' => 'N'),
);

//申请单状态
$config['applaystatus'] = array(
		'00' => '待复核',
		'01' => '待勾兑',
		'02' => '待报',
		'03' => '',
		'04' => '废单',
		'05' => '已撤',
		'06' => '已报',
		'07' => '已确认',
		'08' => '已结束',
);

//支付状态
$config['paystatus'] = array(
		'00' => '未支付',
		'01' => '委托正在处理',
		'02' => '支付成功',
		'03' => '支付失败',
		'07' => '托收成功',
);

//配置选择的支付渠道
$config['selectChannel'] = 'KQ';
//需要提供开户省、市的银行列表
$config['needProvCity'] = array(
	'KQ' => array(),
);

$config['investorType'] = array(
		'11' => '自然人',
		'01' => '法人或其他组织',
		'02' => '金融机构',
		'03' => '证券公司子公司',
		'04' => '期货公司子公司',
		'05' => '私募基金管理人',
		'06' => '社会保障基金',
		'07' => '企业年金等养老基金',
		'08' => '慈善基金等社会公益基金',
		'09' => '合格境外机构投资者（QFII）',
		'10' => '人民币合格境外机构投资者（RQFII）',
);

$config['investorProfession'] = array(0=>'未从事相关职业',1=>'专业投资者的高级管理人员、从事金融相关业务的注册会计师或律师');

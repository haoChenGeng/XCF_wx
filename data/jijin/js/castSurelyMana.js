window.onload = function() {
	getCastList();
}

function getCastList(){
	mui.ajax("/jijin/FixedInvestmentController/FixedInvestmentQuery",{
		dataType: 'json',
		type: "GET",
		success:function(res){
			if(res.code==0){
				var castList = res.data.fixed;
				var html = "";
				for ( var i in castList) {
					var item = castList[i];
					var status = item.status=="N"?"正常":item.status=="C"?"终止":"未知";
					var numLength = item.depositacct.length;
					var lastNum = item.depositacct.substring(numLength-3,numLength);
					
					html += '<div class="item-f1 mui-row">\
						<div class="castName textOver">'+item.fundname+'（'+item.fundcode+'）</div>\
						<span class="castState">'+(item.risklevel||"")+'</span>\
					</div>\
					<div class="item-f2 mui-row">\
						<span class="payType">扣款方式：'+item.channelname+'（尾号'+lastNum+'）</span>\
						<span class="castState">'+status+'</span>\
					</div>\
					<div class="item-f3 mui-row">\
						<span class="f3-l">\
							<span class="payTimeType">'+item.periodremark+'定投：</span>\
							<span class="payAverage">'+item.continueinvestamount+'元</span>\
						</span>\
						<span class="f3-r">\
							下次扣款：<span class="nextPayDate">'+item.nextinvestdate+'</span>\
						</span>\
					</div>';
							
					var li = document.createElement("div");
					li.className = "clearfix castCurely-item"
					li.innerHTML = html;
					li.onclick = function(){
						window.location.href="/application/views/jijin/trade/castSurelyDetail.html?buyplanno="+item.buyplanno;
					}
					byClass("castCurely-list").appendChild(li);
				}//列表循环结束
			}
		}//success end
	});
}
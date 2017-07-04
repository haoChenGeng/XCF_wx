// JavaScript Document

$(function(){
	/*Img Max-width*/
/*	$(".edittext img").each(function(){
		if($(this).width() > $(this).parents('.edittext').width()){
			$(this).width("100%");
			$(this).height("auto");
		}
	});*/
	var nav_1 = $('.fing_content').height();
	$(window).scroll(function(event){
		var scrollTop = $(this).scrollTop();
		if( scrollTop >= 10){
			$('header.my .span01').css({
				'background': 'rgba(0, 0, 0, 0.2)',
				'height': '88px'
			})
			
		}else{
			$('header.my .span01').css({
				'background': 'rgba(0, 0, 0, 0.0)',
				'height':'88px'
			})
		}
		
		if( scrollTop >= nav_1){
			$('.pinglun_content .title').css({
				'position': 'fixed',
				'top': '0',
				'width': '100%',
			})
		}else if( scrollTop < nav_1){
			$('.pinglun_content .title').css({
				'position': 'relative',
			})
		}
		
	});
});
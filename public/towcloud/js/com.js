// JavaScript Document
var cloud = {
	int:function(){
		this.cw = document.documentElement.clientWidth || document.body.clientWidth;
		this.ch = document.documentElement.clientHeight || document.body.clientHeight;
		this.contextmenu = document.getElementById("contextmenu");
		
		 var dum = document.body || document.documentElement;
 		 dum.oncontextmenu=function(){return false; };
	},
	tc:function(id){
		$(".alertbg").hide();
		$("#"+id).show();
		//$(".modal h4").eq(count).show();
		
		var lw = (this.cw - $(".modal").width())/2;
		var lh = (this.ch - $(".modal").height())/2;
		
		$(".window-overlay").removeClass("none");
		$(".modal").css({left:lw+"px",top:lh+"px"});
	}
};

$(function(){
	
	cloud.int();
/*
	$(".zone-switcher").click(function(e){
		var ee = e||event;	
		ee.stopPropagation();  
		
		if($(".zone-switcher-select").css("display") == "none")
		$(".zone-switcher-select").show();		
		else
		$(".zone-switcher-select").hide();	
	})

	//右键菜单弹框
	$(".table-bordered tbody tr").mousedown(function(e){
		var ee = e||event;	
		var btnNum = ee.button;
		if(btnNum==2){
			$(".contextmenu").css({left:ee.clientX,top:ee.clientY});
			$(".window-overlay .id").text($(this).find(".id").eq(0).text());
		}		
	});
	*/	
	/*点空白处隐藏*/
	$(document).click(function (e) { $(".zone-switcher-select").hide(); });   
	
	/*关闭右键弹框*/
	$(window).click(function(e){
		var ee = e||event;	
		var btnNum = ee.button;
		if(btnNum!=2)
		$(".contextmenu").css({top:"100%"});
	});
	
	/*操作日志点击显示*/
	$(".activity-item").toggle(function(){
		$(this).addClass("on");
	},function(){
		$(this).removeClass("on");
	});
	
	/*关闭弹出框*/
	$(".close,.modal-btn.btn-cancel").click(function(){
		$(".window-overlay").addClass("none");
	});
	
	/*右键弹框里的按钮点击*/
	$(".contextmenu a").click(function(){
		//cloud.tc(0);
		//cloud.tc(1);		
		//cloud.tc(3);
		cloud.tc($(".contextmenu a").index($(this)));
	});
	
	/*操作日志 全部，等待中，执行中，成功，失败 按钮点击*/
	$(".page-tabs li").click(function(){
		$(this).addClass("selected");
		$(this).siblings("li").removeClass("selected");
	});
/*	
	//左边菜单栏点击
	$(".navigation-permission li").click(function(){
		$(this).addClass("selected");
		$(this).siblings("li").removeClass("selected");
	});
*/	
	
	/*弹出层拖动*/
	$(".modal-header").mousedown(function(e){
		var ee = e||event;	
		var x = ee.clientX - parseInt($(".modal").css("left"));
		var y = ee.clientY - parseInt($(".modal").css("top"));	
		
		$(".modal-header").mousemove(function(e){
			var ee2 = e||event;	
			var x2 = ee2.clientX;
			var y2 = ee2.clientY;
			$(".modal").css({left:(x2-x)+"px",top:(y2-y)+"px"});
		});
	});
	
	$(".modal-header").mouseup(function(e){
		$(".modal-header").unbind("mousemove");
	});
	/**/
	
});




















// JavaScript Document
function DateThan(prevStr,nextStr,flag){//flag 1 重置后面的日期 0 重置前面的日期
	var str = '',
	prevD = new Date(Date.parse(prevStr.replace(/-/g,"/"))),
	prevTime = prevD.getTime(),
	nextD = new Date(Date.parse(nextStr.replace(/-/g,"/")));
	nextTime = nextD.getTime();
	
	if(prevTime > nextTime){
		if(flag > 0){
			var year = prevD.getFullYear(),
			month = prevD.getMonth() + 1,
			day = prevD.getDate();
			str = year + '-' + (month > 9?month:'0'+month) + '-' +(day > 9?day:'0'+day);
		}else{
			var year = nextD.getFullYear(),
			month = nextD.getMonth() + 1,
			day = nextD.getDate();
			str = year + '-' + (month > 9?month:'0'+month) + '-' +(day > 9?day:'0'+day);
		}	
	}
	return str;
}

function dateCount(prevStr,nextStr){
	var nextD = new Date(Date.parse(nextStr.replace(/-/g,"/"))),
	nextTime = nextD.getTime(),
	D = new Date(),
	y = D.getFullYear(),
	m = D.getMonth() + 1,
	d = D.getDate(),
	str = y + '-'+(m > 9?m:'0'+m) + '-' +(d > 9?d:'0'+d),
	ToDay = new Date(Date.parse(str.replace(/-/g,"/"))),
	ToDayTime = ToDay.getTime(),
	timeLen = nextTime - ToDayTime;
	if(timeLen == 0){
		var prevD = new Date(Date.parse(prevStr.replace(/-/g,"/")));
		prevTime = prevD.getTime(),
		srchLen = nextTime - prevTime,
		n = srchLen/(24*60*60*1000);
		switch(n){
			case 0:
				$('.stockLi').find('a').eq(1).addClass('Ac').siblings('a').removeClass('Ac');
				break;
			case 7:
				$('.stockLi').find('a').eq(2).addClass('Ac').siblings('a').removeClass('Ac');
				break;
			case 30:
				$('.stockLi').find('a').eq(3).addClass('Ac').siblings('a').removeClass('Ac');
				break;
			default:{
				$('.stockLi').find('a').removeClass('Ac');
			}
		}
	}else if(timeLen < 0){
		var n = Math.abs(timeLen/(24*60*60*1000));
		if(n == 1){
			var prevD = new Date(Date.parse(prevStr.replace(/-/g,"/")));
			prevTime = prevD.getTime(),
			srchLen = nextTime - prevTime;
			if(srchLen == 0){
				$('.stockLi').find('a').eq(0).addClass('Ac').siblings('a').removeClass('Ac');
			}else{
				$('.stockLi').find('a').removeClass('Ac');
			}
		}else{
			$('.stockLi').find('a').removeClass('Ac');
		}
	}
}
(function($){
	$.fn.userHover = function(){//用户模块交互
		var T = 0;
		$(this).each(function(i){
            var move = $(this).find('.headUser')
			if($(move).length > 0){
				var h = $(move).outerHeight(true) - 100;
				$(move).css('top',(-1)*h+'px');
				$(this).hover(function(){
					var t = parseInt($(move).css('top'));
					clearInterval(T);
					if(t < 50){		
						toMove(move,(h+130),1);
					}else{
						$(move).css('top','125px');
					}
				},function(){
					clearInterval(T);
					toMove(move,230,-1)
				})
			}
        });
		function toMove(obj,D,flag){
			var n = 10,i = 1,
			r = D%n,
			h = $(obj).outerHeight(true),
			t = parseInt($(obj).css('top')),
			e = (D-r)/n;
			T = setInterval(function(){
				t = t + flag*e + (i == n?r:0)*flag;
				if(flag>0){
					t = t > 130?130:t;
				}
				if(flag<0){
					t = t < (100-h)?(100-h):t;
				}
				$(obj).css('top',t+'px');
				if(i == n){
					clearInterval(T);
				}
				i++;
			},20);
		}
	}
	$.fn.Qfigure = function(auto){//轮播图
		auto = typeof auto == 'undefined'?false:auto;
		$(this).each(function(index, element) {
            var nli,autoT,T,allw,w,box = this,Index = 0,evtClk = 'click',
			hasTouch = ("createTouch" in document) || ('ontouchstart' in window),
			ul = $(this).children('ul'),
			li = $(ul).children('li'),
			lbtn = $(this).next(),
			rbtn = $(lbtn).next(),
			len = $(li).length;
			
			if(len == 1){
				$(lbtn).length>0?$(lbtn).css('display','none'):'';
				$(rbtn).length>0?$(rbtn).css('display','none'):'';
			}else{
				var numDiv = $('<div>');
				w = $(li).eq(0).width();
				$(numDiv).addClass('SliderNum');
				for(var j = 0; j < len; j++){
					var span = $('<span>');
					if(j == 0){
						$(span).addClass('Ac');
					}
					$(li).eq(j).css('width',w+'px');
					$(numDiv).append(span);
				}
				$(box).append(numDiv);
				nli = $(numDiv).find('span');
				$(ul).append($(li).eq(0).clone());
				$(ul).prepend($(li).eq(len-1).clone());
				li = $(ul).children('li');
				len = $(li).length;
				Index = 1;
				init();
				if(auto){
					autoMove();
				}
				$(lbtn).bind(evtClk,function(){
					clearInterval(T);
					clearInterval(autoT);
					Index = Index == 0?len-2:Index;
					$(ul).css({'left':(-1*Index*w)+'px'});
					indexCount(-1);
					move(1,w);
					if(auto){
						autoMove();	
					}
				});
				$(rbtn).bind(evtClk,function(){
					clearInterval(T);
					clearInterval(autoT);
					Index = Index == len-1?0:Index;
					$(ul).css({'left':(-1*Index*w)+'px'});
					indexCount(1);
					move(-1,w);
					if(auto){
						autoMove();
					}
				});
			}
						
			function init(){
				var touchFlag = ("createTouch" in document) || ('ontouchstart' in window);
				w = touchFlag?$(box).width():$(li).eq(0).outerWidth(true);
				allw = $(box).width();
				evtClk = touchFlag?'touchend':'click';
				$(ul).css({'width':(len*w)+'px','left':(w*-1)+'px'});
			}
			
			function indexCount(flag){//20171206
				flag = typeof flag == 'undefined'?1:flag;
				Index = Index + flag*1;
				if(flag>0){
					if(Index >= len-1){
						Index = 1
					}
				}else {
					if(Index <= 0){
						Index = len-2
					}
				}
			}
			function autoMove(flag){
				clearInterval(autoT);
				flag = typeof flag == 'undefined'?-1:flag;
				autoT = setInterval(function(){
					clearInterval(T);
					indexCount(flag*-1)
					move(flag,w)
				},10000);
			}			
			function move(flag,D){
				var i = 0,
				m = 10,
				R = D%10,
				E = (D - R)/10,
				L = parseInt($(ul).css('left'));
				clearInterval(T);
				/*clearInterval(autoT);*/
				T = setInterval(function(){
					i++;
					L = L + flag*E + (i == m?flag*R:0);
					$(ul).css({'left':L+'px'});
					if(i == m){
						clearInterval(T);
						/*clearInterval(autoT);*/
						$(ul).css({'left':(-1*Index*w)+'px'});
						nunExg();//20171206
					}
				},30);
			}
			function nunExg(){//number
				if($(nli).length > 0){
					var ni = Index - 1,
					_len = $(nli).length;
					if(Index == 0){
						ni = _len - 1;
					}
					$(nli).eq(ni).addClass('Ac').siblings('span').removeClass('Ac');
				}
			}
		});
	}
	//美化下拉选框
	var z = 1000;
	$.fn.uSelect = function(callback){//美化下拉选框，callback 触发下拉选框onChange事件
		$(this).each(function(ii){
			var T,pli,index = 0,fn,
			//w = $(this).width(),
			len = $(this).find('.SltBox').length,
			slt = $(this).find('select')[0],
			w = $(slt).outerWidth(),
			ops = $(slt).children(),
			inp = $('<input>'),
			box = $('<div>'),
			btn = $('<div>');
			w = w > $(this).width()?w:$(this).width();
			w = w - ($(this).innerWidth() - $(this).width());		
			index = slt.selectedIndex;
			//alert($(slt).innerWidth());
			if(len > 0){//$(slt).is(':hidden')//alert($(slt).is(':hidden'));
				$(slt).css({'visibility':'hidden'});
				inp = $(this).find('input');
				box = $(this).find('.SltBox');
				btn = $(this).find('.SltBtn');
				$(box).html('');
			}else{
				z--;
				$(this).css('width',w+'px');
				$(this).css('zIndex',z);
				$(box).addClass('SltBox');
				$(btn).addClass('SltBtn');
				$(inp).attr('readonly',true);
				$(inp).css('width',w+'px')
				$(this).append(inp);
				$(this).append(btn)
				$(this).append(box);
				$(slt).css('display','none');
				$(box).css('width',$(this).innerWidth()+'px');
				$(box).css('top',$(this).outerHeight()-1+'px');
				$(inp).css('left',($(this).innerWidth() - $(this).width())/2+'px')
				$(box).hover(
					function(){
						$(this).addClass('SltHov');
					},
					function(){
						$(this).removeClass('SltHov');
					}
				);
				$(inp).click(function(){
					if($(btn).hasClass('SltUp')){
						$(box).css('display','none');
						$(btn).removeClass('SltUp');
					}else{
						$(box).css('display','block');
						$(btn).addClass('SltUp');
					}
				});
				$(this).hover(
					function(){
						clearInterval(T);
					},
					function(){
						clearInterval(T);
						T = setTimeout(function(){
							$(box).css('display','none');
							$(btn).removeClass('SltUp');
						},500)
					}
				);
				$(btn).click(function(){
					if($(this).hasClass('SltUp')){
						$(box).css('display','none');
						$(btn).removeClass('SltUp');
					}else{
						$(box).css('display','block');
						$(btn).addClass('SltUp');
					}
				});
			}
			$(ops).each(function(i){
				var p = $('<p>');
				$(p).html($(this).html());
				$(box).append(p);
				if(i == index){
					$(inp).val($(this).html());
					/*if($(inp).is(':hidden')){
						$(btn).html($(this).html());
					}else{
						$(btn).html('');
					}*/
					$(p).addClass('Ac');
				}
            });
			pli = $(box).find('p');
			$(pli).each(function(i) {
				$(this).click(function(){
					$(inp).val($(this).html());
					if($(inp).is(':hidden')){
						$(btn).html($(this).html());
					}
					slt.selectedIndex = i;					
					//$(ops).eq(i).attr("selected",true).siblings('option').removeAttr('selected');
					if(!$(this).hasClass('Ac')){
						//callback(slt);
						if(slt.fireEvent){
							slt.fireEvent('onchange');
						}else{
							var e = document.createEvent('HTMLEvents');
        					e.initEvent('change', false, false);
							slt.dispatchEvent(e);
						}
					}
					$(this).addClass('Ac').siblings('p').removeClass('Ac');
					$(box).css('display','none');
					$(btn).removeClass('SltUp');
				});
				$(this).hover(
					function(){
						$(this).addClass('Cur');
					},
					function(){
						$(this).removeClass('Cur');
					}
				);
            });
		});
	}
	
	/*倒计时*/
	$.fn.codeTime = function(Len){//Len为空是启动计时器
		var o = this;
		if(typeof Len == 'undefined'){
			if(!$(o).hasClass('unClk')){
				var t = eT;
				clearInterval(Time);
				$(o).addClass('unClk').html(eT+'s');
				Time = setInterval(function(){
					eT--;
					if(eT == 0){
						$(o).removeClass('unClk').html('重新获取');
						clearInterval(Time);
						eT = t;
					}else{
						$(o).html(eT+'s');
					}
				},1000);
			}
		}else{//重置计时器 Len为下一次启动计时器历时 例如60
			clearInterval(Time);
			eT = Len;
			$(o).removeClass('unClk').html('获取验证码');
		}
	}
	$.fn.errorTip = function(){
		$(this).each(function(i){
			var w = 0,
			tip = $(this).find('.TipUl');
            $(tip).css('display','block');
			w = $(tip).width() + 11;
			$(tip).css({'display':'none','right':(-w/2 + 6)+'px'});
			$(this).hover(
			function(){
				$(this).find('.TipUl').css('display','block');
			},
			function(){
				$(this).find('.TipUl').css('display','none');
			})
        });
	}
	$.fn.dateStr = function(){
		$(this).each(function(i){
            var len = parseInt($(this).attr('datelen')),
			li = $(this).parent().find('.dateLi'),
			n = $(this).prevAll('a').length,
			inp = $(li).find('input');
			if(len >= 0){
				var d = new Date(),
				time = d.getTime() - len*24*60*60*1000;
				D = new Date(time),
				y = d.getFullYear(),
				m = d.getMonth() + 1,
				day = d.getDate(),
				_y = D.getFullYear(),
				_m = D.getMonth() + 1,
				_day = D.getDate(),
				str = y+'-'+(m>9?m:'0'+m)+'-'+(day>9?day:'0'+day),
				_str = _y+'-'+(_m>9?_m:'0'+_m)+'-'+(_day>9?_day:'0'+_day);
				$(inp).eq(0).val(_str);
				//$(inp).eq(1).val(str);
				if(n == 0){
					$(inp).eq(1).val(_str);
				}else{
					$(inp).eq(1).val(str);
				}
			}			
        });
	}
	$.fn.mainBox = function(){
		$(this).each(function(i){
            var page = $(this).find('.contDes'),
			side = $(this).find('.sBlock'),
			sh = $(side).height(),
			mh = $(this).height();
			if(sh > mh){
				var h = $(this).height() - $(this).innerHeight();
				mh = sh - h;
				$(this).css('height',mh+'px');
			}
			if($(page).length > 0){
				var ph = $(page).offset().top - $(this).offset().top + $(page).outerHeight(); 
				
				if(mh - ph > 50){
					$(page).css({'position':'absolute', 'bottom':0, 'width':'870px'})
				}
			}
        });
	}
	//表格浮动
	$.fn.trHover = function(){
		$(this).each(function(i){
            var tr = $(this).find('tr');
			$(tr).each(function() {
                $(this).hover(function(){
					$(this).addClass('Cur');
				},function(){
					$(this).removeClass('Cur');
				})
            });
        });
	}
	/*模拟placeholder密码框*/
	$.fn.placeholder = function(flag){
		flag = typeof flag == 'undefined'?true:flag;
		$(this).each(function(i){
			var o = $(this).find('input'),
			vstr = $(o).val();
			if(vstr != ''){
				$(o).prev().css('display','none');
			}
			if(flag){
				$(o).bind('focus',function(){
					var str = $(this).val();
					if(str == ''){
						$(this).prev().css('display','none');
					}
					$(this).showTip('Right');
				});
			}
        });
	}
	//电话号码校验
	$.fn.inpTel = function(itype,iReg){
		var flag = true,
		reg = typeof iReg == 'undefined'?/^1[3|4|5|7|8]\d{9}$|^([6|9])\d{7}$|^[6]([8|6])\d{5}$/g:iReg;
		itype = typeof itype=='undefined'?false:itype;
		
		$(this).each(function(i){
			var str = $(this).val();
			pos = $(this).getCursor();
			if(!reg.test(str)){
				if(itype){	
					var len = str.length -1;
					str = str.substr(0,pos-1)+str.substr(pos,len);//str.substr(0,len);
					if(str != ''){
						
						var _reg = /[^\d]/g;
						str = str.replace(_reg,'');
						$(this).showTip('Right');
					}
					$(this).val(str);
					$(this).setCursor(pos-1);
				}else{
					flag = false;
				}
			}
		})
		return flag;
	}
	/*金额输入事件 start*/
	$.fn.inpCash = function(itype,iReg){
		var flag = true,
		reg = typeof iReg == 'undefined'?/^0$|^0\.\d{0,12}$|^[1-9]\d*\.?\d{0,12}$/:iReg;
		itype = typeof itype=='undefined'?false:itype;
		$(this).each(function(i){
            var str = $(this).val(),
			pos = $(this).getCursor();
			
			if(!reg.test(str)){
				if(itype){		
					var len = str.length -1;
					str = str.substr(0,pos-1)+str.substr(pos,len);//str.substr(0,len);
					if(str != ''){
						var _reg = /[^\d\.]/g;
						str = str.replace(_reg,'');
						$(this).showTip('Right');
					}
					$(this).val(str);
					$(this).setCursor(pos-1);
				}else{
					flag = false;
				}
			}
        });
		return flag;
	}
	/*浮点数输入事件 start*/
	$.fn.inpNum = function(itype,iReg){
		var flag = true,
		reg = typeof iReg == 'undefined'?/^0$|^0\.\d{0,2}$|^[1-9]\d*\.?\d{0,2}$/g:iReg;
		itype = typeof itype=='undefined'?false:itype;
		$(this).each(function(i){
            var str = $(this).val(),
			pos = $(this).getCursor();
			
			if(!reg.test(str)){
				if(itype){		
					var len = str.length -1;
					str = str.substr(0,pos-1)+str.substr(pos,len);//str.substr(0,len);
					if(str == ''){
						$(this).showTip('Error');
					}else{
						var _reg = /[^\d\.]/g;
						str = str.replace(_reg,'');
					}
					$(this).val(str);
					$(this).setCursor(pos-1);
				}else{
					flag = false;
				}
			}
        });
		return flag;
	}
	//获取光标的位置
	$.fn.getCursor = function(){
		var Pos = 0;
		$(this).each(function(i){
            var o = this;
			if(document.selection) {// IE Support 
				o.focus(); 
				var S1 = document.selection.createRange(),
				S2 = S1.duplicate();
				S1.moveStart("character", -event.srcElement.value.length);
				Pos = S1.text.length;
			}else if(o.selectionStart || o.selectionStart == '0'){// Firefox support 
				Pos = o.selectionStart;
			}
        });
		return Pos;
	}
	//设置光标位置
	$.fn.setCursor = function(pos,code){
		$(this).each(function(i){
            var o = this;
			if(o.setSelectionRange){ 
				o.focus(); 
				o.setSelectionRange(pos,pos); 
			}else if (o.createTextRange){ 
				o.focus(); 
				var range = o.createTextRange(); 
				range.collapse(true); 
				range.moveEnd('character',pos); 
				range.moveStart('character',pos); 
				range.select(); 
			} 
        });
	}
	/*错误提示*/
	$.fn.showTip = function(cls,msg){
		$(this).each(function(i){
			var p = $(this).parents('span'),
			em = $(p).find('em:last');
			p = $(em).length == 0?$(this).parents('.formLi'):p;
			em = $(em).length == 0?$(p).find('em:last'):em;
			//em = $(this).parents('.formLi').find('em:last');
			if($(em).length > 0){
				msg = typeof msg == 'undefined'?'':msg;
				//$(em).attr('class',cls);
				$(em).html(msg);
			}
			if(cls == 'Error'){
				$(p).addClass('Error');
			}else{
				$(p).removeClass('Error');
			}
			
        });
	}
	/*弹窗事件处理*/
	$.fn.winInit = function(){//初始分关闭 按钮点击事件
		$(this).each(function(i){
            var cls = $(this).find('.qCls'),
			btn = $(this).find('.qWbtn'),
			bli = $(btn).children('a');
			$(cls).each(function(j){
                $(this).bind('click',function(){
					$(this).parents('.qWin').css('display','none');
					$('.qMask').css('display','none');
				})
            });
			$(bli).each(function(i){
				if($(this).hasClass('undo')){
					$(this).bind('click',function(){
						$(this).parents('.qWin').css('display','none');
						if($('.qWin:visible').length == 0){
							$('.qMask').css('display','none');
						}
					});
				}
            });
        });
	}
	$.fn.winPlace = function(){//初始化弹窗位置及高度
		$(this).each(function(i){
            var w = document.documentElement.clientWidth,
			h = document.documentElement.clientHeight,
			//sTop = $(window).scrollTop(),
			sh = $('body').outerHeight(true),
			_h = $(this).height(),
			_w = $(this).outerWidth(true),
			_ah = $(this).outerHeight(true) - $(this).height(),
			left = (w - _w)/2,
			top = (h - _h - _ah)/2;
			if(_h > h){
				var con = $(this).find('.qWmess'),
				_ch = $(con).prev().outerHeight(true) + $(con).next().outerHeight(true) + ($(con).outerHeight(true) - $(con).height()) + _ah;
				$(this).css('height',(h-_ah)+'px');
				$(con).css('height',(h-_ch)+'px');
			}else{
				var con = $(this).find('.qWmess');
				//$(con).css('height','auto');
			}
			left = left < 0?0:left;
			top = top < 0?0:top;
			
			$(this).css({'left':left+'px','top':top+'px'});
        });
	}
	$.fn.showWin = function(msg,til){//显示弹窗
		$(this).each(function(i){
			if(typeof msg != 'undefined'){
				$(this).find('.qWmess').html(msg);
			}
			if(typeof til !== 'undefined'){
				$(this).find('h3>span').html(til);
			}
            $(this).css('display','block');
			var mask = $('.qMask'),
			w = document.documentElement.clientWidth,
			h = document.documentElement.clientHeight,
			sh = $('body').outerHeight(true);
			$(this).winPlace();
			$(mask).css({'height':(h<sh?sh:h)+'px','display':'block'});
        });	
	}
	$.fn.hidWin = function(){//隐藏弹窗
		$(this).each(function(i){
            $(this).css('display','none');
			$('.qMask').css('display','none');
        });
	}
	$('.qWin').winInit();//初始化弹窗关闭事件
})(jQuery)
var super_include, status_map;
super_include = {
	path: '/static/cms/js/', resources: [
		'super_common.js',
		//'super_select2.js',
		//'super_upload2.js',
		//'super_table.js',
		/*'global_data.js',
	 'super_language.js',
	 'super_form.js',
	 'super_select2.js',
	 'super_function.js',
	 'super_tips.js',
	 'super_pinyin.js',
	 'check_page.js'*/
		'super_table.js'],
	//基本初始化（只执行一次）
	_init: function () {
		var _this = this;
		_this._init_file();
		_this._init_data();
		$(function(){
			//_this.bind_event();
			_this.init_operate($(document));
			//refrash_iframe_height();
			_this.init_table($(document));
		});
	},
	//基础文件初始化
	_init_file: function () {
		var header = '';
		var now = new Date();
		//var ord = now.getFullYear().toString() + (now.getMonth() + 1).toString() + now.getDate().toString();
		var ord = now.getTime();
		// do not load init.js for unit test
		for (var i = 0; i < this.resources.length; i++) {
			if (typeof this.resources[i] != 'undefined') {
				header += '<script type="text/javascript" src="' + this.path + this.resources[i] + '?' + ord + '">' + '<\/script>'
			}
		}
		document.write(header);
	},
	//基础数据初始化
	_init_data: function () {
	},
	//列表初始化
	init_table: function($obj){
		var $table = $obj.find('.js_super_table');
		if (!$table.length) return false;
		$table.super_table();
	},
	//表单初始化
	init_form: function ($obj) {
		var $form = $obj.find('.js_super_form');
		if (!$form.length) return false;
		var layer_load = '';
		$form.Validform({
			ajaxPost: true,
			postonce: true,
			tiptype: function (msg, o, cssctl) {
				switch(o.type){
					case 3://验证失败
						layer.tips(msg, o.obj, {
							tips: 1
						});
					case 4://提示ignore状态
						;
				}
			},
			beforeSubmit:function(curform){
				//在验证成功后，表单提交前执行的函数，curform参数是当前表单对象。
				//这里明确return false的话表单将不会提交;
				layer_load = layer.load(1, {shade: 0.3,offset: $(top.document).scrollTop()});
				curform.ajaxSubmit({
					type:'post',
					dataType: 'jsonp',
					success: function(json_data){
						layer.close(layer_load);
						switch(json_data.code){
							//请求失败
							case undefined:
								layer.msg(json_data.msg, {icon: 5,offset: $(top.document).scrollTop()});
								break;
							//提交成功
							case 1:
								layer.msg('提交成功，请等待页面刷新', {icon: 6,offset: $(top.document).scrollTop(), time: 2000});
                                setTimeout(function(){
                                    location.href = location.href;
                                }, 2000);
								break;
							//提交失败
							default:
								layer.msg(json_data.msg || '提交失败', {icon: 5,offset: $(top.document).scrollTop()});
						}
					},
					error: function(e){
						layer.close(layer_load);
						layer.msg('请求失败', {icon: 5,offset: $(top.document).scrollTop()});
					}
				});
				return false;
			}
		});
	},
	//checkbox初始化
	init_checkbox: function($obj){
		var $checkbox = $obj.find('input[type="checkbox"]');
		if (!$checkbox.length) return false;
		$checkbox.iCheck({
			checkboxClass: 'icheckbox-blue',
			increaseArea: '20%'
		});
	},
	//radio初始化
	init_radio: function($obj){
		var $radio = $obj.find('input[type="radio"]');
		if (!$radio.length) return false;
		$radio.iCheck({
			radioClass: 'iradio-blue',
			increaseArea: '20%'
		});
	},
	//基础AJAX配置
	base_ajax: function (set) {
		var layer_load = layer.load(1, {shade: 0.3,offset: $(top.document).scrollTop()});
		$.ajax({
			url: set.url || location.href,
			type: set.type || 'get',
			dataType: set.dataType || 'jsonp',
			data:set.data || {},
			success: set.success || function (json_data) {
				layer.close(layer_load);
				if (json_data.code != 1) {
					return layer.msg(json_data.msg, {icon: 5,offset: $(top.document).scrollTop()});
				}
				if (typeof set.after_success == 'function') {
					set.after_success(json_data);
				}
			},
			error: function (e) {
				layer.close(layer_load);
				layer.msg('请求失败', {icon: 5,offset: $(top.document).scrollTop()});
			}
		});
	},
	//取窗口滚动条高度
	get_scroll_top: function()
	{
		var scrollTop=0;
		if(top.document.documentElement&&top.document.documentElement.scrollTop)
		{
			scrollTop=top.document.documentElement.scrollTop;
		}
		else if(top.document.body)
		{
			scrollTop=top.document.body.scrollTop;
		}
		return scrollTop;
	},
	//取窗口可视范围的高度
	get_client_height: function()
	{
		var clientHeight=0;
		if(document.body.clientHeight&&document.documentElement.clientHeight)
		{
			var clientHeight = (document.body.clientHeight<document.documentElement.clientHeight)?document.body.clientHeight:document.documentElement.clientHeight;
		}
		else
		{
			var clientHeight = (document.body.clientHeight>document.documentElement.clientHeight)?document.body.clientHeight:document.documentElement.clientHeight;
		}
		return clientHeight;
	},
	//基础modal配置
	base_layer_model: function (set) {
		layer.open({
			type: 1, title: set.title || '信息窗',
			shadeClose: true,
			offset: Math.max($(top.document).scrollTop()-100,0),
			scrollbar: false,
			area: '1000px', //宽度
			content: set.content, //iframe的url
			success: function (layero, index) {//层弹出后的成功回调方法
				super_include.init_form(layero);
				super_include.init_checkbox(layero);
				super_include.init_radio(layero);
				layero.find('.js_close_layer').on('click',function(){
					layer.close(index);
				});
				refrash_iframe_height();
			}
		});
	},
	//基础modal配置
	base_layer_confirm: function (set) {
		layer.confirm('确认吗？',{
			title: set.title || '确认窗',
			offset: $(top.document).scrollTop(),
			scrollbar: false,
			icon:3,
			content: set.content || '确认' + set.title + '吗？',
		},function(index) {
			layer.close(index);
			set.ok();
		});
	},
	init_operate: function($obj){
		var _this = this;
		$obj.find('.js_operate').each(function(i_1,o_1){
			var $o_1 = $(o_1),event = $o_1.data('event') || 'click';
			//遍历绑定触发事件
			$o_1.off(event).on(event,function(){
				if(event == 'blur' && $o_1.data('default') != undefined && $o_1.data('default') == $o_1.val()){
					return false;
				}
				//直接传递id或者一个选择器
				var id = $o_1.data('id') || function(){
						var ids = [];
						$($o_1.data('ids')).each(function(i,o){
							//过滤空值
							if($(o).val()){
								ids.push($(o).val());
							}
						});
						return ids.join(',');
					}();
				var $o_2 = $(this);
				//ajax提交函数
				var submit = function(){
					super_include.base_ajax({
						url: $o_1.data('url'),
						data:{
							id: id,
							value: $o_2.val()
						},
						after_success: function(json_data){
							switch($o_1.data('callback')){
								case 'layer_model':
									super_include.base_layer_model({
										obj: $o_1, title: $o_1.data('title'), content: json_data.data
									});
									break;
								default:
									layer.msg('提交成功，请等待页面刷新', {icon: 6,offset: $(top.document).scrollTop(), time: 2000});

                                    setTimeout(function(){
                                    	location.href = location.href;
                                    	}, 2000);
							}
						}
					});
				};
				//确认操作
				if($o_1.data('confirm') || $o_1.data('callback') == 'layer_confirm'){
					//确认操作必须有id
					if(!id){
						return layer.msg('请选择数据', {
							icon: 5,
							offset: $(top.document).scrollTop()
						});
					}
					_this.base_layer_confirm({
						title: $o_1.data('title'),
						content: $o_1.data('content'),
						url:$o_1.data('url'),
						ok:submit
					});
				}else{
					submit();
				};
				return false;
			});
		})


	},
	//绑定事件
	bind_event: function () {
	}
};
layer.config({
	//offset: '200px',
	zIndex: '100',
});
super_include._init();

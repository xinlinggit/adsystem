$.super_table = function (options, element) {
	options = options || {};
	$.extend(true, options, {
		element: $(element)
	});
	this.init(options);
	return this;
};
$.super_table.defaults = {
	element: {},
	obj:{
		container: '.js_super_table',
		pagebrowse: '.pagination .tx-pagebrowse li a',
		sort_link: 'th[data-sort]',
		add_row: '.add_tr',
		btn_delete_row: '.btn_delete_row',
		btn_add_row: '.btn_add_row',
		st_orderby: '#st_orderby',
		model_link: '.l_view',
	},
	opt:{
		current_page: 1,//当前页码
		per_page: 20,//每页数量
		total: 0,//总数据量
		last_page: 1,//最后页码
		page_length: 10,//分页页码数
		point_left : 0,
		point_right : 0,
		activeTd: 0,
		calculate: 0,//0: null; 4: all; 2:only out_move 3:in_move and out_move up-down;
		orderBy: '',
		ascDesc: '',
		page_key: 'page',
		page_num: '1',
		ajax_url: location.href,
	},
	after_refresh: function(){},
	get_tbody: function(list){},
};
$.super_table.prototype = {
	init: function(options){
		var opts = this.options = $.extend(true, {}, $.super_table.defaults, options);
		this.bind_event();
		//this.ajax_table();
	},
	get_opt: function(key){
		//var _this = this;
		return this.options.opt[key];
	},
	set_opt: function(key,value){
		this.options.opt[key] = value;
	},
	update : function (key) {
		if ($.isPlainObject(key)) {
			this.options = $.extend(true, this.options, key);
		}
	},
	refresh: function (data){
		var _this = this;

		_this.set_opt('current_page',data.current_page * 1);
		_this.set_opt('per_page',data.per_page * 1);
		_this.set_opt('total',data.total * 1);
		_this.set_opt('last_page',data.last_page * 1);
		_this.options.element.find('tbody').html(_this.get_tbody(data.data));
		_this.options.element.find('.dataTables_info').html('共有<strong class="red">' + data.total * 1 + '</strong>条数据；' + _this.get_select());
		_this.options.element.find('.dataTables_paginate').html(_this.format_page());

		$('.js_change_num').on('change',function(){
			location.href = _this.replace_url('num',$(this).val());
		});
		_this.table_check();
		_this.options.element.find('.edit_model').on('click',function(){
			var _this = $(this);
			$.ajax({
				url:_this.data('url'),
				type: 'get',
				dataType: 'json',
				success:function(html_data){
					if(html_data){
						$('#edit_form').html(html_data.data);
						Dialog('edit_form');
					}
				},
				error: function(html_data){
					//window.location.href=window.location.href;
				},
				beforeSend: function () {
					//$('#loading').show();
				}
			});
			return false;
		});
		_this.options.after_refresh();
	},
	get_tbody:function(list){
		var _this = this;
		var tr = _this.options.element.find('.js_tr_demo').clone().removeClass('js_tr_demo').show(),html='',tbody='';
		var reg=new RegExp("%%([^%]*)%%|%25%25([^%]*)%25%25","g");
		if(tr.length){
			html = tr.prop('outerHTML');
			$.each(list, function (i_1, o_1) {
				o_1.even = i_1 % 2 ? 'Even' : ' ';
				tbody += html.replace(reg,function($1,$2,$3){
					if(o_1[$2] === undefined){
						if(o_1[$3] === undefined){
							return '';
						}
						return o_1[$3];
					}
					return o_1[$2];
				});
			});
		}
		return tbody;
	},
	format_page: function(){
		var _this = this, _opt = _this.options.opt, length = _opt.page_length,html = '';
		html += '<a class="paginate_button" href="' + _this.replace_url('page',1) + '">首页</a>';
		html += '<a class="paginate_button" href="' + _this.replace_url('page',Math.max(1,_opt.current_page - 1)) + '">上一页</a>';
		for(var i=Math.max(1,_opt.current_page-length/2);i<=Math.min(_opt.current_page+length/2,_opt.last_page);i++){

			html += '<a href="' + _this.replace_url('page',i) + '" class="paginate_button ' + ((_opt.current_page == i) ? 'current' : '') + '">' + i + '</a>';
		}
		html += '<a class="paginate_button" href="' + _this.replace_url('page',Math.min(_opt.last_page,_opt.current_page + 1)) + '">下一页</a>';
		html += '<a class="paginate_button" href="' + _this.replace_url('page',_opt.last_page) + '">尾页</a>';
		html += '';
		return html;
	},
	get_select: function(){
		var _this = this, html = '每页显示：<select class="js_change_num" >';
		for(var i=20;i<=100;i+=20){
			html += '<option ' + ((_this.get_opt('per_page')== i) ? 'selected="selected"' : '' ) + ' value="' + i + '">' + i + '</option>';
		};
		html += '</select>';
		return html;
	},
	replace_url: function(key,value){
		return set_url_param(location.href,key,value);

	},
	//全选功能
	event_check: function () {
		var $checkbox = this.options.element.find(".js_checkbox");
		if(!$checkbox.length) return false;
		var $parent = $checkbox.filter('[data-type="parent"]');
		var $child = $checkbox.filter('[data-type="child"]');
		$parent.on('click',function(){
			if ($(this).prop('checked')) {
				$child.prop('checked', 'checked');
				$child.closest('tr').addClass('success');
			} else {
				$child.prop('checked', false);
				$child.closest('tr').removeClass('success');
			}
		});
		$child.on('click',function () {
			if ($child.filter(':checked').length == $child.length) {
				$parent.prop('checked', 'checked');
			} else {
				$parent.prop('checked', false);
			}
			var $tr = $(this).closest('tr');
			if($tr.hasClass('success')){
				$tr.removeClass('success');
			}else{
				$tr.addClass('success');
			}
		});
		$child.closest('td').siblings().not(':has(a)').off('click').on('click', function () {
			$(this).parent().find('.js_checkbox').click();
		});
	},
	//折叠功能
	event_fold: function(){
		var $tr = this.options.element.find('.js_fold_tr');
		if(!$tr.length) return false;
		var fold = {
			//折叠
			fold: function($obj){
				if(!$obj.data('id')) return false;
				var $child = $obj.siblings('tr').filter('[data-pid=' + $obj.data('id') + ']');
				if(!$child.length) return false;
				$obj.data('fold',1);
				$obj.find('.Hui-iconfont-jianhao').removeClass('Hui-iconfont-jianhao').addClass('Hui-iconfont-add');
				$child.hide(1000);
				if(!$child) return false;
				//循环折叠子级
				$child.each(function(i,o){
					fold.fold($(o));
				})
			},
			//展开
			unfold: function($obj){
				if(!$obj.data('id')) return false;
				var $child = $obj.siblings('tr').filter('[data-pid=' + $obj.data('id') + ']');
				if(!$child.length) return false;
				$obj.data('fold',0);
				$obj.find('.Hui-iconfont-add').removeClass('Hui-iconfont-add').addClass('Hui-iconfont-jianhao');
				$child.show(1000);
			},
		}
		$tr.on('click', function () {
			var $this = $(this);
			if ($this.data('fold')) {
				fold.unfold($this);
			} else {
				fold.fold($this);
			}
			//调整IFRAME高度
			refrash_iframe_height();
			return false;
		});
	},
	//排序功能
	event_sorting: function(){
		var $th = this.options.element.find('.js_th_sorting');
		if(!$th.length) return false;
		$th.on('click',function(){
			var $this = $(this);
			if(!$this.data('url')){
				return false;
			}
			var layer_load = layer.load(1, {shade: 0.3});
			location.href = $this.data('url');
		});
	},
	//调整每页条数功能
	event_change_num: function(){
		var _this = this,$num = this.options.element.find('.js_change_num');
		if(!$num.length) return false;
		$num.on('change',function(){
			var $this = $(this);
			var layer_load = layer.load(1, {shade: 0.3});
			location.href = _this.replace_url('num',$this.val());
		});
	},
	bind_event: function(){
		this.event_check();
		this.event_fold();
		this.event_sorting();
		this.event_change_num();
	},
	ajax: function($obj){
		var super_table = this;
		super_include.base_ajax({
			obj: $obj,
			url: $obj.data('url') || location.href,
			data:{
				id: $obj.data('id') || super_table.get_checked(),
				value: $obj.val(),
				operate: $obj.data('operate') || 'table_update'
			},
			after_success: function (json_data) {
				super_include.base_layer_model({
					obj: $obj, title: $obj.data('title'), content: json_data.data
				});
			}
		})
	},
	get_checked: function(){
		var id = [];
		this.options.elements.find('.js_checkbox:checked').each(function(i,o){
			id.push($(o).val());
		});
		return id.join(',');
	}

};
$.fn.super_table = function (options) {
	this.each(function () {
		var instance = $(this).data('super_table');
		if (instance) {
			instance.update(options);
		} else {
			$(this).data('super_table', new $.super_table(options, this));
		}
	});
	return this;
};
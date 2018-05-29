$.super_select2 = function (options, element) {
	options = options || {};
	var opt = {};
	opt.element = $(element);
	var key = $(element).attr('data-select2-key');
	opt.key = key;
	opt.parent = $(element).attr('data-select2-parent');
	opt.parm_set = '&ajax_select2=set_' + key;
	opt.parm_get = '&ajax_select2=get_' + key;
	if($(element).attr('data-select2-limit')){
		opt.limit = $(element).attr('data-select2-limit');
	}
	this.init($.extend(true,options,opt));
	return this;
};
$.super_select2.defaults = {
	element: '.super_select2',
	parent: '',
	child: '',
	key: '',
	parm_get: '',
	parm_set: '',
	limit: 3
};
$.super_select2.prototype = {
	init: function(options){
		var options = this.options = $.extend(true, {}, $.super_select2.defaults, options);
		this.bind_select2(options.element);
	},
	FormatResult: function (data) {
		var markup = "<table class='movie-result'><tr>";
		markup += '<td valign="top"><div>' + data.value + '</div>';
		markup += "</td></tr></table>"
		return markup;
	},
	FormatSelection: function (data) {
		return data.value;
	},
	update : function (key) {
		if ($.isPlainObject(key)) {
			$.extend(true, this, key);
		}
	},
	bind_select2: function (obj) {
		var _this = this;
		var p = obj.parent('.controls');
		obj.select2({
			allowClear: true,
			minimumInputLength: _this.options.limit,
			ajax: {
				url: location.href + _this.options.parm_get,
				dataType: 'json',
				quietMillis: 100,
				data: function (term, page) {
					return {
						q: term, //search term
						page_limit: 10, // page size
						page: page, // page number
						apikey: "ju6z9mjyajq2djue3gbvv26t"
					};
				},
				error: function (){
					window.location.href=window.location.href;
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: _this.FormatResult, // omitted for brevity, see the source of this page
			formatSelection: _this.FormatSelection, // omitted for brevity, see the source of this page
			initSelection: function (element, callback) {
				var id = obj.val();
				if (id !== "") {
					$.ajax(location.href + _this.options.parm_set + '&val=' + id, {
						data: {
							apikey: "ju6z9mjyajq2djue3gbvv26t"
						},
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			},
			escapeMarkup: function (data) {
				var p = obj.closest(_this.options.parent);
				var child = 'data-select2-' + _this.options.key;
				var result = obj.select2("data");
				if(!data || !result){
					return data;
				}
				p.find('input[' + child + '],select[' + child + '],div[' + child + '],span[' + child + ']').each(function(i,o){
					var name = $(o).attr(child);
					if(eval('result.'+name) == undefined){
						return '';
					}
					var value = eval('result.'+name);
					switch($(o).attr('data-select2-change')){
						case 'html':
							$(o).html(value);
							break;
						default:
							$(o).val(value);
					}
				});
				if(typeof(_this.options.after_select) == "function") {
					_this.options.after_select(result);
				}
				return data;
			},
			formatInputTooShort: function (input, min) {
				var n = min - input.length;
				var content = search_info.replace("%x%", n);
				if(n !== 1){
					content += search_infos;
				}
				return content;
			},
			formatSearching: function () {
				return search_begin;
			},
			reset: function(){
				var p = obj.closest(_this.options.parent);
				var child = 'data-select2-' + _this.options.key;
				p.find('input[' + child + '],select[' + child + '],div[' + child + '],span[' + child + ']').each(function(i,o){
					switch($(o).attr('data-select2-change')){
						case 'html':
							$(o).html('');
							break;
						default:
							$(o).val('');
					}
				});
				if(typeof(_this.options.after_reset) == "function") {
					_this.options.after_reset();
				}
			},
			dropdownCssClass: "bigdrop"
		});
	}
}
$.fn.super_select2 = function (options) {
	this.each(function () {
		var instance = $(this).data('super_select2');
		if (instance) {
			instance.update(options);
		} else {
			$(this).data('super_select2', new $.super_select2(options, this));
		}
	});
	return this;
};
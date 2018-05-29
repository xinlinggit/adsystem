//获取URL参数
function get_url_param(name) {
	var sValue = location.search.match(new RegExp("[\?\&]" + name + "=([^\&]*)(\&?)", "i"));
	return sValue ? sValue[1] : sValue;
}
//设置URL参数
function set_url_param(url,name,value){
	var r = url;
	if (r != null && r != 'undefined' && r != "") {
		value = encodeURIComponent(value);
		var reg = new RegExp("(^|)" + name + "=([^&]*)(|$)");
		var tmp = name + "=" + value;
		if (url.match(reg) != null) {
			r = url.replace(eval(reg), tmp);
		}
		else {
			if (url.match("[\?]")) {
				r = url + "&" + tmp;
			} else {
				r = url + "?" + tmp;
			}
		}
	}
	return r;

}
//刷新IFRAME高度
function refrash_iframe_height(iframe){
	var iframe = iframe || parent.document.getElementsByTagName('iframe')[0];
	try{
		iframe.style.height = $('body').height() + "px";
	}catch (ex){
	}
}


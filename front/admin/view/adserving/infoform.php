<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<style>
	.layui-form-pane .layui-form-label {width:200px;}
    .info_material_choosed {
        display: inline-block;
        width:200px;
        height:1em;
    }
    .red {
        color:red;
    }
</style>
<style>
</style>
<form class="layui-form layui-form-pane" action="{$action}" method="post" id="editForm" enctype="multipart/form-data" onsubmit="checkPriceLimit()">
    基本信息
    <hr class="layui-bg-green">
	<div class="layui-tab-item layui-show layui-form-pane">
		<div class="layui-form-item">
			<label class="layui-form-label layui-form-text">广告名称:</label>
			<div class="layui-input-inline">
				<input type="text" class="layui-input field-title" name="title" lay-verify="required" autocomplete="off" placeholder="请输入广告名称">
			</div>
		</div>
        <div class="layui-form-item">
            <label class="layui-form-label">投放形式:</label>
            <div class="">
                <input type="radio" class="field-project_type" lay-filter="project_type" name="project_type" value="3" title="图文" checked />
            </div>
        </div>
		<div class="layui-form-item">
			<label class="layui-form-label">选择站点:</label>
			<div class="layui-input-inline">
				<select name="adsiteid" class="field-adsiteid" lay-verify = "required" type="select" lay-filter="adsiteid" id="info_adsiteid" {if condition="$edit eq 1"}disabled{/if}>
                    <option></option>
					{volist name="adsite" id = "site"}
						<option value="{$site['id']}">{$site.sitename}</option>
					{/volist}
				</select>
			</div>
		</div>
        <div class="layui-form-item">
            <label class="layui-form-label">投放模式:</label>
            <input type="radio" lay-filter="bidding_rpm" id = "bidding_rpm" class="field-spending" title="竞价" name="spending" value="2" checked />
        </div>
        <div id="rpm_detail">
            <div class="layui-form-item">
                <label class="layui-form-label">竞价出价:</label>
                <div class="layui-input-inline">
                    <input type="number" lay-filter="price"  lay-verify="required|NoLessThanPositiveInt" data-pricelimit = '10' id="price" onblur="checkPriceLimit()" class="layui-input field-price" name="price" placeholder="最低竞价 10 元" value="" />
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">CPM 单次消耗金额上限:</label>
                <div class="layui-input-inline">
                    <input type="number" lay-filter = "pricelimit" lay-verify="required|positiveInt" onblur="checkPriceLimit()" id="pricelimit" class="layui-input field-pricelimit" name="pricelimit" placeholder="只能是竞价价格的倍数" value="" />
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="">
                <input type="text" lay-verify="required"  name="time[t1][btime]" id="serving_time_begin_1" class="my_input my_c field-btime" placeholder="投放开始时间" readonly /> -
                <input type="text"  lay-verify="required"  name="time[t1][etime]" id="serving_time_end_1" class="my_input my_c field-etime" placeholder="投放结束时间" readonly />
            </div>
        </div>
        <br />
        可选素材尺寸( 点击尺寸添加 )
        <hr class="layui-bg-green">
        <div id="size_optional" style="overflow: hidden;">

        </div>
        <br />

        已选素材
        <hr class="layui-bg-green">
        <div id="material_list">
        </div>

        <div class="layui-form-item">
            <input type="hidden" name="adsenseid" class="field-adsenseid" value="0" />
            {if condition="$edit eq 1"}
            <input type="hidden" class="field-adsiteid" name="adsiteid" value="{$data_info['adsiteid']}" />
            <input type="hidden" id="choosed_material_data" value="{$choosed_material_data}" />
            <input type="hidden" name="id" class="field-id" />
            <input type="hidden" name="adv_type" value="{$data_info['adv_type']}" />
            {/if}
            <input type="hidden" name="spending" id="" value="2"/>
            <input type="hidden" name="materialid" id="materialid" class=" field-materialid" value="" lay-verify =  "materialIdNotEmpty" />
            <input type="submit" class="layui-btn" lay-submit="" value="开始投放" />
            <input type="reset" class="layui-btn layui-btn-primary" value="重置" />
            <a href="{:url('admin/adserving/lists')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回列表页</a>
        </div>
	</div>
</form>
{include file="block/layui" /}
<script>
    // 获取钱包余额
    function checkBlance(price, obj)
    {
        var $ = layui.jquery;
        var blance = 0;
        $.ajax({
            method: "POST",
            url: "{:url('admin/adserving/get_blance')}",
            data:{"price":price}
        }).done(function(data){
            eval("var data = " + data + "");
            if(data.result)
            {
                layer.msg('余额不足', {icon: 5});
            }
        })
        return blance;
    }

    function del(obj, id)
    {
        obj.parentNode.remove();
        pop(id);
    }

    /**
     * 弹出元素
     */
    function pop(id){
        var $ = layui.jquery;
        var str_mid = "";
        var mid = $("#materialid").val();
        var mid_arr = mid.split("|");
        mid_arr.splice($.inArray(id, mid_arr), 1);
        str_mid = mid_arr.join("|");
        $("#materialid").val(str_mid);
    }

     /*
      * 插入元素
      */
    function shift(id){
        var $ = layui.jquery;
        var str_mid = "";
        var mid = $("#materialid").val();
        if(mid == "")
        {
            str_mid += id;
        } else {

            str_mid = mid + "|" + id;
        }
        $("#materialid").val(str_mid);
    }

    /*
    素材添加回调函数
    */
    function func(data) {
        var $ = layui.jquery;
        $("#material_list").hide();
        add_choosed_material_list(data.data.id, data.data.material_title);
        $("#material_list").show();
        shift(data.data.id);
        if(data.code !== 1)
        {
            layer.msg('添加失败', {icon:5});
        }
    }

    /**
     * 素材编辑回调函数
     */
    function update_func(data)
    {
        var $ = layui.jquery;
        $("#id" + data.data.id).remove();
        pop(data.data.id);
        func(data);
    }

    function add_choosed_material_list(id, title)
    {
        var $ = layui.jquery;
        var div = $('<div id="id'+ id +'">');
        var span = $('<span class="info_material_choosed" >').text(title);
        var a = $("<a class='red' onclick=\"del(this, "+ id +")\">").text('删除');
        var preview = $("<a target='_blank' href=\"http://as.cnfol.com/index/index/preview"+ '?material_id=' + id +"\">" ).text('预览');
        var modify_btn = $("<a href=\"{:url('admin/admaterial4info/pop_edit?callback=update_func')}"+ '?material_id=' + id +"\" class=\" j-iframe-pop\">" ).text('修改');
        var br = $("<br />");
        $("#material_list").append(div.append(span).append(preview).append(modify_btn).append(a).append(br));
    }

    function load_choosed_material()
    {
        var $ = layui.jquery;
        var mid = $("#materialid").val();
        var choosed_material_data = $("#choosed_material_data").val();
        var choosed_obj = new Array();
        var choosed_arr = choosed_material_data.split("|")
        $("#material_list").hide();
        $.each(choosed_arr, function(k, v){
            var arr = v.split(",");
            add_choosed_material_list(arr[0], arr[1]);
        });
        $("#material_list").show();
    }

    // 加载所有广告位对应的尺寸
    function infoLoadAdPositionSize()
    {
        var $ = layui.jquery, form = layui.form;
        var adsite = $("#info_adsiteid").val();
        $('#size_optional').html("");
        $.ajax({
            method: "POST",
            url: "{:url('admin/adserving/getAdPositionSize')}",
            data: {adsiteid: adsite}
        }).done(function(data){
            var data = JSON.parse(data);
            var obj_size_optional = document.getElementById("size_optional");
            if (!obj_size_optional)
            {
                return false;
            }
            obj_size_optional.style.display = 'none';
            $.each(data, function(i, item){
                var span = $("<a href=\"{:url('admin/admaterial4info/pop?callback=func')}"+ '?width=' + item.width +'&height=' + item.height + '&adsite=' + adsite +"\" class=\"layui-btn layui-btn-primary j-iframe-pop fl\" >").text(item.width + '*' + item.height);
                $('#size_optional').append(span);
                $('#size_optional').append("&nbsp;&nbsp;");
            })
            document.getElementById("size_optional").style.display = 'block';
        })
    }

    function checkPrice()
    {
        var $ = layui.jquery;
        var ele_price = $("#price");
        var price = parseInt($("#price").val());
        // checkBlance(price, $("#price"));
        var NoLessThan = ele_price.attr('data-pricelimit');
        NoLessThan = parseInt(NoLessThan);
        if(price < NoLessThan)
        {
            layer.msg('价格不能低于' + NoLessThan + '元', {icon: 5});
            ele_price.focus();
        }
    }

    function copyPrice2Pricelimit()
    {
        checkPrice();
        var $ = layui.jquery;
        var ele_price = $("#price");
        var price = ele_price.val();
        var pricelimit = $("#pricelimit").val();
        $("#pricelimit").val(price);
    }

    /**
     * TODO: 检查是否有匹配当前条件的素材，否则提示
     */
    function check_has_materials()
    {
        
    }

    // 只有的在竞价模式才判断价格
    function check_rpm_model()
    {
        var $ = layui.jquery;
        var rpm_model = $("input[name = 'spending']:checked").val();
        return rpm_model;
    }

    function checkPriceLimit()
    {
        if(check_rpm_model() == 2) {
            var $ = layui.jquery;
            var price = $("#price").val();
            price = parseInt(price);
            var e_pricelimit = $("#pricelimit");
            var pricelimit = e_pricelimit.val();
            pricelimit = parseInt(pricelimit);
            if(((!pricelimit) || (pricelimit < price) && pricelimit > 10))
            {
                copyPrice2Pricelimit();
            }

            // checkBlance(pricelimit, "#pricelimit");
            if (pricelimit) {
                if ((pricelimit % price) !== 0) {
                    layer.msg('竞价价格不能为空,必须是单价的倍数', {icon: 5});
                    // alert('竞价价格不能为空,必须是单价的倍数');
                    e_pricelimit.val("");
                    e_pricelimit.focus();
                    return false;
                }
            }
        }
    }
    layui.use('laydate', function(){
        // TODO: 结束时间需要大于开始时间，多段时间段不能交叉
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#serving_time_begin_1' //指定元素
            ,type:'datetime'
            ,min: 'now'
        });
        //执行一个laydate实例
        laydate.render({
            elem: '#serving_time_end_1' //指定元素
            ,type:'datetime'
        });
    });

    function check_required_field(flag)
    {
        var $ = layui.jquery;
        if(flag == 'on')
        {
            $("#price").attr("lay-verify","required");
            $("#pricelimit").attr("lay-verify","required");
            $("#priority").attr("lay-verify","required");
        } else if(flag == 'off')
        {
            $("#price").attr("lay-verify","");
            $("#pricelimit").attr("lay-verify","");
            $("#priority").attr("lay-verify","");
        }
        $("#serving_time_begin_1").attr("lay-verify","required");
        $("#serving_time_end_1").attr("lay-verify","required");
    }

    layui.use(['form'], function() {
        var $ = layui.jquery, form = layui.form;
        var couplet_form = document.getElementById('couplet_form');
        form.on('select(adsiteid)', function(data) {
            infoLoadAdPositionSize();
        });

        infoLoadAdPositionSize();

        {if condition="$edit eq 1"}
        load_choosed_material();
        {/if}

        form.verify({
            positiveInt: function(val, item){
                if(check_rpm_model() == 2) {
                    // /^[1-9]\d*$/
                    if (!new RegExp("^[1-9]\\d*$").test(val)) {
                        return '请输入合法的金额';
                    }
                }
            },
            NoLessThanPositiveInt: function(val, item)
            {
                if(check_rpm_model() == 2)
                {
                    var pricelimit = $(item).attr('data-pricelimit');
                    if(parseInt(val) < parseInt(pricelimit))
                    {
                        return '价格不能低于' + pricelimit + '元';
                    }
                }
            }
            ,
            materialIdNotEmpty: function(val, item)
            {
                if(check_rpm_model() == 2)
                {
                    var mid = $(item).val();
                    if(!mid)
                    {
                        return '请上传素材';
                    }
                }
            }
        })
    });
</script>
<script>
    var formData = {:json_encode($data_info)};
    layui.use(['jquery'], function() {
        var $ = layui.jquery, input = '',form = layui.form;

        /*iframe弹窗 - 动态绑定*/
        $("#size_optional").on('click', '.j-iframe-pop', function(){
            var that = $(this),
                _url = that.attr('href'),
                _title = '添加素材',
                _width = that.attr('width') ? that.attr('width') : 750,
                _height = that.attr('height') ? that.attr('height') : 500;
            if (!_url) {
                layer.msg('请设置href参数');
                return false;
            }
            layer.open({type:2, title:_title, content:_url, area: [_width+'px', _height+'px']});
            return false;
        });
        /*iframe弹窗 - 动态绑定*/
        $("#material_list").on('click', '.j-iframe-pop', function(){
            var that = $(this),
                _url = that.attr('href'),
                _title = '编辑素材',
                _width = that.attr('width') ? that.attr('width') : 750,
                _height = that.attr('height') ? that.attr('height') : 500;
            if (!_url) {
                layer.msg('请设置href参数');
                return false;
            }
            layer.open({type:2, title:_title, content:_url, area: [_width+'px', _height+'px']});
            return false;
        });
        /* 修改模式下表单自动赋值 */
        if (formData) {
            for (var i in formData) {
                switch($('.field-'+i).attr('type')) {
                    case 'select':
                        input = $('.field-'+i).find('option[value="'+formData[i]+'"]');
                        input.prop("selected", true);
                        break;
                    case 'radio':
                        input = $('.field-'+i+'[value="'+formData[i]+'"]');
                        input.prop('checked', true);
                        break;
                    case 'checkbox':
                        for(var j in formData[i]) {
                            input = $('.field-'+i+'[value="'+formData[i][j]+'"]');
                            input.prop('checked', true);
                        }
                        break;
                    case 'img':
                        input = $('.field-'+i);
                        input.attr('src', formData[i]);
                    default:
                        input = $('.field-'+i);
                        input.val(formData[i]);
                        break;
                }
                if (input.attr('data-disabled')) {
                    input.prop('disabled', true);
                }
                if (input.attr('data-readonly')) {
                    input.prop('readonly', true);
                }
            }
        }
    });
</script>
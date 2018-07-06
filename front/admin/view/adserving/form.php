<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<style>
	.layui-form-pane .layui-form-label {width:200px;}
</style>
<form class="layui-form layui-form-pane" action="{:url('admin/Adserving/getMaterialList')}" method="get" id="editForm" enctype="multipart/form-data" onsubmit="checkPriceLimit()">
	<div class="layui-tab-item layui-show layui-form-pane">
		<div class="layui-form-item">
			<label class="layui-form-label layui-form-text">广告名称:</label>
			<div class="layui-input-inline">
				<input type="text" class="layui-input field-title" name="title" lay-verify="required" autocomplete="off" placeholder="请输入广告名称">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">选择站点:</label>
			<div class="layui-input-inline">
				<select name="adsiteid" class="field-adsiteid" lay-verify = "required" type="select" lay-filter="adsiteid" id="adsiteid" {if condition="$edit eq 1"}disabled{/if}>
                    <option></option>
					{volist name="adsite" id = "site"}
						<option value="{$site['id']}">{$site.sitename}</option>
					{/volist}
				</select>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">选择广告位:</label>
			<div class="layui-input-inline">
				<select name="adsenseid" class="field-adsenseid" class="" type="select" lay-filter="adsenseid" id="adsenseid" lay-verify="required" {if condition="$edit eq 1"}disabled{/if}>
					<option value=""></option>
				</select>
			</div>
		</div>
        <div class="layui-form-item">
            <label class="layui-form-label">投放模式:</label>
            {if condition="$role_id neq 4 /*普通用户不可见*/"}
            <input type="radio" lay-filter="bidding_time" id = "bidding_time" class="field-spending" title="包时段" name="spending" value="1" {if condition="$edit eq 1"} disabled {/if} />
            {/if}
            <input type="radio" lay-filter="bidding_rpm" id = "bidding_rpm" class="field-spending" title="竞价" name="spending" value="2"  {if condition="$edit eq 1"} disabled {/if}/>
        </div>
        <!-- 选中竞价 radio 后显示与隐藏  begin -->
        <div id="rpm_detail" {if condition="($edit eq 0) OR $data_info AND $data_info.spending eq 1"} style="display:none;" {else/} style="display:block;" {/if}>
            <div class="layui-form-item">
                <label class="layui-form-label">竞价出价:</label>
                <div class="layui-input-inline">
                    <input type="number" lay-filter="price"  lay-verify="required|NoLessThanPositiveInt" data-pricelimit = '10' id="price" onblur="checkPriceLimit()" class="layui-input field-price" name="price" placeholder="该广告位最低竞价 10 元" value="" />
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">CPM 单次消耗金额上限:</label>
                <div class="layui-input-inline">
                    <input type="number" lay-filter = "pricelimit" lay-verify="required|positiveInt" onblur="checkPriceLimit()" id="pricelimit" class="layui-input field-pricelimit" name="pricelimit" placeholder="只能是竞价价格的倍数" value="" />
                </div>
            </div>
        </div>
        <!-- 选中竞价 radio 后显示与隐藏  end -->
        <!--选中包时段，显示与隐藏  begin-->
        <div class="layui-form-item" id="priority_detail" {if condition="($edit eq 0) OR $data_info AND $data_info.spending eq 2"} style="display:none;" {else/} style="display:block;" {/if}>
            <label class="layui-form-label">优先级:</label>
            <div class="layui-input-inline">
                <select name="price" class="field-price" class="" type="select" id="priority" lay-verify="required">
                    <option value="5">5</option>
                    <option value="4">4</option>
                    <option value="3">3</option>
                    <option value="2">2</option>
                    <option value="1">1</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="">
                <input type="text" lay-verify="required"  name="time[t1][btime]" id="serving_time_begin_1" class="my_input my_c field-btime" placeholder="投放开始时间" /> -
                <input type="text"  lay-verify="required"  name="time[t1][etime]" id="serving_time_end_1" class="my_input my_c field-etime" placeholder="投放结束时间" />
            </div>
        </div>
        <!--选中包时段，显示与隐藏  end-->
        <div class="layui-form-item">
            <label class="layui-form-label">广告定向:</label>
            <input type="radio" name="orientation" title="默认全地段" checked value = "1" />
        </div>
        <div class="layui-form-item">
            <!-- 如果是修改状态则将站点、广告位和投放模式的 disabled 的值提交-->
            {if condition="$edit eq 1"}
                <input type="hidden" name="adsiteid" value="{$data_info['adsiteid']}" />
                <input type="hidden" name="adsenseid" value="{$data_info['adsenseid']}" />
                <input type="hidden" name="spending" value="{$data_info['spending']}" />
            {else/}
                <input type="hidden" name="spending" id="my_spendding" />
            {/if}

            <input type="hidden" name="sensetype" class="field-sensetype" id="sensetype" />
            {if condition="isset($edit) && $edit eq 1"}
            <input type="hidden" name="materialid" value="{$data_info.materialid}" />
            <input type="hidden" name="adid" value="{$data_info.id}" />
            {/if}
            <input lay-filter = "nextStep" type="submit" class="layui-btn" lay-submit="" value="下一步" />
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
        });
        //执行一个laydate实例
        laydate.render({
            elem: '#serving_time_end_1' //指定元素
            ,type:'datetime'
        });
    });

    // 加载所有广告位
    function loadAdPosition()
    {
        var $ = layui.jquery, form = layui.form;
        var adsite = $("#adsiteid").val();
        $('#adsenseid option').html("");
        form.render('select');
        $.ajax({
            method: "POST",
            url: "{:url('admin/adserving/getAdPosition')}",
            data: {adsiteid: adsite}
        }).done(function(data){
            eval("var data=" + data + "");
            var options = '';
            $.each(data, function(i, item){
                var option = $("<option>").val(item.id).text(item.sensename);
                $('#adsenseid').append(option);
                form.render('select');
            })
        })
    }

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
            loadAdPosition();
        });
        form.on('select(adsenseid)', function(data) {
            // 根据广告位，自动选中不同的竞价方式
            var adsenseid = $("#adsenseid").val();
            $("#sensetype").val(adsenseid);
            if( ! adsenseid)
            {
                return;
            }
            $.ajax({
                method: "POST",
                url: "{:url('admin/adserving/sensemodel')}",
                data: {adsenseid: adsenseid}
            }).done(function(data){
                var bidding_time = $("#bidding_time");
                var bidding_rpm = $("#bidding_rpm");

                // 包时段
                if(data == 1)
                {
                    // 选中
                    bidding_time.prop('checked', true);

                    // 初始化
                    bidding_time.prop('disabled', false);
                    bidding_rpm.prop('disabled', false);

                    // 改变样式 - TODO:
                    bidding_time.prop('disabled', true);
                    bidding_rpm.prop('disabled', true);
                    form.render('radio');

                    var rpm_detail = document.getElementById("rpm_detail");
                    var priority_detail = document.getElementById("priority_detail");
                    rpm_detail.style.display = "none";
                    priority_detail.style.display = "block";

                    check_required_field('off')
                    $("#my_spendding").val(1);

                    // 同时应该禁用掉优先级
                    var priority = $("#priority");
                    priority.prop('disabled', false);
                }else if(data == 2)
                // 竞价
                {
                    // 选中
                    bidding_rpm.prop('checked', true);

                    // 初始化
                    bidding_time.prop('disabled', false);
                    bidding_rpm.prop('disabled', false);

                    bidding_rpm.prop('disabled', true);
                    bidding_time.prop('disabled', true);
                    form.render('radio');

                    var rpm_detail = document.getElementById("rpm_detail");
                    var priority_detail = document.getElementById("priority_detail");
                    rpm_detail.style.display = "block";
                    priority_detail.style.display = "none";

                    // 同时应该禁用掉优先级
                    var priority = $("#priority");
                    priority.prop('disabled', true);

                    check_required_field('on')
                    $("#my_spendding").val(2);
                }
            })
        });

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
        })
    });
</script>
<script>
    var formData = {:json_encode($data_info)};
    layui.use(['jquery'], function() {
        var $ = layui.jquery, input = '',form = layui.form;
        /* 修改模式下表单自动赋值 */
        if (formData) {
            for (var i in formData) {
                switch($('.field-'+i).attr('type')) {
                    case 'select':
                        if(i == 'adsenseid')
                        {
                            var options = '';
                            $.each(formData.adPositions, function(ii, item){
                                var option = $("<option>").val(item.id).text(item.sensename);
                                $('#adsenseid').append(option);
                                // form.render('select');
                            })
                        }
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
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
    #size_optional a {
        display:inline-block;
        width:105px;
        margin:3px 8px;
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
            <!--编辑模式，禁止切换投放形式-->
            <label class="layui-form-label">投放形式:</label>
            <div class="">
                <input type="radio" class="field-project_type" lay-filter="project_type3" name="project_type" value="3" title="图文" checked {if condition="isset($edit) && ($edit eq 1) && ($data_info.project_type neq 3)"} disabled {/if} />
                <input type="radio" class="field-project_type" lay-filter="project_type1" name="project_type" value="1" title="文字" {if condition="isset($edit) && ($edit eq 1) && ($data_info.project_type neq 1)"} disabled {/if} />
                <input type="radio" class="field-project_type" lay-filter="project_type2" name="project_type" value="2" title="图片"  {if condition="isset($edit) && ($edit eq 1) && ($data_info.project_type neq 2)"} disabled {/if}/>
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
            <label class="layui-form-label">出价形式:</label>
            <input type="radio" lay-filter="CPM" id = "CPM"  title="CPM" name="spending" value="2" {if condition="isset($edit) && ($edit eq 1) && ($data_info.spending neq 2)"} disabled {/if} {if condition="(isset($data_info.spending) AND $data_info.spending eq 2) OR ($edit eq 0)"} checked {/if} />
            <input type="radio" lay-filter="CPC" id = "CPC"  title="CPC" name="spending" value="3"  {if condition="isset($edit) && ($edit eq 1) && ($data_info.spending neq 3)"} disabled {/if} {if condition="isset($data_info.spending) AND $data_info.spending eq 3"} checked {/if}  />
        </div>
        <!-- cpm 的价格设置 -- begin -->
        <div id="cpm_detail">
            <div class="layui-form-item">
                <label class="layui-form-label">竞价出价:</label>
                <div class="layui-input-inline">
                    <input type="text"  lay-verify="required|NoLessThanPositiveInt" data-pricelimit = '{$baseprice}' id="price" onblur="checkPriceLimit()" class="layui-input field-price" name="price" placeholder="最低竞价 {$baseprice} 元" value="" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                    />
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">单次消耗金额上限:</label>
                <div class="layui-input-inline">
                    <input type="text" lay-verify="required|positiveInt" onblur="checkPriceLimit()" id="pricelimit" class="layui-input field-pricelimit" name="pricelimit" placeholder="只能是竞价价格的倍数" value="" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                    />
                </div>
            </div>
        </div>
        <!-- cpm 的价格设置 -- end -->
        <!-- cpc 的价格设置 -- begin -->
        <div id="cpc_detail">
            <div class="layui-form-item">
                <label class="layui-form-label">竞价出价:</label>
                <div class="layui-input-inline">
                    <input type="text" lay-verify="required|NoLessThanPositiveInt" data-pricelimit = '{$cpcprice}' id="price" onblur="checkPriceLimit()" class="layui-input field-price" name="price" placeholder="最低竞价 {$cpcprice} 元" value="" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                    />
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">出价上限:</label>
                <div class="layui-input-inline">
                    <input type="text" lay-verify="required|positiveInt" onblur="checkPriceLimit()" id="pricelimit" class="layui-input field-pricelimit" name="pricelimit" placeholder="只能是竞价价格的倍数" value="" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                    />
                </div>
            </div>
        </div>
        <!-- cpc 的价格设置 -- end -->
        <div class="layui-form-item">
            <div class="">
                <input type="text" lay-verify="required"  name="time[t1][btime]" id="serving_time_begin_1" class="my_input my_c field-btime" placeholder="投放开始时间" readonly /> -
                <input type="text"  lay-verify="required"  name="time[t1][etime]" id="serving_time_end_1" class="my_input my_c field-etime" placeholder="投放结束时间" readonly />
            </div>
        </div>
        <br />
        可选素材尺寸( 点击尺寸添加 )
        <hr class="layui-bg-green">
        <!--文字-->
        <button class="layui-btn layui-btn-primary j-iframe-pop" id="info_text_btn" style="display: none;" href="{:url('admin/admaterial4info/text_pop?callback=func')}">新增</button>
        <!--信息流-->
        <div id="size_optional" style="overflow: hidden;">
        </div>
    </div>
    <br />
    已选素材
    <hr class="layui-bg-green">
    <div id="material_list" style="display: block">
    </div>

    <div class="layui-form-item">
        <input type="hidden" name="adsenseid" class="field-adsenseid" value="0" />
        {if condition="$edit eq 1"}
        <input type="hidden" class="field-adsiteid" name="adsiteid" value="{$data_info['adsiteid']}" />
        <input type="hidden" id="choosed_material_data" value="{$choosed_material_data}" />
        <input type="hidden" name="id" class="field-id" />
        <input type="hidden" name="adv_type" value="{$data_info['adv_type']}" />
        {/if}
        <input type="hidden" name="materialid" id="materialid" class=" field-materialid" value="" lay-verify =  "materialIdNotEmpty" />
        <!--信息流投放按钮-->
        <input type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" value="开始投放" />
        <input type="reset" class="layui-btn layui-btn-primary" value="重置" />
        <a href="{:url('admin/adserving/lists')}" class="layui-btn layui-btn-primary ml10"><i class="aicon ai-fanhui"></i>返回列表页</a>
    </div>
    </div>
</form>
{include file="block/layui" /}
<script>
    // 设置表单一些元素的初始状态
    function reset_them(){
        var $ = layui.jquery;
        var spending_status = get_spending_status();
        if(({$edit} == 0 || spending_status == 2)) {
            // cpm 设置为必填
            var cpm_price_obj = $("#cpm_detail input[name='price']");
            var cpm_pricelimit_obj = $("#cpm_detail input[name='pricelimit']");
            enable_obj(cpm_price_obj);
            enable_obj(cpm_pricelimit_obj);
            document.getElementById("cpm_detail").style.display = "block";
            document.getElementById("cpc_detail").style.display = "none";

            // cpc 取消必填
            var cpc_price_obj = $("#cpc_detail input[name='price']");
            var cpc_pricelimit_obj = $("#cpc_detail input[name='pricelimit']");
            disable_obj(cpc_price_obj);
            disable_obj(cpc_pricelimit_obj);
        } else if(spending_status == 3) {
            // cpm 设置为必填
            var cpm_price_obj = $("#cpm_detail input[name='price']");
            var cpm_pricelimit_obj = $("#cpm_detail input[name='pricelimit']");
            disable_obj(cpm_price_obj);
            disable_obj(cpm_pricelimit_obj);

            // cpc 取消必填
            var cpc_price_obj = $("#cpc_detail input[name='price']");
            var cpc_pricelimit_obj = $("#cpc_detail input[name='pricelimit']");
            enable_obj(cpc_price_obj);
            enable_obj(cpc_pricelimit_obj);

            document.getElementById("cpc_detail").style.display = "block";
            document.getElementById("cpm_detail").style.display = "none";
        }
    }

    // 根据出价形式的不同，控制价格区块的显示与隐藏
    function switch_cpc_cpm(){
        // 清空价格
        reset_price_block();
        var $ = layui.jquery;
        var rpm_model = get_spending_status();
        if(rpm_model == 2){
            // cpm 设置为必填
            var cpm_price_obj = $("#cpm_detail input[name='price']");
            var cpm_pricelimit_obj = $("#cpm_detail input[name='pricelimit']");
            enable_obj(cpm_price_obj);
            enable_obj(cpm_pricelimit_obj);
            document.getElementById("cpm_detail").style.display = "block";
            document.getElementById("cpc_detail").style.display = "none";

            // cpc 取消必填
            var cpc_price_obj = $("#cpc_detail input[name='price']");
            var cpc_pricelimit_obj = $("#cpc_detail input[name='pricelimit']");
            disable_obj(cpc_price_obj);
            disable_obj(cpc_pricelimit_obj);
        } else if(rpm_model == 3)
        {
            // cpm 设置为必填
            var cpm_price_obj = $("#cpm_detail input[name='price']");
            var cpm_pricelimit_obj = $("#cpm_detail input[name='pricelimit']");
            disable_obj(cpm_price_obj);
            disable_obj(cpm_pricelimit_obj);

            // cpc 取消必填
            var cpc_price_obj = $("#cpc_detail input[name='price']");
            var cpc_pricelimit_obj = $("#cpc_detail input[name='pricelimit']");
            enable_obj(cpc_price_obj);
            enable_obj(cpc_pricelimit_obj);
            document.getElementById("cpc_detail").style.display = "block";
            document.getElementById("cpm_detail").style.display = "none";
        }
    }

    // 将元素设置为必填项,启用
    function enable_obj(obj){
        obj.attr("lay-verify", "required");
        obj.prop('disabled', false);
    }

    // 将元素设置为非必填项，禁用
    function disable_obj(obj)
    {
        obj.attr("lay-verify", "");
        obj.prop('disabled', true);
    }

    // 当出价形式切换的时候，清空价格区块中的输入值
    function reset_price_block(){
        var $ = layui.jquery;
        var price_obj = $("input[name='price']").val("");
        var pricelimit_obj = $("input[name='pricelimit']").val("");
    }

    function del(obj, id)
    {
        layer.confirm('删除之后无法恢复，您确定要删除吗？', {title:false, closeBtn:0}, function(index){
            obj.parentNode.remove();
            pop(id);
            layer.close(index);
        });
    }

    /**
     * 弹出元素
     */
    function pop(id){
        var $ = layui.jquery;
        var str_mid = "";
        var mid = $("#materialid").val();
        var mid_arr = mid.split("|");
        var id = id.toString();
        var pos = $.inArray(id, mid_arr);
        mid_arr.splice(pos, 1);
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
            layer.msg(data.msg, {icon:5});
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


    /**
     * 创建"修改"按钮
     */
    function gen_edit_btn(id, title)
    {
        var $ = layui.jquery;
        var project_type= $('input:radio[name="project_type"]:checked').val();
        var modify_bth = '';
        if(project_type == 3)
        {
            modify_bth = $("<a href=\"{:url('admin/admaterial4info/pop_edit?callback=update_func')}"+ '?material_id=' + id +"\" class=\" j-iframe-pop\">" ).text('修改');
        } else if(project_type == 2)
        {
            modify_bth = $("<a href=\"{:url('admin/admaterial4info/pop_edit_img?callback=update_func')}"+ '?material_id=' + id +"\" class=\" j-iframe-pop\">" ).text('修改');
        } else if(project_type == 1)
        {
            modify_bth = $("<a href=\"{:url('admin/admaterial4info/pop_edit_text?callback=update_func')}"+ '?material_id=' + id +"\" class=\" j-iframe-pop\">" ).text('修改');
        }
        return modify_bth;
    }

    function add_choosed_material_list(id, title)
    {
        var $ = layui.jquery;
        var div = $('<div id="id'+ id +'">');
        var span = $('<span class="info_material_choosed" >').text(title);
        var a = $("<a class='red' onclick=\"del(this, "+ id +")\">").text('删除');
        var preview = $("<a target='_blank' href=\"{:Config('review')}" + id +"\">" ).text('预览');
        var modify_btn = gen_edit_btn(id, title);
        var br = $("<br />");
        $("#material_list").append(div.append(span).append(preview).append(modify_btn).append(a).append(br));
    }

    function load_choosed_material()
    {
        var $ = layui.jquery;
        var mid = $("#materialid").val();
        var choosed_material_data = $("#choosed_material_data").val();
        var choosed_obj = new Array();
        var choosed_arr = choosed_material_data.split("|");
        $("#material_list").hide();
        $.each(choosed_arr, function(k, v){
            var arr = v.split(",");
            add_choosed_material_list(arr[0], arr[1]);
        });
        $("#material_list").show();
    }

    // 加载所有·位对应的尺寸
    function infoLoadAdPositionSize()
    {
        var $ = layui.jquery, form = layui.form;

        // 当投放形式为文字的时候，退出
        var project_type= $('input:radio[name="project_type"]:checked').val();
        if(project_type == 1) {
            return false;
        }
        var adsite = $("#info_adsiteid").val();
        $('#size_optional').html("");
        $.ajax({
            method: "POST",
            url: "{:url('admin/adserving/getAdPositionSize')}",
            data: {adsiteid: adsite, project_type: project_type}
        }).done(function(data){
            var data = JSON.parse(data);
            var obj_size_optional = document.getElementById("size_optional");
            if (!obj_size_optional)
            {
                return false;
            }
            obj_size_optional.style.display = 'none';
            $.each(data, function(i, item){
                // 根据 project_type 的不同，加载不同的按钮路径
                if(project_type == 2) {
                    // 图片
                    var span = $("<a href=\"{:url('admin/admaterial4info/pop_img?callback=func')}"+ '?width=' + item.width +'&height=' + item.height + '&adsite=' + adsite +"\" class=\"layui-btn layui-btn-primary j-iframe-pop fl\" >").text(item.width + '*' + item.height);
                } else {
                    var span = $("<a href=\"{:url('admin/admaterial4info/pop?callback=func')}"+ '?width=' + item.width +'&height=' + item.height + '&adsite=' + adsite +"\" class=\"layui-btn layui-btn-primary j-iframe-pop fl\" >").text(item.width + '*' + item.height);
                }
                $('#size_optional').append(span);
            })
            document.getElementById("size_optional").style.display = 'block';
        })
    }

    function checkPrice()
    {
        var $ = layui.jquery;
        var ele_price = get_price_obj();
        var price = get_price();
        var NoLessThan = ele_price.attr('data-pricelimit');
        NoLessThan = parseInt(NoLessThan);
        if(price < NoLessThan)
        {
            layer.msg('价格不能低于' + NoLessThan + '元', {icon: 5});
            ele_price.focus();
        }
    }

    // 切换投放形式的时候，重置站点和对应的尺寸
    function resetSiteAndSize() {
        var $ = layui.jquery;
        // 如果选中的站点，则根据站点重新加载对应的尺寸，如果没有选择站点，则清空尺寸
        var adsite = $("#info_adsiteid").val();
        if(adsite){
            infoLoadAdPositionSize();
        } else {
            // 清空尺寸
            $('#size_optional').html("");
        }
    }

    // 切换投放形式的时候，重置已选素材
    function resetChoosedMaterials(){
        var $ = layui.jquery;
        // 清空已选素材id
        $("#materialid").val("");
        // 清空显示列表
        $("#material_list").html("");
    }

    function copyPrice2Pricelimit()
    {
        checkPrice();
        var price = get_price();
        var pricelimt = get_pricelimit_obj();
        pricelimt.val(price);
    }

    // 只有的在竞价模式才判断价格
    function get_spending_status()
    {
        var $ = layui.jquery;
        var rpm_model = $("input[name = 'spending']:checked").val();
        return rpm_model;
    }

    // 根据出价形式获取对应的竞价出价
    function get_price(){
        var price_obj = get_price_obj();
        var price = price_obj.val();
        return parseInt(price);
    }

    // 获取出价价格的表单元素
    function get_price_obj(){
        var $ = layui.jquery;
        var rpm_model = get_spending_status();
        if(rpm_model == 2){
            var price_obj = $("#cpm_detail input[name='price']");
        } else if(rpm_model == 3) {
            var price_obj = $("#cpc_detail input[name='price']");
        }
        return price_obj;
    }

    function get_pricelimit_obj(){
        var $ = layui.jquery;
        var rpm_model = get_spending_status();
        if(rpm_model == 2){
            var pricelimit_obj = $("#cpm_detail input[name='pricelimit']");
        } else if(rpm_model == 3) {
            var pricelimit_obj = $("#cpc_detail input[name='pricelimit']");
        }
        return pricelimit_obj;
    }

    function checkPriceLimit()
    {
        var $ = layui.jquery;
        var rpm_model = get_spending_status();
        if(rpm_model == 2 || rpm_model == 3) {
            var price = get_price();
            var e_pricelimit = get_pricelimit_obj();
            var pricelimit = e_pricelimit.val();
            pricelimit = parseInt(pricelimit);
            if(((!pricelimit) || (pricelimit < price)))
            {
                if(price) {
                    copyPrice2Pricelimit();
                }
            }
            if (pricelimit) {
                if ((pricelimit % price) !== 0) {
                    layer.msg('竞价价格不能为空,必须是单价的倍数', {icon: 5});
                    e_pricelimit.val("");
                    e_pricelimit.focus();
                    return false;
                }
            }
        }
    }

    layui.use('laydate', function(){
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

    layui.use(['form'], function() {
        var $ = layui.jquery, form = layui.form;
        // var spendding = $('input:radio[name="spendding"]:checked').val();
        reset_them();
        form.on('radio(CPM)', function(data){
            switch_cpc_cpm();
        });
        form.on('radio(CPC)', function(data){
            switch_cpc_cpm();
        });
        var project_type = $('input:radio[name="project_type"]:checked').val();
        if(project_type == 1) {
            document.getElementById("info_text_btn").style.display = "block";
        }

        // 根据投放形式变换表单
        // 文字
        form.on('radio(project_type1)', function(data) {
            document.getElementById("info_text_btn").style.display = "block";
            document.getElementById("size_optional").style.display = "none";
            resetChoosedMaterials();
        });

        // 图片
        form.on('radio(project_type2)', function(data) {
            document.getElementById("info_text_btn").style.display = "none";
            document.getElementById("size_optional").style.display = "block";
            resetSiteAndSize();
            resetChoosedMaterials();
        });

        // 图文
        form.on('radio(project_type3)', function(data) {
            document.getElementById("info_text_btn").style.display = "none";
            document.getElementById("size_optional").style.display = "block";
            resetSiteAndSize();
            resetChoosedMaterials();
        });

        // 根据站点加载尺寸
        form.on('select(adsiteid)', function(data) {
            infoLoadAdPositionSize();
        });
        infoLoadAdPositionSize();
        {if condition="$edit eq 1"}
        load_choosed_material();
        {/if}
        form.verify({
            positiveInt: function(val, item){
                if(get_spending_status() == 2) {
                    // /^[1-9]\d*$/
                    if (!new RegExp("^[1-9]\\d*$").test(val)) {
                        return '请输入合法的金额';
                    }
                }
            },
            NoLessThanPositiveInt: function(val, item)
            {
                if(get_spending_status() == 2)
                {
                    var pricelimit = $(item).attr('data-pricelimit');
                    if(parseInt(val) < parseInt(pricelimit))
                    {
                        return '价格不能低于' + pricelimit + '元';
                    }
                }
            },
            materialIdNotEmpty: function(val, item)
            {
                if(get_spending_status() == 2)
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
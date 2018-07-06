<!--基本统计-->
<link href="__STATIC__/myCommon.css?v=0.1" rel="stylesheet">
<script src="__STATIC__/js/echarts.min.js"></script>
<style>
    .layui-form-pane .layui-form-label {width:200px;}
</style>
<form class="layui-form layui-form-pane" action="{:url()}" method="get" id="editForm">
    <div class="layui-tab-item layui-show layui-form-pane">
        <div class="layui-form-item">
            <label class="layui-form-label">平台统计</label>
            <div class="layui-input-block">
                <input type="checkbox" name="platform[]" value="pc" title="PC" {if condition="isset($platform) && in_array('pc', $platform)"} checked {/if}>
                <input type="checkbox" name="platform[]" value="web" title="移动 web" {if condition="isset($platform) && in_array('web', $platform)"} checked {/if}>
                <input type="checkbox" name="platform[]" value="app" title="APP" {if condition="isset($platform) && in_array('app', $platform)"} checked {/if}>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="">
                <input type="text"   name="btime" id="serving_time_begin_1" class="my_input my_c field-btime" value="{:input('btime')}" placeholder="开始时间" /> -
                <input type="text"    name="etime" id="serving_time_end_1" class="my_input my_c field-etime" value="{:input('etime')}"  placeholder="结束时间" />
            </div>
        </div>
        <div class="layui-form-item">
            <input lay-filter = "nextStep" type="submit" class="layui-btn" lay-submit="" value="查询" />
            <a href="{:url()}" class="layui-btn layui-btn-primary">重置</a>
        </div>
    </div>
</form>
<div id="my_charts" style="width: 1200px;height:400px;"></div>
<form id="pageListForm">
    <div class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <thead>
            <tr>
                <th style="text-align: center">创建时间</th>
                <th  style="text-align: center;">总曝光量</th>
                <th  style="text-align: center;">CPM总消耗</th>
                <th  style="text-align: center;">总点击量</th>
                <th  style="text-align: center;">CPC总消耗</th>
            </tr>
            </thead>
            <tbody>
            {volist name="data_list" id="vo"}
            <tr>
                <td style="text-align: center;">{$vo['time']}</td>
                <td style="text-align: center;" >{$vo['my_sum']}</td>
                <td style="text-align: center;" >{$vo['cost_sum']}</td>
                <td style="text-align: center;" >{$vo['click_sum'] ?: 0}</td>
                <td style="text-align: center;" >{$vo['cost_sum_click']?:0}</td>
            </tr>
            {/volist}
            </tbody>
        </table>
        {$pages}
    </div>
</form>
{include file="block/layui" /}
<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="width: 600px;height:400px;"></div>
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('my_charts'));

    // 指定图表的配置项和数据
    var option = {
        xAxis: {
            type: 'category',
            data: {$x_data},
            name : '日期',
            axisLabel :{
                interval:0,
                rotate:40,
            }
        },
        legend: {
            data:['曝光量', '点击量']
        },
        yAxis: {
            type: 'value',
            name : '曝光/点击量',
        },
        series: [{
            name:"曝光量",
            // data: [820, 932, 901, 934, 1290, 1330, 1320],
            data: {$y_data},
            type: 'line',
            label: {
                normal: {
                    show: true,
                    position: 'top'
                }
            },
        },
            {
                name:"点击量",
                // data: [820, 932, 901, 934, 1290, 1330, 1320],
                data: {$y_data_click},
                type: 'line',
                label: {
                    normal: {
                        show: true,
                        position: 'top'
                    }
                },
            }
        ]
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>
<script>
    layui.use('laydate', function(){
        // TODO: 结束时间需要大于开始时间，多段时间段不能交叉
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#serving_time_begin_1' //指定元素
        });
        //执行一个laydate实例
        laydate.render({
            elem: '#serving_time_end_1' //指定元素
        });
    });

    // 加载广告
    function loadAdv()
    {
        var $ = layui.jquery, form = layui.form;
        var adsenseid = $("#adsenseid").val();
        $('#adv option').html("");
        form.render('select');
        $.ajax({
            method: "POST",
            url: "{:url('admin/statistics/getAd')}",
            data: {adsenseid: adsenseid}
        }).done(function(data){
            eval("var data=" + data + "");
            var selected_adv = $("#selected_adv").val()
            $.each(data, function(i, item){
                var option = '';
                if(item.id == selected_adv)
                {
                    option = $("<option selected='selected'>").val(item.id).text(item.title);
                } else {
                    option = $("<option>").val(item.id).text(item.title);
                }
                $('#adv').append(option);
            })
            form.render('select');
        })
    }

</script>
<script src="__STATIC__/js/echarts.min.js"></script>
<div id="my_charts" style="width: 1200px;height:400px;"></div>
<div class="layui-form-item">
	<label class="layui-form-label">账户总金额(元)</label>
	<div class="layui-input-inline">
		<input type="text" class="layui-input " name="username" value="{$data.charge_sum}" autocomplete="off" placeholder="" readonly>
	</div>
</div>
<div class="layui-form-item">
	<label class="layui-form-label">剩余金额</label>
	<div class="layui-input-inline">
		<input type="text" class="layui-input " name="username" value="{$data.blance}"  autocomplete="off" placeholder=""  readonly>
	</div>
</div>
<div class="layui-form-item">
	<label class="layui-form-label">消费金额</label>
	<div class="layui-input-inline">
		<input type="text" class="layui-input" name="company"  value="{$data.spendding}" autocomplete="off" placeholder=""  readonly>
	</div>
</div>
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('my_charts'));

    // 指定图表的配置项和数据
    var option = {
        title: {
            // text: '天气情况统计',
            // subtext: '虚构数据',
            left: 'center'
        },
        tooltip : {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        legend: {
            // orient: 'vertical',
            // top: 'middle',
            bottom: 10,
            left: 'center',
            // data: ['西凉', '益州']
        },
        series : [
            {
                type: 'pie',
                radius : '65%',
                center: ['50%', '50%'],
                selectedMode: 'single',
                data:[
                    {value:{$data.blance}, name: '剩余金额（元）'},
                    {value:{$data.spendding}, name: '消费金额（元）'}
                ],
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
    };


    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>
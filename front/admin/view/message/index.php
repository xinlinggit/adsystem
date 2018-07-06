<script src="__STATIC__/js/echarts.min.js"></script>
<div class="layui-row">
    <div class="layui-col-md12">
        <form class="layui-form" action="{:url('')}" method="get">
            <div class="layui-form-item">
                <label class="layui-form-label">日期选择</label>
                <div class="layui-input-inline">
                    <input type="text" name="time" class="layui-input" autocomplete="off" id="timeSelect"
                           placeholder="查询时间范围" value="" onclick="layui.laydate({elem: this,format:'YYYY-MM-DD'})">
                </div>
                <div class="layui-input-inline">
                    <button type="submit" class="layui-btn" style="margin: 0 15px;">查询</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="layui-row">
    <div class="layui-col-md6">
        <table class="layui-table">
            <colgroup>
                <col width="80">
                <col width="200">
                <col>
            </colgroup>
            <thead>
            <tr>
                <th>编号</th>
                <th>通知标题</th>
                <th>通知时间</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {volist name="messages" id="vo" key="ko"}
            <tr data-id="{$vo.id}">
                <td>{$vo.id}</td>
                <td>{$vo.title}</td>
                <td>{$vo.operate_time}</td>
                <td>
                    <a style="color: #0099FF" href="javascript:void 0;" onclick="see({$vo.id})">查看</a>
                </td>
            </tr>
            {/volist}
            </tbody>
        </table>
        {$page}
    </div>
</div>

<!--<div class="layui-card">-->
<!--    <div class="layui-card-header">系统通知</div>-->
<!--    <div class="layui-card-body">-->
<!--            <div class="layui-form-item">-->
<!--                <label class="layui-form-label">通知标题</label>-->
<!--                <div class="layui-input-inline">-->
<!--                    <input style="width: 300px;" type="text" name="title" required  lay-verify="required" disabled value="2.0版本更新通知" autocomplete="off" class="layui-input">-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="layui-form-item layui-form-text">-->
<!--                <label class="layui-form-label">通知内容</label>-->
<!--                <div class="layui-input-inline">-->
<!--                    <textarea  style="width: 300px;" name="desc" placeholder="请输入内容" class="layui-textarea" disabled>-->
<!--                        111111111111111111111111111111111111111111111111111111111111111111111-->
<!--                        111111111111111111111111111111111111111111111111111111111111111111111-->
<!--                        111111111111111111111111111111111111111111111111111111111111111111111-->
<!--                    </textarea>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="layui-form-item">-->
<!--                <div class="layui-input-block">-->
<!--                    <button class="layui-btn layui-btn-normal layui-layer-close">关闭</button>-->
<!--                </div>-->
<!--            </div>-->
<!--    </div>-->
<!--</div>-->

{include file="block/layui" /}

<script>
    layui.use(['laydate'], function () {
        var laydate = layui.laydate;
        //执行一个laydate实例
        laydate.render({
            elem: '#timeSelect'
            , range: true //或 range: '~' 来自定义分割字符
        });

    });

    function see(id) {
        // 阅读
        layui.use(['layer', 'jquery'], function () {
            var layer = layui.layer;
            var $ = layui.jquery;

            // 请求这条数据的信息
            $.ajax({
                url: "{:url('/admin/Message/readMsg')}",
                type: "get",
                data: {'id': id},
                dataType: "json",
                success: function (data) {
                    console.log(data);
                    layer.open({
                        type: 1,
                        offset: '100px',
                        area: ['500px', '400px'], //宽高
                        content: '<div class="layui-card">\n' +
                        '    <div class="layui-card-header">系统通知</div>\n' +
                        '    <div class="layui-card-body">\n' +
                        '            <div class="layui-form-item">\n' +
                        '                <label class="layui-form-label">通知标题</label>\n' +
                        '                <div class="layui-input-inline">\n' +
                        '                    <input style="width: 300px;" type="text" name="title" required  lay-verify="required" disabled value="' + data.title + '" autocomplete="off" class="layui-input">\n' +
                        '                </div>\n' +
                        '            </div>\n' +
                        '            <div class="layui-form-item layui-form-text">\n' +
                        '                <label class="layui-form-label">通知内容</label>\n' +
                        '                <div class="layui-input-inline">\n' +
                        '                    <textarea  style="width: 300px;" name="desc" placeholder="请输入内容" class="layui-textarea" disabled>' + data.content + '</textarea>\n' +
                        '                </div>\n' +
                        '            </div>\n' +
                        '            <div class="layui-form-item">\n' +
                        '                <div class="layui-input-block">\n' +
                        '                    <button class="layui-btn layui-btn-normal layui-layer-close">关闭</button>\n' +
                        '                </div>\n' +
                        '            </div>\n' +
                        '    </div>\n' +
                        '</div>'
                    });
                }
            });
        });
    }
</script>

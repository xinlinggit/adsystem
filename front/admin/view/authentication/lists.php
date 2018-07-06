<form id="pageListForm">
    <div class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <colgroup>
                <col width="50">
            </colgroup>
            <thead>
            <tr>
<!--                <th style="text-align: center;" class="w50">编号</th>-->
                <th style="text-align: center;" class="w200">提交时间</th>
                <th style="text-align: center;" class="w200">认证状态</th>
                <th style="text-align: center;" class="w200">操作理由</th>
                <th style="text-align: center;" class="w200">操作</th>
            </tr>
            </thead>
            <tbody>
            {volist name="data_list" id="vo"}
            <tr>
<!--                <td style="text-align: center;" >{$vo['id']}</td>-->
                <td style="text-align: center;" >{$vo['create_time']}</td>
                <td style="text-align: center;" >
                    {switch name="$vo.status"}
                    {case value="-1"}未通过{/case}
                    {case value="1"}通过{/case}
                    {case value="2"}等待审核{/case}
                    {/switch}
                </td>
                <td style="text-align: center;" >{$vo.remark}</td>
                <td style="text-align: center;" >
                    <a href="check?id={$vo['id']}" target="_blank" class="layui-btn layui-btn-primary">查看</a>
                </td>
            </tr>
            {/volist}
            </tbody>
        </table>
        {$pages}
    </div>
</form>
{include file="block/layui" /}
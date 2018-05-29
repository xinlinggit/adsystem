<div class="layui-form-item">
    <label class="layui-form-label">姓名</label>
    <div class="layui-input-inline">
        <input type="text" class="layui-input field-username" name="username" value ="{$data.username}" lay-verify="required" autocomplete="off" readonly placeholder="" >
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">公司名</label>
    <div class="layui-input-inline">
        <input type="text" class="layui-input field-company" name="company" value ="{$data.company}"  lay-verify="required" autocomplete="off"  readonly placeholder="" >
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">行业</label>
    <div class="layui-input-inline">
        <input type="text" class="layui-input field-industry" name="industry"  value ="{$data.industry}" lay-verify="required" autocomplete="off"  readonly placeholder="" >
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">联系电话</label>
    <div class="layui-input-inline">
        <input type="text" class="layui-input field-phone" name="phone"  value ="{$data.phone}" lay-verify="required" autocomplete="off"  readonly placeholder="" >
    </div>
</div>
		身份证（正）
	<img src="{$data.ID_card_front_url}" width="400px" height="400px" />
		身份证（反）
	<img src="{$data.ID_card_end_url}"  width="400px" height="400px" />
		营业执照（正）
	<img src="{$data.business_license_front_url}" width="400px" height="400px"  />
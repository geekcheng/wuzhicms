<?php defined('IN_WZ') or exit('No direct script access allowed');?>
<?php include $this->template('header','core');?>
<link href="<?php echo R;?>css/validform.css" rel="stylesheet">
<script src="<?php echo R;?>js/validform.min.js"></script>
<body class="body pxgridsbody">
<section class="wrapper">
<div class="row">
	<div class="col-lg-12">
	<section class="panel">
	<?php echo isset($GLOBALS['_menuid']) ? $this->menu($GLOBALS['_menuid']) : '';?>
	<div class="panel-body">
		<form id="myform" name="myfrom" class="form-horizontal tasi-form" method="post" action="index.php?m=member&f=group&v=add<?php echo $this->su();?>">
			<div class="form-group">
				<label class="col-sm-2 control-label">组名</label>
				<div class="col-sm-4 input-group"><input type="text" name="info[name]" class="form-control" placeholder="请输入组名" datatype="/^[a-z\d\u4E00-\u9FA5\uf900-\ufa2d][a-z\d_\u4E00-\u9FA5\uf900-\ufa2d]*[a-z\d\u4E00-\u9FA5\uf900-\ufa2d]$/i" errormsg="组名为2-15位数字、字母、汉字和_组成，且不能以_开头或结尾" sucmsg="OK" ajaxurl="index.php?m=member&f=group&v=check_name<?php echo $this->su();?>"/></div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">排序</label>
				<div class="col-sm-4 input-group"><input type="text" name="info[sort]" class="form-control" placeholder="排序 0-255" datatype="n" errormsg="排序为0-255" sucmsg="OK" /></div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">最小积分</label>
				<div class="col-sm-4 input-group"><input type="text" name="info[points]" class="form-control" placeholder="请输入最小积分" datatype="n" errormsg="请输入最小积分" sucmsg="OK" /></div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">自主升级</label>
				<input type="checkbox" name="info[upgrade]"  value="1"/> 
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">升级价格</label>
				包年：<input type="text" name="info[money_y]" class="date"  size="4" /> 包月：<input type="text" name="info[money_m]" class="date"  size="4" /> 包日：<input type="text" name="info[money_d]" class="date" size="4"/>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label"></label>
				<div class="col-sm-4 input-group"><input class="btn btn-info" type="submit" name="submit" value="提交"></div>
			</div>
		</form>
	</div>
	</section>
	</div>
</div>
</section>
<script src="<?php echo R;?>js/bootstrap.min.js"></script>
<script src="<?php echo R;?>js/jquery.nicescroll.js" type="text/javascript"></script>
<script src="<?php echo R;?>js/pxgrids-scripts.js"></script>
<script type="text/javascript">
	$(function(){
		$(".form-horizontal").Validform({
			tiptype:3
		});
	});
</script>
</body>
</html>
<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>ECSHOP 管理中心 - <?php echo $_web_title; ?> </title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="/Public/Admin/Styles/general.css" rel="stylesheet" type="text/css" />
<link href="/Public/Admin/Styles/main.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/Public/umeditor1_2_2-utf8-php/third-party/jquery.min.js"></script>
</head>
<body>
<h1>
    <span class="action-span"><a href="<?php echo $_page_btn_link; ?>"><?php echo $_page_btn_name; ?></a>
    </span>
    <span class="action-span1"><a href="__GROUP__">ECSHOP 管理中心</a></span>
    <span id="search_id" class="action-span1"> - <?php echo $_page_title; ?> </span>
    <div style="clear:both"></div>
</h1>


<div class="form-div">
    <form action="/index.php/Admin/Goods/lst" method="GET" name="searchForm">
        <p>
            商品名称:
            <input type="text" name="gn" size="30" value="<?php echo I('get.gn'); ?>"/>
        </p>
        <p>
            分　　类:
            <?php $catId = I('get.cat_id'); ?>
            <select name="cat_id">
                <option value="">选择分类</option>
                <?php foreach($catData as $k=>$v): if($v['id']==$catId){ $selected = 'selected="selected"'; }else{ $selected = ""; } ?>
                    <option <?php echo $selected; ?> value="<?php echo $v['id']; ?>">
                        <?php echo str_repeat("-",8*$v['level']).$v['cat_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            品牌名称:
            <?php buildSelect('brand','brand_id','id','brand_name', I('get.brand_id')); ?>
        </p>
        <p>
            价　　格:
            从<input type="text" name="fp" size="10" value="<?php echo I('get.fp'); ?>"/> 到
            <input type="text" name="tp" size="10" value="<?php echo I('get.tp'); ?>"/> 
        </p>
        <p>
            是否上架:
            <input type="radio" name="ios" value="" <?php if(I('get.ios')=="") echo 'checked="checked"';?> />全部
            <input type="radio" name="ios" value="是" <?php if(I('get.ios')=="是") echo 'checked="checked"';?>/>上架
            <input type="radio" name="ios" value="否" <?php if(I('get.ios')=="否") echo 'checked="checked"';?>/>下架
        </p>
        <p>
            添加时间:
            从<input type="type" value="<?php echo I('get.fa'); ?>" id="fa" name="fa" size="20"/> 到
            <input type="type"value="<?php echo I('get.ta'); ?>" id="ta" name="ta" size="20"/>
        </p>
        <p>
            排　　序:
            <?php $orderby=I('get.orderby', 'id_desc'); ?>
            <input type="radio" onclick="this.parentNode.parentNode.submit()" name="orderby" value="id_desc" <?php if(I('get.orderby')=="id_desc"|| I('get.orderby')=="") echo 'checked="checked"';?>/>以添加时间降序
            <input type="radio" onclick="this.parentNode.parentNode.submit()" name="orderby" value="id_asc" <?php if(I('get.orderby')=="id_asc") echo 'checked="checked"';?>/>以添加时间升序
            <input type="radio" onclick="this.parentNode.parentNode.submit()" name="orderby" value="price_asc" <?php if(I('get.orderby')=="price_asc") echo 'checked="checked"';?>/>以价格升序
            <input type="radio" onclick="this.parentNode.parentNode.submit()" name="orderby" value="price_desc" <?php if(I('get.orderby')=="price_desc") echo 'checked="checked"';?>/>以价格降序
        </p>
        <input type="submit" value=" 搜索 " class="button" />
    </form>
</div>

<!-- 商品列表 -->
<form method="post" action="" name="listForm" onsubmit="">
    <div class="list-div" id="listDiv">
        <table cellpadding="3" cellspacing="1">
            <tr>
                <th>编号</th>
                <th>商品名称</th>
                <th>主分类</th>
                <th>扩展分类</th>
                <th>品牌</th>
                <th>logo</th>
                <th>市场价格</th>
                <th>本店价格</th>
                <th>是否上架</th>
                <th>添加时间</th>
                <th>操作</th>
            </tr>
            <?php foreach($data as $k=>$v): ?>
            <tr class="tron">
                <td align="center"><?php echo $v['id']; ?></td>
                <td align="center" class="first-cell"><span><?php echo $v['goods_name']; ?></span></td>
                <td align="center"><?php echo $v['cat_name']; ?></td>
                <td align="center"><?php echo $v['ext_cat_name']; ?></td>
                <td align="center"><?php echo $v['brand_name']; ?></td>
                <td align="center"><?php echo showImage($v['sm_logo']); ?></td>
                <td align="center"><span><?php echo $v['market_price']; ?></span></td>
                <td align="center"><span><?php echo $v['shop_price']; ?></span></td>
                <td align="center"><span><?php echo $v['is_on_sale']; ?></span></td>
                <td align="center"><span><?php echo $v['addtime']; ?></span></td>
                <td align="center">
                <a href="<?php echo U('qty?id='.$v['id']); ?>" title="库存量">库存量</a>
                <a href="<?php echo U('edit?id='.$v['id']); ?>" title="编辑"><img src="/Public/Admin/Images/icon_edit.gif" width="16" height="16" border="0" /></a>
                <a onclick="return confirm('确认删除吗？')"href="<?php echo U('delete?id='.$v['id']); ?>" onclick="" title="回收站"><img src="/Public/Admin/Images/icon_trash.gif" width="16" height="16" border="0" /></a></td>
            </tr>
            <?php endforeach; ?>
        </table>

    <!-- 分页开始 -->
        <table id="page-table" cellspacing="0">
            <tr>
                <td width="80%">&nbsp;</td>
                <td align="center" nowrap="true">
                    <?php echo $page; ?>
                </td>
            </tr>
        </table>
    <!-- 分页结束 -->
    </div>
</form>

<!--获取时间的插件需要引入的文件(基于JQuery的一个包)-->
<link href="/Public/datetimepicker/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" charset="utf-8" src="/Public/datetimepicker/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/Public/datetimepicker/datepicker-zh_cn.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="/Public/datetimepicker/time/jquery-ui-timepicker-addon.min.css" />
<script type="text/javascript" src="/Public/datetimepicker/time/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript" src="/Public/datetimepicker/time/i18n/jquery-ui-timepicker-addon-i18n.min.js"></script>

<script type="text/javascript">
    $.timepicker.setDefaults($.timepicker.regional['zh-CN']);  //定义使用中文
    //2016-12-28 00:00
    $("#fa").datetimepicker();
    $("#ta").datetimepicker();

    //或者是：(没有时和分的显示) 2016-12-01
    //$("#fa").datepicker({ dateFormat: "yy-mm-dd" });
    //$("#ta").datepicker({ dateFormat: "yy-mm-dd" });
</script>


<!--引入行高亮显示的js文件-->
<script type="text/javascript" src="/Public/Admin/Js/tron.js"></script>



<div id="footer">
共执行 9 个查询，用时 0.025162 秒，Gzip 已禁用，内存占用 3.258 MB<br />
版权所有 &copy; 2005-2012 上海商派网络科技有限公司，并保留所有权利。</div>
</body>
</html>
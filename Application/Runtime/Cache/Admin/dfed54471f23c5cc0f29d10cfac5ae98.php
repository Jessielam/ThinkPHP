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



<div class="tab-div">
    <div id="tabbody-div">
        <form  action="/index.php/Admin/Category/edit/id/23.html" method="post">
            <input type="hidden" name="id" value="<?php echo $data['id']; ?>" />
            <table width="90%" class="tab_table" align="center">
                <tr>
                    <td class="label">选择分类: </td>
                    <td>
                        <select name="parent_id">
                            <option value="0">顶级分类</option>
                            <?php foreach($catData as $k=>$v): if($v['id']==$data[id]||in_array($v['id'], $children)) continue; if($v['id']==$data['parent_id']){ $selected = 'selected="selected"'; }else{ $selected = ""; } ?>
                                <option <?php echo $selected; ?> value="<?php echo $v['id']; ?>">
                                    <?php echo str_repeat("-",8*$v['level']).$v['cat_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label">分类名称：</td>
                    <td><input type="text" name="cat_name"  value="<?php echo $data['cat_name']; ?>" size="22"/>
                </tr>
                <tr>
                    <td class="label">推荐到楼层：</td>
                    <td>
                        <input type="radio" name="is_floor" value="是" <?php if($data['is_floor']=="是") echo 'checked="checked"';?> /> 是
                        <input type="radio" name="is_floor" value="否" <?php if($data['is_floor']=="否") echo 'checked="checked"';?> /> 否
                    </td>
                </tr>
            </table>
            <div class="button-div">
                <input type="submit" value=" 确定 " class="button"/>
                <input type="reset" value=" 重置 " class="button" />
            </div>
        </form>
    </div>
</div>

<div id="footer">
共执行 9 个查询，用时 0.025162 秒，Gzip 已禁用，内存占用 3.258 MB<br />
版权所有 &copy; 2005-2012 上海商派网络科技有限公司，并保留所有权利。</div>
</body>
</html>
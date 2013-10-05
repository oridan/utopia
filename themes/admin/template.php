<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0">
<meta name="robots" content="noindex"/>
<link href="{const.PATH_REL_CORE}themes/admin/select2/select2.css" rel="stylesheet" type="text/css">
<link href="{const.PATH_REL_CORE}themes/admin/fontello/css/ucore-symbols.css" rel="stylesheet" type="text/css">
<link href="http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css">
<script src="{const.PATH_REL_CORE}themes/admin/html5shiv.js"></script>
<script src="{const.PATH_REL_CORE}themes/admin/select2/select2.js"></script>
<script>
$(function(){
	if ($('nav ul li').length) {
		$(document).on('click touchstart','nav .icon-menu',function() { $('body').toggleClass('open'); return false; });
	}
});

utopia.Initialise.add(InitCombo);
function InitCombo() {
	$('.inputtype-combo').select2({adaptDropdownCssClass:function(c){return c;}});
}
</script>
</head>
<body class="u-admin">

<header><?php
	$o = utopia::GetInstance('UserProfileDetail'); $r = $o->LookupRecord();
	if ($r) echo '<span id="user_welcome"><i class="icon-user"></i> Hi, '.$r['visible_name'].' | </span>';
	echo '<a href="{home_url}">Website</a>';
	if ($r) echo ' | {logout}';
?></header>
<div id="contentWrap">
	<nav>
		<span class="icon-menu mobile"></span>
		{UTOPIA.modlinks}
	</nav>
	<div id="content">
		{utopia.content}
		<div class="footer">
			<div>Powered by <a href="http://ucorecms.org" target="_blank">uCore CMS</a>. Running <?php include(PATH_ABS_CORE.'version.txt'); ?>.</div>
			<div><a href="https://github.com/oridan/utopia" target="_blank">GitHub</a> | <a href="https://www.facebook.com/uCoreCMS" target="_blank">Facebook</a></div>
		</div>
	</div>
</div>

</body>
</html>

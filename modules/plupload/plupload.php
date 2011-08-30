<?php
uJavascript::IncludeFile(dirname(__FILE__).'/plupload.full.js');
uJavascript::IncludeFile(dirname(__FILE__).'/jquery.ui.plupload/jquery.ui.plupload.js');
uStylesheet::IncludeFile(dirname(__FILE__).'/jquery.ui.plupload/css/jquery.ui.plupload.css');

class uPlupload {
	static function Init($jsVarName,$uploadPath) {
		$pathCore = PATH_REL_CORE;
		uJavascript::AddText(<<<FIN
var $jsVarName = {
    runtimes : 'html5,flash,browserplus,html4',
    chunk_size : '1mb',
    url : '$uploadPath',
    flash_swf_url : '{$pathCore}modules/plupload/plupload.flash.swf'
};
FIN
		);
	}
}

?>

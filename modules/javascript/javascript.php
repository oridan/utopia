<?php

// dependancies
// check dependancies exist - Move to install?

uJavascript::IncludeFile(dirname(__FILE__).'/js/min/jquery.metadata.min.js');
uJavascript::IncludeFile(dirname(__FILE__).'/carousel/jquery.jcarousel.min.js');
uJavascript::IncludeFile(dirname(__FILE__).'/js/ajaxfileupload.js');
uJavascript::IncludeFile(dirname(__FILE__).'/js/sqlDate.js');
uJavascript::IncludeFile(dirname(__FILE__).'/js/functs.js');
utopia::AddJSFile(PATH_REL_CORE.'.javascript.js');

$b = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
if (strpos($b,'MSIE 6') !== FALSE || strpos($b,'MSIE 7') !== FALSE) utopia::AddJSFile(dirname(__FILE__).'/js/DD_belatedPNG-min.js');

class uJavascript extends uBasicModule {
	private static $includeFiles = array();
	public static function IncludeFile($path) {
		// if running ALERT: CANNOT BE CALLED AT RUN TIME
		self::$includeFiles[] = $path;
	}
	private static $includeText = '';
	public static function IncludeText($text) {
		self::$includeText .= "\n$text";
	}
	public static function AddText($text) {
		utopia::AppendVar('script_include',"\n$text");
	}

	public function SetupParents() {
		modOpts::AddOption('uJavascript','googleAPI','Google API Key');
		$key = ($gAPI = modOpts::GetOption('uJavascript','googleAPI')) ? 'key='.$gAPI.'&' : '';

		// commented because if a user enters an incorrect version (too high for example) they can not change it back.
//		modOpts::AddOption('uJavascript','jQuery','jQuery Version',1);
//		$jq  = modOpts::GetOption('uJavascript','jQuery');

//		modOpts::AddOption('uJavascript','jQueryUI','jQuery UI Version',1);
//		$jqui = modOpts::GetOption('uJavascript','jQueryUI');

		modOpts::AddOption('uJavascript','jQueryUI-Theme','jQuery UI Theme','ui-lightness');
		$jquitheme = modOpts::GetOption('uJavascript','jQueryUI-Theme');

		$s = (utopia::IsRequestSecure()) ? 's' : '';
		utopia::AddJSFile('//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js',true);
		utopia::AddJSFile('//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js',true);
		utopia::AddCSSFile('//ajax.googleapis.com/ajax/libs/jqueryui/1/themes/'.$jquitheme.'/jquery-ui.css');
		utopia::AddCSSFile(PATH_REL_CORE.'modules/javascript/js/jquery.auto-complete.css');
	}

	public function RunModule() {
	}

	static function BuildJavascript() {
		$body = '';
		array_push($GLOBALS['jsDefine'],'FORMAT_DATETIME','FORMAT_DATE','FORMAT_TIME','USE_TABS','PATH_REL_ROOT','PATH_REL_CORE');
		if (array_key_exists('jsDefine',$GLOBALS))
		foreach ($GLOBALS['jsDefine'] as $var) {
			if (!defined($var)) continue;
			$val = is_numeric(constant($var)) ? constant($var) : '\''.constant($var).'\'';
			$body .= "var $var = $val;\n";
		}
/*
		$lastTime = NULL;
		foreach (self::$includeFiles as $filename) {
			//does it exist?
			if (!file_exists($filename)) continue;
			if ($lastTime == NULL || filemtime($filename) > $lastTime) $lastTime = filemtime($filename);
		}

		$etag = sha1($lastTime.'-'.count(self::$includeFiles).'-'.strlen($body));
		utopia::Cache_Check($etag,'text/javascript');
*/
		foreach (self::$includeFiles as $filename) {
//			//does it exist?
			if (!file_exists($filename)) continue;
			$body .= file_get_contents($filename).';';
		}
    
    $body .= self::$includeText;
    
		$body = JSMin::minify($body);
		file_put_contents(PATH_ABS_CORE.'.javascript.js',$body);
//		ob_end_clean();
//		header('Content-Encoding: ',true);
		
//		utopia::Cache_Output($body,$etag,'text/javascript');
	}
}

?>

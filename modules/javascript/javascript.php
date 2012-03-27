<?php

class uJavascript extends uBasicModule {
	public function GetOptions() { return PERSISTENT; }
	private static $includeFiles = array();
	public static function IncludeFile($path) {
		// if running ALERT: CANNOT BE CALLED AT RUN TIME
		if (!file_exists($path)) $path = utopia::GetAbsolutePath($path);
		if (!file_exists($path)) return;
		self::$includeFiles[] = $path;
	}
	private static $includeText = '';
	public static function IncludeText($text) {
		self::$includeText .= "\n$text";
	}
	public static function AddText($text) {
		utopia::AppendVar('script_include',"\n$text");
	}
	public function GetUUID() { return 'javascript.js'; }

	public function SetupParents() {
		module_Offline::IgnoreClass(__CLASS__);
		$this->SetRewrite(true);
		utopia::AddJSFile($this->GetURL(),true);

		utopia::AddJSFile('//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js',true);
		utopia::AddJSFile('//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js',true);

		uJavascript::IncludeFile(dirname(__FILE__).'/js/min/jquery.metadata.min.js');
		uJavascript::IncludeFile(dirname(__FILE__).'/carousel/jquery.jcarousel.min.js');
		uJavascript::IncludeFile(dirname(__FILE__).'/js/ajaxfileupload.js');
		uJavascript::IncludeFile(dirname(__FILE__).'/js/sqlDate.js');
		uJavascript::IncludeFile(dirname(__FILE__).'/js/functs.js');

		modOpts::AddOption('google_api_key','Google API Key');
		$key = ($gAPI = modOpts::GetOption('google_api_key')) ? 'key='.$gAPI.'&' : '';

		// commented because if a user enters an incorrect version (too high for example) they can not change it back.
//		modOpts::AddOption('jquery_version','jQuery Version',NULL,1);
//		$jq  = modOpts::GetOption('jQuery');

//		modOpts::AddOption('jquery_ui_version','jQuery UI Version',NULL,1);
//		$jqui = modOpts::GetOption('jquery_ui_version');

	}

	public function RunModule() {
		utopia::CancelTemplate();

		clearstatcache();
		$uStr = '';
		self::$includeFiles = array_unique(self::$includeFiles);
		foreach (self::$includeFiles as $filename) {
			//does it exist?
			if (!file_exists($filename)) continue;
			$uStr .= $filename.filemtime($filename).'-'.filesize($filename);
		}

		$etag = sha1($uStr.'-'.count(self::$includeFiles).'-'.sha1(self::GetJavascriptConstants()).self::$includeText.'-'.PATH_REL_CORE);
		utopia::Cache_Check($etag,'text/javascript',$this->GetUUID());

		// minify caching
		$minifyCache = '';
		if (file_exists(__FILE__.'.cache') && file_exists(__FILE__.'.cache.sha1')) $minifyCache = file_get_contents(__FILE__.'.cache.sha1');
		if ($etag !== $minifyCache) {
			$out = self::BuildJavascript(true);
			file_put_contents(__FILE__.'.cache',$out); chmod(__FILE__.'.cache', 0664);
			file_put_contents(__FILE__.'.cache.sha1',$etag); chmod(__FILE__.'.cache.sha1', 0664);
		} else {
			$out = file_get_contents(__FILE__.'.cache');
		}

		utopia::Cache_Output($out,$etag,'text/javascript',$this->GetUUID());
	}

	static function GetJavascriptConstants() {
		$body = '';
		array_push($GLOBALS['jsDefine'],'FORMAT_DATETIME','FORMAT_DATE','FORMAT_TIME','USE_TABS','PATH_REL_ROOT','PATH_REL_CORE');
		$GLOBALS['jsDefine'] = array_unique($GLOBALS['jsDefine']);
		if (array_key_exists('jsDefine',$GLOBALS))
		foreach ($GLOBALS['jsDefine'] as $var) {
			if (!defined($var)) continue;
			$val = is_numeric(constant($var)) ? constant($var) : '\''.constant($var).'\'';
			$body .= "var $var = $val;\n";
		}
		return $body;
	}

	static function BuildJavascript($minify=true) {
		$body = self::GetJavascriptConstants();

		$body .= self::$includeText;

		foreach (self::$includeFiles as $filename) {
			if (!file_exists($filename)) continue;
			$body .= file_get_contents($filename).";\n\n";
		}
    
		if ($minify) $body = JSMin::minify($body);

		return $body;
	}
}

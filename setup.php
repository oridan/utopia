<?php

define('CFG_TYPE_TEXT',flag_gen('configType'));
define('CFG_TYPE_PATH',flag_gen('configType'));
define('CFG_TYPE_PASSWORD',flag_gen('configType'));
define('CFG_TYPE_CALLBACK',flag_gen('configType'));

uConfig::AddConfigVar('ERROR_EMAIL','Email Address');
define('DB_TYPE','mysql');// uConfig::AddConfigVar('DB_TYPE','Database Type',NULL,array('mysql'));
uConfig::AddConfigVar('SQL_SERVER','Database Host');
uConfig::AddConfigVar('SQL_PORT','Database Port',3306);
uConfig::AddConfigVar('SQL_DBNAME','Database Name');
uConfig::AddConfigVar('SQL_USERNAME','Database Username');
uConfig::AddConfigVar('SQL_PASSWORD','Database Password',NULL,NULL,CFG_TYPE_PASSWORD);

uConfig::AddConfigVar('FORMAT_DATE','<a target="_blank" href="http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html#function_date-format">Date Format</a>','%d/%m/%Y');
uConfig::AddConfigVar('FORMAT_TIME','<a target="_blank" href="http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html#function_date-format">Time Format</a>','%H:%I:%S');

uConfig::AddConfigVar('TEMPLATE_ADMIN','Admin Template',PATH_REL_CORE.'styles/admin',array('utopia::GetTemplates',array(false)),CFG_TYPE_CALLBACK|CFG_TYPE_PATH);

uConfig::ReadConfig();

class uConfig {
	static $configVars = array();
	static function AddConfigVar($name,$readable,$default=NULL,$values=NULL,$type=CFG_TYPE_TEXT) {
		if (array_key_exists($name,self::$configVars)) { echo "Config variable $name already added." ; return false;}
		self::$configVars[$name] = array('name'=>$readable,'default'=>$default,'values'=>$values,'type'=>$type);
	}
	static $oConfig = '';
	static function ReadConfig() {
		$arr = array();
		// read config
		if (file_exists(PATH_ABS_CONFIG)) {
			$conf = file_get_contents(PATH_ABS_CONFIG);
			$lines = explode(PHP_EOL,$conf);
			if (!$lines) return $arr;
			array_shift($lines);
			foreach ($lines as $line) {
				if (!$line) continue;
				list($ident,$val) = explode('=',$line,2);
				$arr[trim($ident)] = trim($val);
			}
		}
		self::$oConfig = $arr;
	}
	static function SaveConfig() {
		$text = "<?php die('Direct access to this file is prohibited.'); ?>".PHP_EOL;
		foreach (self::$configVars as $key => $info) {
			if (!defined($key)) continue;
			$val = constant($key);
			$text .= "$key=$val".PHP_EOL;
		}
		file_put_contents(PATH_ABS_CONFIG,trim($text,PHP_EOL));
	}
	static $isDefined = FALSE;
	static function DefineConfig() {
		$arr = self::$oConfig;
		if (isset($_SESSION['__config_validate']) && $_SESSION['__config_validate'] && $_POST && isset($_POST['ucore_reconfig'])) {
			$arr = $_POST;
			unset($_SESSION['__config_validate']);
		}
		foreach (self::$configVars as $key => $info) {
			if (!isset($arr[$key])) {
				if (!$info['default']) continue;
				$arr[$key] = $info['default'];
			}
			$val = $arr[$key];
			if (!$val && $info['type'] == CFG_TYPE_PASSWORD && isset(self::$oConfig[$key])) {
				$val = self::$oConfig[$key];
			}
			define($key,$val);
		}

		define("FORMAT_DATETIME"         , FORMAT_DATE.' '.FORMAT_TIME);

		self::$isDefined = TRUE;
	}
	static $isValid = FALSE;
	static function ValidateConfig() {
		$showConfig = false;
		foreach (self::$configVars as $key => $info) {
			if (!defined($key)) {
				$showConfig = true;
			}
			$val = defined($key) ? constant($key) : null;
			if (($info['type'] & CFG_TYPE_PASSWORD) && empty($val)) {
				$showConfig = true;
				self::$configVars[$key]['notice'] = "Must not be empty.";
			}
			
			if (($info['type'] & CFG_TYPE_PATH) && !is_dir(PATH_ABS_ROOT.$val)) {
				$showConfig = true;
				self::$configVars[$key]['notice'] = "Must be a valid directory.";
			}
		}

		if ($showConfig) self::ShowConfig();
		try {
			sql_query('SHOW TABLES FROM `'.SQL_DBNAME.'`');
		} catch (Exception $e) {
			self::$configVars['SQL_SERVER']['notice'] = $e->getMessage().' ('.$e->getCode().')';
		}

		$changed = false;
		foreach (self::$configVars as $key => $info) {
			if (isset($info['notice'])) self::ShowConfig();
			if (!isset(self::$oConfig[$key]) || self::$oConfig[$key] !== constant($key)) $changed = true;
		}
		
		if ($changed) self::SaveConfig();

		self::$isValid = TRUE;
		return true;
	}
	static function ShowConfig() {
		if (!self::$isValid) {
			$rc = preg_replace('/^'.preg_quote(PATH_REL_ROOT,'/').'/','',PATH_REL_CORE);
			utopia::UseTemplate($rc.'styles/install');
			utopia::SetTitle('uCore Installation');
			echo '<h1>uCore Installation</h1>';
		}

		$frmAction = DEFAULT_FILE;
		echo <<<FIN
<form method="post" action="$frmAction">
<input type="hidden" name="ucore_reconfig" value="true" />
<table>
	<colgroup>
		<col align="right">
		<col style="text-align: left; padding-left: 15px">
	</colgroup>
FIN;
		foreach (self::$configVars as $key => $info) {
			$val = defined($key) ? constant($key) : $info['default'];
			echo '<tr><td>'.$info['name'].':</td>';
			if (($info['type'] & CFG_TYPE_CALLBACK) && is_callable($info['values'][0])) {
				$info['values'] = call_user_func_array($info['values'][0],$info['values'][1]);
			}
			echo '<td>';
			if (isset($info['notice'])) echo '<span style="color:red;font-size:0.8em">'.$info['notice'].'</span><br/>';
			if (is_array($info['values'])) {
				$assoc = is_assoc($info['values']);
				echo '<select name="'.$key.'">';
				foreach ($info['values'] as $k => $v) {
					if ($info['type'] & CFG_TYPE_PATH) $v = str_replace(PATH_ABS_ROOT,'',$v);
					$selected = (($assoc ? $k : $v) == $val) ? ' selected="selected"' : '';
					$selVal = $assoc ? ' value="'.$k.'"' : '';
					echo '<option'.$selected.$selVal.'>'.$v.'</option>';
				}
				echo '</select>';
			} else {
				$type = $info['type'] & CFG_TYPE_PASSWORD ? 'password' : 'text';
				$dVal = $info['type'] & CFG_TYPE_PASSWORD ? '' : $val;
				if ($info['type'] & CFG_TYPE_PATH) {
					echo PATH_REL_ROOT;
					$dVal = str_replace(PATH_ABS_ROOT,'',$dVal);
				}
				echo '<input name="'.$key.'" type="'.$type.'" size="40" value="'.$dVal.'">';
			}
			echo '</td></tr>';
		}
		$_SESSION['__config_validate'] = true;
		echo '</table><input type="submit" value="Make It So!"></form>';
		if (!self::$isValid) utopia::Finish();
	}
}

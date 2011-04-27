<?php

class internalmodule_Reconfigure extends uBasicModule {
	// title: the title of this page, to appear in header box and navigation
	public function GetTitle() { return 'Reconfigure Database'; }
	public function GetOptions() { return IS_ADMIN | ALWAYS_ACTIVE; }

	public function SetupParents() {
		if (!internalmodule_AdminLogin::IsLoggedIn(ADMIN_USER)) $this->DisableModule('You must log in as the Site Administrator to use this module.');
		$this->AddParent('internalmodule_Admin');
	}

	public function GetSortOrder() { return -10; }

	public function ParentLoad($parent) {
		//utopia::LinkList_Add('child_buttons',$this->GetTitle(),$this->GetURL(),$this->GetSortOrder(),NULL,array('class'=>'fdb-btn'));
	}

	public function RunModule() {
		//utopia::CancelTemplate();
		uConfig::ShowConfig();
	}
}


class internalmodule_Admin extends uBasicModule {
	// title: the title of this page, to appear in header box and navigation
	public function GetTitle() { return 'Admin Home'; }
	public function GetOptions() { return IS_ADMIN | ALWAYS_ACTIVE; }

	public function GetSortOrder() { return -10000; }
	public function GetURL($filters = NULL, $encodeAmp = false) {
		$qs = $filters ? '?'.http_build_query($filters) : '';
		return PATH_REL_CORE.'index.php'.$qs;
	}
	public function SetupParents() {
		$this->AddParent('/');
//		$this->AddParent('internalmodule_Admin');
		$this->RegisterAjax('toggleT',array($this,'toggleT'));
		$this->RegisterAjax('toggleQ',array($this,'toggleQ'));
//		$this->RegisterAjax('optimizeTables',array($this,'optimizeTables'),false);
	}
	public function optimizeTables() {
		echo '<h3>Optimise Tables</h3>';
		echo '<pre>';
		set_time_limit( 100 );

		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$start = $time;

		$db_name = SQL_DBNAME;
		echo "Database : $db_name \n";
		$res = sql_query("SHOW TABLES FROM `" . $db_name . "`") or die('Query : ' . mysql_error());
		while ( $rec = mysql_fetch_row($res) ) {
			sql_query('OPTIMIZE TABLE `'.$rec[0].'`');
		}

		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $start), 6);
		echo "\nTables optimised in $total_time secs\n</pre>";
	}

	public function toggleT() {
		if (!array_key_exists('admin_showT',$_SESSION))
			$_SESSION['admin_showT'] = true;
		else
			$_SESSION['admin_showT'] = !$_SESSION['admin_showT'];
		die('window.location.reload();');
	}

	public function toggleQ() {
		if (!array_key_exists('admin_showQ',$_SESSION))
			$_SESSION['admin_showQ'] = true;
		else
			$_SESSION['admin_showQ'] = !$_SESSION['admin_showQ'];
		die('window.location.reload();');
	}

	public function ParentLoad($parent) { }

	static function compareVersions($ver1,$ver2) {
		if ($ver1 == $ver2) return 0;

		//major.minor.maintenance.build
		preg_match_all('/([0-9]+)\.?([0-9]+)?\.?([0-9]+)?\.?([0-9]+)?/',$ver1,$matches1,PREG_SET_ORDER); $matches1 = $matches1[0]; array_shift($matches1);
		preg_match_all('/([0-9]+)\.?([0-9]+)?\.?([0-9]+)?\.?([0-9]+)?/',$ver2,$matches2,PREG_SET_ORDER); $matches2 = $matches2[0]; array_shift($matches2);

		if ($matches1 == $matches2) return 0;
		while (count($matches1) < 4) $matches1[] = 0;
		while (count($matches2) < 4) $matches2[] = 0;
		foreach ($matches1 as $k => $v) {
			if ($v == $matches2[$k]) continue;
			if ($v < $matches2[$k]) return -1;
			if ($v > $matches2[$k]) return 1;
		}
		return 0;
	}

	public function RunModule() {
		echo '<h1>Welcome to Admin Home</h1>';

		$gitTags = json_decode(file_get_contents("http://github.com/api/v2/json/repos/show/oridan/utopia/tags"),true);
		$gitTags = array_keys($gitTags['tags']);
		usort($gitTags,'internalmodule_Admin::compareVersions');
		
		$latestVer = end($gitTags);
		$myVer = file_get_contents(PATH_ABS_CORE.'version.txt');
		echo '<table><tr><td>Current Version:</td><td>'.$myVer.'</td></tr><tr><td>Latest Version:</td><td>'.$latestVer.'</td></tr></table>';
		if (self::compareVersions($myVer,$latestVer) < 0) echo '<a href="https://github.com/oridan/utopia/zipball/'.$latestVer.'">Update Available</a>';
		else echo 'You are using the latest version of uCore.';

		if (!internalmodule_AdminLogin::IsLoggedIn(ADMIN_USER)) return;
		//GetFiles(true);
		timer_start('Rebuild Javascript');
		uJavascript::BuildJavascript();
		timer_end('Rebuild Javascript');

		$rc = PATH_REL_CORE;
		$ucStart = '## uCore ##';
		$ucEnd	 = '##-uCore-##';
		$content = <<<FIN
<FilesMatch "\.(ico|pdf|flv|jpg|jpeg|png|gif|js|css|swf)$">
	SetOutputFilter DEFLATE
	<IfModule mod_headers.c>
		Header set Cache-Control "max-age=290304000, public"
		Header set Expires "Thu, 15 Jan 2015 20:00:00 GMT"
	</IfModule>
</FilesMatch>

<IfModule mod_rewrite.c>
	# Tell PHP that the mod_rewrite module is ENABLED.
	SetEnv HTTP_MOD_REWRITE On

	RewriteEngine on
	RewriteRule ^(.*/)?\.svn/ - [F,L]
	ErrorDocument 403 "Access Forbidden"

	RewriteRule u/([^/?$]+)	{$rc}index.php?uuid=$1&%2 [NE,L,QSA]

	RewriteCond %{REQUEST_URI} ^$ [OR]
	RewriteCond %{REQUEST_URI} ^/$
	RewriteRule ^(.*)$ {$rc}index.php?uuid=cms [NE,L,QSA]

	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d     
	RewriteRule ^(.*)$ {$rc}index.php?uuid=cms [NE,L,QSA]
</IfModule>
FIN;
		$search = PHP_EOL.PHP_EOL.PHP_EOL.$ucStart.PHP_EOL.$content.PHP_EOL.$ucEnd;
		$htaccess = '';
		if (file_exists(PATH_ABS_ROOT.'.htaccess')) $htaccess = file_get_contents(PATH_ABS_ROOT.'.htaccess');
		if (strpos($htaccess,$search) === FALSE) {
			// first remove existing (outdated)
			$s = strpos($htaccess,$ucStart);
			$e = strrpos($htaccess,$ucEnd); // PHP5
			//$e = strpos(strrev($htaccess),strrev($ucEnd)); // PHP4
			if ($s !== FALSE && $e !== FALSE) {
				$e += strlen($ucEnd); // PHP5
				//$e = strlen($htaccess) - $e; // PHP4
				$htaccess = substr_replace($htaccess,'',$s,$e);
			}

			$htaccess = trim($htaccess).$search;
			file_put_contents(PATH_ABS_ROOT.'.htaccess',$htaccess);
			echo 'Updated .htaccess';
		}
		echo "<h3>Variables</h3><pre>";
		echo 'PATH_ABS_ROOT: '.PATH_ABS_ROOT.'<br>';
		echo 'PATH_REL_ROOT: '.PATH_REL_ROOT.'<br>';
		echo 'PATH_ABS_CORE: '.PATH_ABS_CORE.'<br>';
		echo 'PATH_REL_CORE: '.PATH_REL_CORE.'<br>';
		echo 'PATH_ABS_CONFIG: '.PATH_ABS_CONFIG.'<br>';
		echo '</pre>';

		$installed = utopia::GetModules();
		echo '<h3 style="cursor:pointer" onclick="$(\'#modulesList\').toggle();">Installed Modules</h3><div id="modulesList" style="display:none"><pre>';
		foreach ($installed as $m)
			echo $m['module_name']."\n";
		echo '</pre></div>';
		
		echo '<a href="?optimise=1">Optimise Tables</a>';
		if (isset($_GET['optimise'])) $this->optimizeTables();
	}
}
?>

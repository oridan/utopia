<?php

//-- debugging
//define('SHOW_QUERY'		,false);

//--  InputType
define('itNONE'		,'');
define('itBUTTON'	,'button');
define('itSUBMIT'	,'submit');
define('itRESET'	,'reset');
define('itCHECKBOX'	,'checkbox');
define('itOPTION'	,'option');

define('itMD5'		,'password');
define('itPASSWORD'	,'password');
define('itPLAINPASSWORD','plain_password');

define('itTEXT'		,'text');
define('itTEXTAREA'	,'textarea');
define('itSUGGEST'	,'suggest');
define('itSUGGESTAREA'	,'suggestarea');
define('itCOMBO'	,'combo');
define('itLISTBOX'	,'listbox');
define('itFILE'		,'file');
define('itDATE'		,'date');
define('itTIME'		,'time');
define('itDATETIME'	,'datetime');
define('itSCAN'		,'scan');
define('itCUSTOM'	,'~~custom~~');

//--  FilterCompareType
define('ctCUSTOM'	,'{custom}');
define('ctIGNORE'	,'{ignore}');
define('ctMATCH'	,'{MATCH}');
define('ctMATCHBOOLEAN'	,'{MATCH_BOOLEAN}');
define('ctANY'		,'');
define('ctEQ'		,'=');
define('ctNOTEQ'	,'!=');
define('ctLT'		,'<');
define('ctGT'		,'>');
define('ctLTEQ'		,'<=');
define('ctGTEQ'		,'>=');
define('ctLIKE'		,'LIKE');
define('ctNOTLIKE'	,'NOT LIKE');
define('ctIN'		,'IN');
define('ctIS'		,'IS');
define('ctISNOT'	,'IS NOT');
define('ctREGEX'	,'REGEXP');
define('ctBETWEEN'  ,'BETWEEN');

//--  Filter Sections
define('FILTER_HAVING'	,'having');
define('FILTER_WHERE'	,'where');

//--  Date Formats
// date
//	define("SQL_FORMAT_DATE"			,'%Y-%m-%d');
//	define("SQL_FORMAT_TIME"			,'%H:%i:%s');
//	define("SQL_FORMAT_DATETIME"		,SQL_FORMAT_DATE.' '.SQL_FORMAT_TIME);
//	define("SQL_FORMAT_EMPTY_DATE"		,'00/00/0000');
//	define("PHP_FORMAT_DATE"			,'d/m/Y'); // used with date(), but strftime() uses the SQL format
// timestamp
//	define("SQL_FORMAT_TIMESTAMP"		,'%d/%m/%Y %H:%i:%s');
//	define("SQL_FORMAT_EMPTY_TIMESTAMP"	,'00/00/0000 00:00:00');
//	define("PHP_FORMAT_TIMESTAMP"		,'d/m/Y H:i:s'); // used with date(), but strftime() uses the SQL format
// Javascript date/time regex:
// datetimeRegex = new RegExp("([0-9]{2})/([0-9]{2})/([0-9]{4})( ([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]+)?)?");
// datetimeRegex = new RegExp("([0-9]{2})/([0-9]{2})/([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]+)?|([0-9]{2})/([0-9]{2})/([0-9]{4})|([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]+)?");
//	define('SQL_DATETIME_REGEX'			,"(([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]+)?)");
//	define('SQL_DATE_REGEX'				,"(([0-9]{4})-([0-9]{2})-([0-9]{2}))");
//	define('SQL_TIME_REGEX'			,"(([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]+)?)");
//	define('DATETIME_REGEX'			,"((([0-9]{2})/([0-9]{2})/([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]+)?)|(([0-9]{2})/([0-9]{2})/([0-9]{4}))|(([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]+)?))");

// MODULE OPTIONS
define('ALWAYS_ACTIVE',flag_gen());
define('INSTALL_INACTIVE',flag_gen());
define('NO_HISTORY',flag_gen());
define('DISTINCT_ROWS',flag_gen());
define('ALLOW_FILTER',flag_gen());
define('ALLOW_EDIT',flag_gen());
define('ALLOW_ADD',flag_gen());
define('ALLOW_DELETE',flag_gen());
define('NO_NAV',flag_gen());
define('PERSISTENT',flag_gen());
define('PERSISTENT_PARENT',flag_gen());
define('SHOW_FUNCTIONS',flag_gen());
define('SHOW_TOTALS',flag_gen());
define('LIST_HIDE_HEADER',flag_gen());
define('LIST_HIDE_STATUS',flag_gen());

define('DEFAULT_OPTIONS',ALLOW_FILTER);

// START CLASSES

class uDataset {
	private $module = null;
	private $query = null;
	private $args = array();
	private $recordCount = null;
	
	public function __construct($query, $args, $module) {
		// initialise count
		$this->module = $module;
		$this->query = $query;
		$this->args = $args;
	}
	public function DebugDump() {
		return $this->query."\n".var_export($this->args,true);
	}
	
	public function CountPages($items_per_page=10) {
		return (int)ceil($this->CountRecords() / $items_per_page);
	}
	public function CountRecords() {
		if ($this->recordCount === null) $this->recordCount = (int)database::query('SELECT COUNT(*) FROM '.$this->query.' as `__`',$this->args)->fetchColumn();
		return $this->recordCount;
	}
	
	public function GetFirst() {
		$rows = $this->GetOffset(0,1);
		return reset($rows);
	}
	
	/* page is zero indexed */
	public function GetPage($page, $items_per_page=10) {
		$limit = '';
		if ($items_per_page > 0) {
			$start = $items_per_page * $page;
			$limit = ' LIMIT '.$start.','.$items_per_page;
		}
		return $this->GetOffset($start,$items_per_page);
	}
	
	public function GetOffset($offset,$count) {
		$limit = ' LIMIT '.$offset.','.$count;
		return $this->CreateRecords(database::query($this->query.$limit,$this->args)->fetchAll());
	}
	
	private $ds = null;
	public function fetch() { return $this->CreateRecord($this->ds()->fetch()); }
	public function fetchAll() { return $this->CreateRecords($this->ds()->fetchAll()); }
	public function ds() {
		if ($this->ds === null) $this->ds = database::query($this->query,$this->args);
		return $this->ds;
	}
	
	public function CreateRecords($rows) {
		if (!is_array($rows)) return $rows;
		$rc = count($rows);
		for ($i = 0; $i < $rc; $i++) {
			$rows[$i] = $this->CreateRecord($rows[$i]);
		}
		return $rows;
	}
	public function CreateRecord($row) {
		if (!is_array($row)) return $row;
		if (isset($row['__metadata']) && $row['__metadata']) {
			$meta = utopia::jsonTryDecode($row['__metadata']);
			if ($meta) $row = array_merge($row,$meta);
		}
		
		// make link tables into array
		foreach ($row as $field=>$val) {
			if (!isset($this->module->fields[$field])) continue;
			if (!is_string($val)) continue;
			if (strpos($val,"\x1F") === FALSE) continue;
			$fieldData = $this->module->fields[$field];
			if (!isset($fieldData['vtable'])) continue;
			if (!is_subclass_of($fieldData['vtable']['tModule'],'iLinkTable')) continue;
			$row[$field] = explode("\x1F",$val);
		}
		return $row;
	}
}


/**
 * Basic Utopia module. Enables use of adding parents and module to be installed and run.
 * No field or data access is available, use uDataModule and its decendants.
 */
abstract class uBasicModule implements iUtopiaModule {
/*  static $singleton = NULL;
  public static function &call($method) {
    $null = NULL;
    if (self::$singleton == NULL) { ErrorLog('Singleton not configured'); }//ErrorLog("Error Calling {$classname}->{$funcname}"); return $null;}
    if (!method_exists(self::$singleton,$method)) { return $null; }
        
    $stack = debug_backtrace();
    $args = array();
    if (isset($stack[0]["args"]))
      for($i=2; $i < count($stack[0]["args"]); $i++)
        $args[$i-2] = & $stack[0]["args"][$i];
    
    $call = array(self::$singleton,$funcname);
    $return = call_user_func_array($call,$args);
  
    return $return;
  }
	public static function __callStatic($name, $arguments) {
		// Note: value of $name is case sensitive.
		$instance =& utopia::GetInstance(get_class($this));
		return call_user_func_array(array($instance,$name),$arguments);
	}*/

	public function GetOptions() { return DEFAULT_OPTIONS; }

	public function GetTitle() { return get_class($this); }
	public function GetDescription() {}
	public function GetKeywords() {}

	public $tabGroup = NULL;
	
	private $isSecurePage = false;
	public function SetSecure() {
		$this->isSecurePage = true;
	}

	private $isInitialised = false;
	public function Initialise() {
		if ($this->isInitialised === TRUE) return false;
		$this->isInitialised = true;

		// setup parents
		$this->_SetupParents();
		return true;
	}

	public $isDisabled = false;
	public function DisableModule($message='') {
		if (!$message) $message = true;
		$this->isDisabled = $message;
	}

	private $loaded = array();
	public function LoadChildren($loadpoint) {
		$class = get_class($this);
		$children = utopia::GetChildren($class);

		$keys = array_keys($children);
		$size = sizeof($keys);

		foreach ($children as $child) {
			foreach ($child as $info) {
				if (!isset($info['callback'])) continue;
				if ($info['loadpoint'] !== $loadpoint) continue;

				if (!isset($this->loaded[$info['moduleName']])) {
					$this->loaded[$info['moduleName']] = true;
					$obj =& utopia::GetInstance($info['moduleName']);
					$result = $obj->LoadChildren(0);
					if ($result === FALSE) continue;
				}

				$result = call_user_func($info['callback'],$class);
				if ($result === FALSE) return FALSE;
			}
		}
		return true;
	}
	
	public function AssertURL($http_response_code = 301, $currentOnly = true) {
		if (!$currentOnly || get_class($this) == utopia::GetCurrentModule()) {
			$url = $this->GetURL($_GET);
			$checkurl = $_SERVER['REQUEST_URI'];
			if (($this->isSecurePage && !utopia::IsRequestSecure()) || $checkurl !== urldecode($url)) {
				$abs = '';
				if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') != $this->isSecurePage) {
					$layer = 'http';
					if ($this->isSecurePage) $layer .= 's';
					$abs = $layer.'://'.utopia::GetDomainName();
				}
				header('Cache-Control: no-store, no-cache, must-revalidate'); // don't cache redirects
				header('Location: '.$abs.$url,true,$http_response_code); die();
			}
		}
	}

	public $hasRun = false;
	public function _RunModule() {
		$this->AssertURL();
		
		if ($this->isDisabled) { echo $this->isDisabled; return; }

		if (uEvents::TriggerEvent('BeforeRunModule',$this) === FALSE) return FALSE;
		$lc = $this->LoadChildren(0);
		if ($lc !== TRUE && $lc !== NULL) return $lc;

		timer_start('Run Module');
		ob_start();
		if ($this instanceof iAdminModule) echo '<h1>'.$this->GetTitle().'</h1>';
		$result = $this->RunModule();
		$c = ob_get_contents();
		ob_end_clean();
		timer_end('Run Module');
		if (utopia::UsingTemplate()) $c = '<div class="'.get_class($this).'">'.$c.'</div>';
		echo $c;
		if ($result === FALSE) return false;
		$this->hasRun = true;

		if (uEvents::TriggerEvent('AfterRunModule',$this) === FALSE) return FALSE;
		$lc = $this->LoadChildren(1);
		if ($lc !== TRUE && $lc !== NULL) return $lc;
	}

	public $parentsAreSetup = false;
	public abstract function SetupParents();
	public function _SetupParents() {
		if ($this->parentsAreSetup) return;
		$this->parentsAreSetup = true;
		$this->SetupParents();
	}

	public $parents = array();
	public function HasParent($parentModule) {
		$children = utopia::GetChildren($parentModule);
		return array_key_exists(get_class($this),$children);
	}
	public function HasChild($childModule) {
		$children = utopia::GetChildren(get_class($this));
		return array_key_exists($childModule,$children);
	}

	public function AddParentCallback($parentModule,$callback,$loadpoint=1) {
		$info = array('moduleName'=>get_class($this), 'callback' => $callback, 'loadpoint' => $loadpoint);
		utopia::AddChild($parentModule,get_class($this),$info);
	}
	public function AddChildCallback($child,$callback,$loadpoint=1) {
		$info = array('moduleName'=>get_class($this), 'callback' => $callback, 'loadpoint' => $loadpoint);
		utopia::AddChild(get_class($this),$child,$info);
	}

	// parentModule =
	// sourceField = the field on the parent which the filter is taken from (generally the PK value of the current module)
	// destinationField = the field on the current module which the filter is set to.
	// parentField = the field which will be "linked from", if null or empty, a child button is created. allows easy injection to list forms.
	// EG:  'client list', array('client id'=>'client id'), 'client name' : applies a link from the "client name" field on the "client list" to open the current form where "client id" = "client id"
	// EG:  'client list', 'title', 'title id', 'title' : applies a link from the "title" field on the "client list" to open the current form where "title id" = "title"
	// EG:  'client form', 'client id', 'client id' : creates a button on the "client form" page to open the current form and where "client id" = "client id"

	// TODO: AddParent - overriden in data module
	// suggested change:  sourceField, destinationField become an array of "filters", eg: array("title" => "title id")
	/**
	 * Add a parent of this module, linked by a field.
	 *
	 * @param string $parentModule The classname of the parent
	 * @param string|array optional $fieldLinks
	 * @param string optional $parentField
	 * @param string optional $text
	 */
	public function AddParent($parentModule,$fieldLinks=NULL,$parentField=NULL,$text=NULL) {
		if (is_string($fieldLinks)) $fieldLinks = array(array('fromField'=>$fieldLinks,'toField'=>$fieldLinks,'ct'=>ctEQ));
		if (is_array($fieldLinks) && !array_key_exists(0,$fieldLinks)) {
			if (array_key_exists('fromField',$fieldLinks) && array_key_exists('toField',$fieldLinks)) {
				$fieldLinks = array($fieldLinks);
			} else {
				// change from array(from>to) to new format
				$newFL = array();
				foreach ($fieldLinks as $from => $to) {
					$newFL[] = array('fromField'=>$from,'toField'=>$to,'ct'=>ctEQ);
				}
				$fieldLinks = $newFL;
			}
		}

		if (is_array($fieldLinks)) {
			foreach ($fieldLinks as &$linkInfo) {
				if (empty($linkInfo['ct'])) $linkInfo['ct'] = ctEQ;
				if (is_subclass_of($this,'uDataModule')) {
					$fltr =& $this->FindFilter($linkInfo['toField'],$linkInfo['ct'],itNONE,FILTER_WHERE);
					if ($fltr === NULL) {
						$fltr =& $this->AddFilterWhere($linkInfo['toField'],$linkInfo['ct']);
						$uid = $fltr['uid'];
						//$fltr =& $this->GetFilterInfo($uid);
					} else $uid = $fltr['uid'];
					$fltr['linkFrom'] = $parentModule.':'.$linkInfo['fromField'];
					$linkInfo['_toField'] = $linkInfo['toField'];
					$linkInfo['toField'] = $uid;
				}
				//				if (is_numeric($key)) {
				//					unset($fieldLinks[$key]);
				//					$fieldLinks[$val] = $val;
			}	}//	}

	/*	if (utopia::GetCurrentModule() == get_class($this)) {
			if ($parentModule === '/') $pm = utopia::GetCurrentModule();
			else $pm = $parentModule;
			$filters = NULL;
			if ($fieldLinks && !$parentField) {
				$filters = array();
				foreach ($fieldLinks as $link) {
					//print_r($link);
					if (array_key_exists('_f_'.$link['toField'],$_GET))
						$filters[$link['fromField']] = $_GET['_f_'.$link['toField']];
				}
			}
			breadcrumb::AddModule($pm,$filters,0,utopia::GetCurrentModule());
		}*/

		//if (!array_key_exists('children',$GLOBALS)) $GLOBALS['children'] = array();
/*		$children = utopia::GetChildren($parentModule);
		// check parent field hasnt already been selected
		if ($parentField !== NULL && $parentField !== '*') {
			//if (array_key_exists($parentModule,$children))
			foreach ($children as $childName => $links) {
				foreach ($links as $link) {
					//if ($child['moduleName'] !== get_class($this)) continue;
					if (!isset($link['parentField'])) continue;
					if ($link['parentField'] == $parentField) {
						//trigger_error('Cannot add parent ('.$parentModule.') of '.get_class($this).', parentField ('.$parentField.') has already been defined in '.$child['moduleName'].'.',E_USER_ERROR);
						return;
					}
				}
			}
		}
*/

		if (!is_null($fieldLinks) && !is_array($fieldLinks)) // must be null or array
			trigger_error('Cannot add parent ('.$parentModule.') of '.get_class($this).', fieldLinks parameter is an invalid type.',E_USER_ERROR);

		$info = array('moduleName'=>get_class($this), 'parentField'=>$parentField, 'fieldLinks' => $fieldLinks, 'text' => $text);
		$this->parents[$parentModule][] = $info;
		utopia::AddChild($parentModule, get_class($this), $info);

		return $fieldLinks;
	}

	public function AddChild($childModule,$fieldLinks=NULL,$parentField=NULL,$text=NULL) {
		//$childModule = (string)$childModule;
		//echo "addchild $childModule<br/>";
		$obj =& utopia::GetInstance($childModule);
		$obj->AddParent(get_class($this),$fieldLinks,$parentField,$text);
	}

	/**
	 * Register this module to receive Ajax calls. Normal execution will be terminated in order to process the specified callback function.
	 *
	 * @param string $ajaxIdent
	 * @param string  $callback
	 * @param (bool|function) $requireLogin Either True or False for admin login, or a custom callback function.
	 * @return bool
	 */
	public function RegisterAjax($ajaxIdent, $callback) {
		return utopia::RegisterAjax($ajaxIdent, $callback);
	}

	public function GetVar($varname) {
		return $this->$varname;
	}
	public function GetUUID() {
		$uuid = preg_replace('((.{8})(.{4})(.{4})(.{4})(.+))','$1-$2-$3-$4-$5',md5(get_class($this)));
		return $uuid;
	}
  private $mID = NULL;
  public function GetModuleId() {
    if ($this->mID !== NULL) return $this->mID;
    $m = utopia::ModuleExists(get_class($this));
    $this->mID = $m['module_id'];
    return $this->mID;
  }
  
	public $rewriteMapping=NULL;
	public $rewriteURLReadable=NULL;
	public $rewritePersistPath=FALSE;
	public function HasRewrite($field = NULL) {
		if ($this->rewriteMapping === NULL) return false;
		if ($field === NULL) return $this->rewriteMapping !== NULL;

		foreach ($this->rewriteMapping as $key => $map) {
			if (strpos($map,'{'.$field.'}') !== FALSE) return true;
		}
		return false;
	}

	
	/**
	 * Indicate that this module should rewrite its URL
	 * @param mixed $mapping NULL to turn off rewriting. FALSE to strip all but uuid. TRUE to allow longer path.  ARRAY( '{fieldName}' , 'some-random-text' , '{another_field}' )
	 * @param bool $URLReadable specifies that all segments of the url should be stripped of non-alphanumeric characters.
	 */
	public function SetRewrite($mapping,$URLReadable = false) {
		if (getenv('HTTP_MOD_REWRITE')!='On') return false;
		if ($mapping === NULL) {
			$this->rewriteMapping = NULL; return;
		}
		if (is_string($mapping) && $mapping !== '') $mapping = array($mapping);
		if ($mapping === true) $this->rewritePersistPath = true;
		if (!is_array($mapping)) $mapping = array();

		// defaults?

		$this->rewriteMapping = $mapping;
		$this->rewriteURLReadable = $URLReadable;

		$this->ParseRewrite();
	}

	public function ParseRewrite($caseSensative = false) {
		if ($this->rewriteMapping === NULL) return FALSE;
		if (get_class($this) !== utopia::GetCurrentModule()) return FALSE;

		$uuid = $this->GetUUID(); if (is_array($uuid)) $uuid = reset($uuid);
		
		$sections = utopia::GetRewriteURL();
		$sections = preg_replace('/^'.preg_quote($uuid,'/').'\/?/','',$sections);
		$sections = explode('/',$sections);
		if (!$sections) return FALSE;
		
		$return = array('uuid'=>$uuid);
		foreach ($sections as $key => $value) {
			$replace = array();
			if (!array_key_exists($key,$this->rewriteMapping)) continue;
			$map = $this->rewriteMapping[$key];
			// generate preg for section
			if (preg_match_all('/{([a-zA-Z0-9_]+)}/',$map,$matches)) {
				foreach ($matches[1] as $match) {
					$map = str_replace('{'.$match.'}','(.+)',$map);
					$replace[] = $match;
				}
			}

			if (preg_match('/'.$map.'/',$value,$matches)) {
				unset($matches[0]);
				foreach($matches as $key => $match) {
				//	if ($match[0] == '{') continue;
					$return[$replace[$key-1]] = $match;
				}
			}
		}

		// TODO: named filters not being picked up
		$_GET = array_merge($_GET,$return);
		$_REQUEST = array_merge($_REQUEST,$return);
		return $return;
	}

	public function RewriteURL(&$filters) {
		$mapped = $this->rewriteMapping;
		foreach ($mapped as $key => $val) {
			if (preg_match_all('/{([a-zA-Z_]+)}/',$val,$matches)) {
				foreach ($matches[1] as $fieldName) {
					$newVal = '';
					if (array_key_exists($fieldName,$filters)) $newVal = $filters[$fieldName];
					elseif (array_key_exists('_f_'.$fieldName,$filters)) $newVal = $filters['_f_'.$fieldName];

					unset($filters[$fieldName]);
					unset($filters['_f_'.$fieldName]);

					$mapped[$key] = str_replace('{'.$fieldName.'}',$newVal,$mapped[$key]);
				}
			}
		}

		foreach ($mapped as $key => $val) {
			$URLreadable = is_array($this->rewriteURLReadable) ? $this->rewriteURLReadable[$key] : $this->rewriteURLReadable;
			$mapped[$key] = ($URLreadable) ? urlencode(UrlReadable($val)) : urlencode($val);
		}

		if (isset($filters['uuid'])) unset($filters['uuid']);
		$uuid = $this->GetUUID(); if (is_array($uuid)) $uuid = reset($uuid);
		array_unshift($mapped,$uuid);

		$newPath = PATH_REL_ROOT.join('/',$mapped);
		$oldPath = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
		if (strpos($oldPath,'/u/')===0) $oldPath = str_replace('/u/','/',$oldPath);

		if ($this->rewritePersistPath && utopia::GetCurrentModule() == get_class($this)) $newPath .= str_replace($newPath,'',$oldPath);

		// DONE: ensure all rewrite segments are accounted for (all '/' are present)
		return rtrim($newPath,'/');
	}

	public function GetURL($filters = NULL) {
		if (!is_array($filters)) $filters = array();

		$uuid = $this->GetUUID(); if (is_array($uuid)) $uuid = reset($uuid);
		unset($filters['uuid']);
		$filters = array('uuid'=>$uuid) + $filters;

		$url = DEFAULT_FILE;
		if ($this->rewriteMapping !== NULL)
			$url = $this->RewriteURL($filters);
		
		$query = http_build_query($filters);
		if ($query) $query = '?'.$query;
		
		return $url.$query;
	}
	public function IsInstalled() {
		return utopia::ModuleExists(get_class($this));
	}
	public function InstallModule() {
		// module should be installed
		// create a new record in "db_modules" - with UUID, module_parent, tablename, title
		$uuids = $this->GetUUID();
		if (!is_array($uuids)) $uuids = array($uuids);
		foreach ($uuids as $uuid) {
			//echo $uuid.'<br/>';
			$row = utopia::UUIDExists($uuid);
			if ($row === FALSE) {
				//echo "not installed:".get_class($this);
				//if (($row = $this->IsInstalled()) == FALSE) {
				//DebugMail('not installed',get_class($this));
				$active = flag_is_set($this->GetOptions(),INSTALL_INACTIVE) ? '0' : '1';
				database::query('INSERT INTO internal_modules (`uuid`,`module_name`,`module_active`) VALUES (?,?,?)',array($uuid,get_class($this),$active));
			} else {
				$qry = 'UPDATE internal_modules SET `uuid` = ?, `module_name` = ?, `sort_order` = ?';
				$args = array($uuid,get_class($this),$this->GetSortOrder);
				if (flag_is_set($this->GetOptions(),ALWAYS_ACTIVE))
					$qry .= ', `module_active` = 1';

				$qry .= ' WHERE `uuid = ?';
				$args[] = $row['uuid'];
				database::query($qry,$args);
			}
		}
		//		$GLOBALS['modules'][$this->GetUUID()] = get_class($this);
	}

	public function HookEvent($eventName,$funcName) {
		$GLOBALS['events'][$eventName][] = get_class($this).".$funcName";
	}

	public function GetSortOrder() {
		//if (is_object($module)) $module = get_class($module);
//		if (get_class($this) == utopia::GetCurrentModule()) return 1;
		return NULL;
	}
	//	public function __construct() { $this->_SetupFields(); } //$this->SetupParents(); }
	public abstract function RunModule();  // called when current_path = parent_path/<module_name>/


	public function CreateParentNavButtons($parentName) {
//		if ($this->navCreated) return;
		//ErrorLog(get_class($this).' making buttons on '.$parentName);
		if ($this->isDisabled) return;
		if (!is_array($this->parents)) return;
		if ($parentName !== utopia::GetCurrentModule()) return;
		if (!array_key_exists($parentName,$this->parents)) return;

//		$this->navCreated = true;
		$sortOrder = $this->GetSortOrder();
		$listDestination =  'child_buttons';
		if ($this instanceof iAdminModule) {
			if (get_class($this) == utopia::GetCurrentModule()) utopia::LinkList_Add($listDestination,'',NULL,-500);
			$sortOrder = $sortOrder - 1000;
		}

			if ($parentName == '/') return;
	//	$lm = utopia::GetVar('loadedModules',array());
	//	foreach ($this->parents as $parentName => $linkArray) {
//			$parentName = $linkArray['moduleName'];
			if (flag_is_set($this->GetOptions(),NO_NAV)) return;
	//		if (array_search($this,$lm,true) === FALSE) continue;

			$cModuleObj =& utopia::GetInstance(utopia::GetCurrentModule());
			if (($parentName != 'uDashboard' && ($obj instanceof iAdminModule)) && $parentName != utopia::GetCurrentModule()) return;
			//echo get_class($this).' '.$parentName.'<br/>';

			$parentObj =& utopia::GetInstance($parentName);
			if (($parentObj instanceof iAdminModule) && !($cModuleObj instanceof iAdminModule)) return;

			$linkArray = $this->parents[$parentName];
			foreach ($linkArray as $linkInfo) {
				if ($linkInfo['parentField'] !== NULL) continue; // has a parentField?  if so, ignore
				$btnText = !empty($linkInfo['text']) ? $linkInfo['text'] : $this->GetTitle();
				if (isset($linkInfo['fieldLinks']) && utopia::GetCurrentModule()) { // is linked to fields in the list
					$cr = $cModuleObj->GetCurrentRecord();
					if (is_array($linkInfo['fieldLinks']) && is_array($cr)) { // this link uses filters
						$filters = array();
						/*foreach ($linkInfo['fieldLinks'] as $fromField => $toField) {
							if ($this->GetFilterValue($toField)) // if existing record --
							$filters["filters[$toField]"] = $this->GetFilterValue($toField);
							}*/
						//print_r($linkInfo['fieldLinks']);
						foreach ($linkInfo['fieldLinks'] as $li) {
							//if ($this->GetFilterValue($this->FindFilter($li['fromField'],$li['ct']))) // if existing record --
							if (array_key_exists($li['fromField'],$cr))
							$filters["_f_".$li['toField']] = $cr[$li['fromField']];
						}
						utopia::LinkList_Add($listDestination,$btnText,$this->GetURL($filters),$sortOrder,NULL,array('class'=>'btn'));
					}
				} else { // not linked to fields (so no filters)
					utopia::LinkList_Add($listDestination,$btnText,$this->GetURL(),$sortOrder,NULL,array('class'=>'btn'));
					//	utopia::AppendVar('child_buttons',CreateNavButton($linkInfo['text'],$this->GetURL()));
				}
			}
		//}
	}
}

/**
 * Abstract class extending the basic module, adding data access and filtering.
 *
 */
abstract class uDataModule extends uBasicModule {
	public $fields = array();
	public $filters = array(FILTER_WHERE=>array(),FILTER_HAVING=>array());
	public $pk = NULL;
	public $pt = NULL;
	public $sqlTableSetupFlat = NULL;
	public $sqlTableSetup = NULL;
	public $dataset = NULL;
	public $currentRecord = NULL;

	public $hasEditableFilters = FALSE;

	public abstract function GetTabledef();

	public abstract function SetupFields();
	//public abstract function ShowData();//$customFilter = NULL);

	public $isAjax = true;
	
	public $fieldsSetup = FALSE;
	public function _SetupFields() {
		if ($this->fieldsSetup == TRUE) return;
		$this->fieldsSetup = TRUE;

		uEvents::TriggerEvent('BeforeSetupFields',$this);
		$this->SetupFields();
		$this->SetupUnionFields();
		if (is_array($this->UnionModules)) foreach ($this->UnionModules as $modulename) {
			$obj =& utopia::GetInstance($modulename);
			$obj->_SetupFields();
		}
		uEvents::TriggerEvent('AfterSetupFields',$this);
		
		$fields = $this->GetStringFields();
		$this->AddField('__global__','(CAST(CONCAT_WS(\' \','.$fields.') AS CHAR))','');
	}
	public function GetStringFields() {	
		$ignoreTypes = array(ftIMAGE,ftFILE);		
		$fields = array();
		foreach ($this->fields as $alias => $info) {
			if (in_array($this->GetFieldType($alias),$ignoreTypes)) continue;
			$str = $this->GetFieldLookupString($alias,$info);
			$fields[] = $str;
		}
		return implode(',',$fields);
	}

	public function ParseRewrite($caseSensative = false) {
		$this->_SetupFields();
		$parsed = parent::ParseRewrite($caseSensative);
		if (!$parsed) return FALSE;
		foreach ($parsed as $key => $val) {
			// check for filter with key
			$filter =& $this->FindFilter($key);
			if ($filter) $filter['value'] = $val;
		}
		return $parsed;
	}
	/*	public function RewriteURL($filters) {
		foreach ($filters as $key => $val) {
		$fltr =& $this->FindFilter($key);
		if ($fltr) {
		$filters['_f_'.$fltr['uid']] = $val;
		unset($filters[$key]);
		}
		}
		//print_r($filters);
		return parent::RewriteURL($filters);
		}*/

	public function MergeRewriteFilters(&$filters,$rec) {
		if (!is_array($filters)) return false;
		if (!$this->HasRewrite()) return false;
		if (!$rec) return false;
		foreach ($this->rewriteMapping as $seg) {
			if (preg_match_all('/{([a-zA-Z0-9_]+)}/',$seg,$matches)) {
				foreach ($matches[1] as $match) {
					if (isset($filters[$match])) continue;
					if (array_key_exists($match,$rec)) $filters[$match] = $rec[$match];
				}
			}
		}
		return true;
	}
	public function RewriteFilters(&$filters = NULL) {
		if (!is_array($filters)) return false;
		if (!$this->HasRewrite()) return false;
		foreach ($filters as $uid => $val) {
			$fltr = $this->GetFilterInfo(substr($uid,3));
			if (!$fltr) continue;
			if ($fltr['default'] == $fltr['value']) continue;
			$filters[$fltr['fieldName']] = $val;
			unset($filters[$uid]);
		}
		if (array_key_exists($this->GetPrimaryKey(), $filters)) {
			foreach ($this->rewriteMapping as $seg) {
				if (preg_match_all('/{([a-zA-Z0-9_]+)}/',$seg,$matches)) {
					foreach ($matches[1] as $match) {
						if (isset($filters[$match])) continue;
						$rec = $this->LookupRecord($filters[$this->GetPrimaryKey()],true);
						if (!$rec) return;
						$this->MergeRewriteFilters($filters,$rec);
						return;
					}
				}
			}
		}
		return true;
	}

	public function GetURL($filters = NULL) {
		$this->_SetupParents();
		$this->_SetupFields();
		if ($filters === FALSE) return parent::GetURL($filters);
		if (!is_array($filters) && $filters !== NULL) $filters = array($this->GetPrimaryKey()=>$filters);

		$this->RewriteFilters($filters);

		$filArr = array();
		if (is_array($filters)) foreach ($filters as $fieldName => $val) {
			$filArr[$fieldName] = $val;
		}

		foreach ($this->filters as $filterType) {
			foreach ($filterType as $filterSet) {
				foreach ($filterSet as $filter) {
					$val = $this->GetFilterValue($filter['uid']);
					if (isset($filters[$filter['fieldName']])) $val = $filters[$filter['fieldName']];
					
					if (!empty($filter['default']) && $val == $filter['default']) {
						unset($filters[$filter['fieldName']]);
						unset($filters['_f_'.$filter['uid']]);
						continue;
					}

					if (!$val) continue;
					if ($this->HasRewrite($filter['fieldName'])) {
						if (isset($filters[$filter['fieldName']])) continue;
						$filArr[$filter['fieldName']] = $val;
						continue;
					}
					$filArr['_f_'.$filter['uid']] = $val;
					unset($filArr[$filter['fieldName']]);
				}
			}
		}
		return parent::GetURL($filArr);
	}

	public function Initialise() {
		if (!parent::Initialise()) return false;
		$this->_SetupFields();
		return true;
	}

	public function GetEncodedFieldName($field,$pkValue=NULL) {
		$pk = is_null($pkValue) ? '' : "($pkValue)";
		return cbase64_encode(get_class($this).":$field$pk");
	}

	public function CreateSqlField($field,$pkValue) {
		return "usql-".$this->GetEncodedFieldName($field,$pkValue);
	}

  
	public function GetDeleteButton($pk,$btnText = NULL,$title = NULL) {
		$title = $title ? "Delete '$title'" : 'Delete Record';
		return '<a class="btn btn-del" name="'.$this->CreateSqlField('__u_delete_record__',$pk).'" href="#" title="'.$title.'">'.$btnText.'</a>';
	}

	public function DrawSqlInput($field,$defaultValue='',$pkValue=NULL,$attributes=NULL,$inputTypeOverride=NULL,$valuesOverride=NULL) {
		if ($attributes==NULL) $attributes = array();
		if (isset($this->fields[$field]['attr']))
			$attributes = array_merge($this->fields[$field]['attr'],$attributes);
		$inputType = $inputTypeOverride ? $inputTypeOverride : $this->fields[$field]['inputtype'];
		$length = $this->GetFieldProperty($field,'length') ? $this->GetFieldProperty($field,'length') : $this->GetTableProperty($field,'length');
		$values = $valuesOverride ? $valuesOverride : $this->GetValues($field,$pkValue);

		if (isset($this->fields[$field]['vtable']['parent']) && !is_subclass_of($this->fields[$field]['vtable']['tModule'],'iLinkTable') && $pkValue !== NULL) {
			foreach ($this->fields[$field]['vtable']['joins'] as $from=>$to) {
				if ($from == $field) {
					$rec = $this->LookupRecord($pkValue);
					$defaultValue = $rec[$this->GetPrimaryKeyField($field)];
					break;
				}
			}
		}

		$prefix = NULL;

		switch ($inputType) {
			case itMD5:
			case itPASSWORD:	$prefix = 'md5';
			case itTEXT:
			case itSUGGEST:
				//if (is_numeric($length) && $length > 0) $attributes['size'] = floor($length*0.75);
				break;
				//case itRICHTEXT:
			case itTEXTAREA:
			case itSUGGESTAREA:
				//$attributes['style'] = "width:100%";
				//if (is_numeric($length) && $length > 0) { $attributes['cols'] = floor($length*0.12); $attributes['rows'] = floor($length*0.01); }
				break;
			case itFILE:		$prefix = 'file'; break;
			break;
		}

		// its a suggest, so lv should be information to lookup with ajax
		// cannot place in switch due to spliting of shared properties (size/cols+rows)

		if ($inputType == itSUGGEST || $inputType == itSUGGESTAREA) {
			if (isset($values[$defaultValue])) $defaultValue = $values[$defaultValue];
			$values = cbase64_encode(get_class($this).':'.$field);
		}
		//		else // dont want to set onchange for suggestions
		//if (!array_key_exists('onchange',$attributes)) $attributes['onchange']='uf(this);';

		// styles
		$attributes['style'] = $this->FieldStyles_Get($field,$defaultValue);

		if (!array_key_exists('class',$attributes)) $attributes['class'] = '';
		if ($this->isAjax) $attributes['class'] .= ' uf';

		$fieldName = $this->CreateSqlField($field,$pkValue);
		if ($inputType == itFILE) $attributes['id'] = $fieldName;
		return utopia::DrawInput($fieldName,$inputType,$defaultValue,$values,$attributes);
	}

	public function GetPrimaryKeyField($fieldAlias=NULL) {
		if (!is_null($fieldAlias)) {
			return '_'.$this->GetFieldProperty($fieldAlias,'tablename').'_pk';
		}
		return '_'.$this->sqlTableSetup['alias'].'_pk';
	}
	public function GetPrimaryKeyTable($fieldAlias=NULL) {
		if (!is_null($fieldAlias)) {
			$setup = $this->sqlTableSetupFlat[$this->GetFieldProperty($fieldAlias,'tablename')];
			return $setup['pk'];
		}
		if ($this->pk == NULL && $this->GetTabledef() != NULL) {
			$obj =& utopia::GetInstance($this->GetTabledef());
			$this->pk = $obj->GetPrimaryKey();
		}
		return $this->pk;
	}
	public function GetPrimaryKey() {
		$pkT = $this->GetPrimaryKeyTable();
		foreach ($this->fields as $fieldAlias=>$info) {
			if ($info['field'] == $pkT && $this->FindFilter($fieldAlias)) return $fieldAlias;
		}
		return $this->GetPrimaryKeyTable();
	}

	public function GetPrimaryTable($fieldAlias=NULL) {
		if (!is_null($fieldAlias)) {
			$setup = $this->sqlTableSetupFlat[$this->GetFieldProperty($fieldAlias,'tablename')];
			return $setup['table'];
		}
		if ($this->pt == NULL) $this->pt = TABLE_PREFIX.$this->GetTabledef();
		return $this->pt;
	}

	public $UnionModules = NULL;
	public $UNION_MODULE = FALSE;
	public function AddUnionModule($modulename) {
		// check fields match! Union modules MUST have identical fields
		$this->UNION_MODULE = TRUE;
		if ($this->UnionModules == NULL) {
			$this->UnionModules = array();
			//			$this->_SetupFields();
			//			$this->AddField('__module__',"'".get_class($this)."'",'');
			//			$tbl = is_array($this->sqlTableSetup) ? $this->sqlTableSetup['alias'] : '';
			//			$this->AddField('__module_pk__',$this->GetPrimaryKey(),$tbl);
		}

		$this->UnionModules[] = $modulename;
		$obj =& utopia::GetInstance($modulename);
		$obj->UNION_MODULE = TRUE;
	}

	public function AddUnionParent($parentModule) {
		$obj =& utopia::GetInstance($parentModule);
		$obj->AddUnionModule(get_class($this));
	}

	public function SetupUnionFields() {
		if ($this->UNION_MODULE !== TRUE) return;
		$this->AddField('__module__',"'".get_class($this)."'",'');
		$tbl = is_array($this->sqlTableSetup) ? $this->sqlTableSetup['alias'] : '';
		$this->AddField('__module_pk__',$this->GetPrimaryKeyTable(),$tbl);
	}

	// this value will be used on new records or field updates
	public function SetDefaultValue($name,$moduleOrValue,$getField=NULL,$valField=NULL,$onlyIfNull=FALSE) { //,$whereField=NULL,$rowField=NULL) {
		if ($getField==NULL || $valField==NULL) {
			$this->SetFieldProperty($name,'default_value',$moduleOrValue);
		} else {
			$this->SetFieldProperty($name,'default_lookup',array('module'=>$moduleOrValue,'getField'=>$getField,'valField'=>$valField));
			// create a callback, when valField is updated, to set value of $name to the new DefaultValue (IF that value is empty?)
			if (!array_key_exists($valField,$this->fields) && get_class($this) != utopia::GetCurrentModule() && utopia::GetCurrentModule()) {
				$obj =& utopia::GetInstance(utopia::GetCurrentModule());
				$obj->AddOnUpdateCallback($valField,array($this,'RefreshDefaultValue'),$name,$onlyIfNull);
			} else
				$this->AddOnUpdateCallback($valField,array($this,'RefreshDefaultValue'),$name,$onlyIfNull);
		}
	}

	protected function RefreshDefaultValue($pkVal, $fieldName,$onlyIfNull) {
		AjaxEcho("// RefreshDefaultValue($fieldName,$onlyIfNull)\n");
		$row = $this->LookupRecord($pkVal);
		if ($row == NULL) return;
		if ($onlyIfNull && !empty($row[$fieldName])) return;
		//$pkVal = $row[$this->GetPrimaryKey()];
		$dVal = $this->GetDefaultValue($fieldName);
		//		echo "update $fieldName on $pkVal to:  $dVal";
		$this->UpdateField($fieldName,$dVal,$pkVal);
	}

	public function GetDefaultValue($name) {
		//		echo "GetDefaultValue($name)";
		// default value should take implicit value as priority over filter value?
		// NO implicit value takes priority
		// is d_v set?
		$dv = $this->GetFieldProperty($name,'default_value');
		if (!empty($dv)) return $dv;

		// is d_l set?  lookup value... lookup field from module,,, what about WHERE?
		$dl = $this->GetFieldProperty($name,'default_lookup');
		if (!empty($dl)) {
			// find the value of valField
			$row = $this->GetCurrentRecord();
			$lookupVal = $this->GetRealValue($dl['valField'],$row[$this->GetPrimaryKey()]);
			$obj =& utopia::GetInstance($dl['module']);
			$value = $obj->GetRealValue($dl['getField'],$lookupVal);

			return $value; //  process so only to return a string.... TO DO
		}

		// is filter set?
		//print_r($this->filters);
		//AjaxEcho("//no predifined default set, searching filters");
		$fltr =& $this->FindFilter($name,ctEQ);
		if ($fltr === NULL) return '';

		$uid = $fltr['uid'];
		$value = $this->GetFilterValue($uid);
		if (!empty($value)) return $value;

		return '';
		// not processed
		/*		foreach ($this->filters[FILTER_WHERE] as $filterset) {
			if (!array_key_exists($name,$filterset))continue;
			$filterData = $filterset[$name];
			if (empty($filterData)) continue;
			if ($filterData['ct'] != ctEQ) continue;
			return $filterData['value'];
			}

			foreach ($this->filters[FILTER_HAVING] as $filterset) {
			if (!array_key_exists($name,$filterset))continue;
			$filterData = $filterset[$name];
			if (empty($filterData)) continue;
			if ($filterData['ct'] != ctEQ) continue;
			return $filterData['value'];
			}*/
	}

	public function GetRootField($alias) {
		$fieldData = $this->fields[$alias];
		if (!array_key_exists('vtable',$fieldData) || !array_key_exists('joins',$fieldData['vtable'])) return $fieldData['field'];

		$vtable = $fieldData['vtable'];
		foreach ($vtable['joins'] as $fromField => $toField) {
			$obj =& utopia::GetInstance($vtable['tModule']);
			if ($toField == $obj->GetPrimaryKey()) return $fromField;
		}
	}

	//	private $rvCache = array();
	public function GetRealValue($alias,$pkVal, $useCache=true) {
		//echo "GetRealValue($alias,$pkVal)<br/>";
		//return "$alias:$pkVal";
		$this->_SetupFields();

		$field = $this->GetRootField($alias);
		if ($field == $this->fields[$alias]['field']) {
			//return "$alias";
			//$rec = $this->GetCurrentRecord();
			$rec = $this->LookupRecord($pkVal);
			return $rec;
			//return "grv $alias:$pkVal<br/>";
			//if ($rec[$this->GetPrimaryKey()] == $pkVal)
			return $rec[$alias];
		}


		//print_r($this->fields[$alias]);
		$table = $this->sqlTableSetup['table'];
		$pk = $this->sqlTableSetup['pk'];

		//$field = $this->fields[$alias]['field'];
		//$table = $this->fields[$alias]['vtable']['table'];
		//$pk = $this->fields[$alias]['vtable']['pk'];

		//		if (!$useCache || (!array_key_exists($table,$this->rvCache) || !array_key_exists($pkVal,$this->rvCache[$table]))) {
		$stm = database::query("SELECT $field FROM $table WHERE $pk = ?",array($pkVal));
		$row = $stm->fetch();
		return $row[$field];
	}

	public function GetLookupValue($alias,$pkValue) {
		if (empty($pkValue)) return;
		$this->_SetupFields();
		$fieldData = $this->fields[$alias];
		$str = $this->GetFieldLookupString($alias,$this->fields[$alias])." as `$alias`";

		$table = $fieldData['vtable']['table'];
		$pk = $fieldData['vtable']['pk'];

		$stm = database::query("SELECT $str FROM $table as {$fieldData['tablename']} WHERE $pk = ?",array($pkValue));
		$row = $stm->fetch();

		if (empty($row[$alias])) return $pkValue;

		return $row[$alias];
	}
	
	public function TableExists($alias) {
		return array_key_exists($alias,$this->sqlTableSetupFlat);
	}

	// fromField is localField, toField is parentField -- pending global rename
	//	public function CreateTable($alias, $tableModule=NULL, $parent=NULL, $fromField=NULL, $toField=NULL, $joinType='LEFT JOIN') {
	public function CreateTable($alias, $tableModule=NULL, $parent=NULL, $joins=NULL, $joinType='LEFT JOIN') {
		// nested array
		// first create the current alias
		if ($tableModule == NULL) $tableModule = $this->GetTabledef();

		//		$alias = strtolower($alias);
		//		$parent = strtolower($parent);
		//		$fromField = strtolower($fromField);
		//		$toField = strtolower($toField);
		if (!$this->sqlTableSetupFlat) $this->sqlTableSetupFlat = array();
		if ($this->TableExists($alias)) { throw new Exception("Table with alias '$alias' already exists."); return; }

		$tableObj =& utopia::GetInstance($tableModule);

		$newTable = array();
		$this->sqlTableSetupFlat[$alias] =& $newTable;
		$newTable['alias']	= $alias;
		$newTable['table']	= TABLE_PREFIX.$tableModule;
		$newTable['pk']		= $tableObj->GetPrimaryKey();
		$newTable['tModule']= $tableModule;
		
		if (!preg_match('/_ufullconcat$/',$alias)) // don't add pk field for 'ufull' concat fields
			$this->AddField('_'.$alias.'_pk',$newTable['pk'],$alias);
		//$this->AddFilter('_'.$alias.'_pk',ctEQ,itNONE);
		
		if ($parent==NULL) {
			if ($this->sqlTableSetup != NULL) {
				ErrorLog('Can only have one base table');
				return;
			}
			$this->sqlTableSetup = $newTable;

			$this->AddField($this->GetPrimaryKeyTable(),$this->GetPrimaryKeyTable(),$alias);
			$this->AddField('_module',"'".get_class($this)."'",$alias);
			$this->AddGrouping($this->GetPrimaryKeyTable());
			return;
		} else {
			$newTable['parent'] = $parent;
		}

		// $fromField in $this->sqlTableSetupFlat[$parent]['tModule']
		if (is_string($joins)) $joins = array($joins=>$joins);
		if (is_array($joins)) foreach ($joins as $fromField => $toField) {
			$tModObj =& utopia::GetInstance($this->sqlTableSetupFlat[$parent]['tModule']);
			if ($fromField[0] !== "'" && $fromField[0] !== '"' && stristr($fromField,'.') === FALSE &&
				$tModObj->GetFieldProperty($fromField,'pk') !== true &&
				$tModObj->GetFieldProperty($fromField,'unique') !== true &&
				$tModObj->GetFieldProperty($fromField,'index') !== true)
			ErrorLog("Field ($fromField) used as lookup but NOT an indexed field in table (".$this->sqlTableSetupFlat[$parent]['tModule'].").");
		}
		//$newTable['fromField'] = $fromField;
		//$newTable['toField'] = $toField;
		$newTable['parent']	= $parent;
		$newTable['joins'] = $joins;
		$newTable['joinType'] = $joinType;
		//		$this->sqlTableSetupFlat[$alias] = $newTable;

		// search through the table setup looking for the $linkFrom alias
		if (($srchParent =& recurseSqlSetupSearch($this->sqlTableSetup,$parent))) {
			// found, add it
			if (!array_key_exists('children',$srchParent)) $srchParent['children'] = array();
			$srchParent['children'][] = $newTable;
		} else {
			// not found.. throw error
			ErrorLog("Cannot find $parent");
		}
		if (is_subclass_of($tableModule,'iLinkTable') && !preg_match('/_ufullconcat$/',$alias)) {
			$this->CreateTable($alias.'_ufullconcat', $tableModule, $parent, $joins, $joinType);
		}
	}

	public function GetValues($alias,$pkVal=null) {
		if (!isset($this->fields[$alias])) {
			$fltr =& $this->FindFilter($alias);
			return $fltr['values'];
		}

		if (isset($this->fields[$alias]['values']) && is_callable($this->fields[$alias]['values'])) {
			return call_user_func_array($this->fields[$alias]['values'],array($this,$alias,$pkVal));
		}

		if (isset($this->fields[$alias]['values_cache'])) return $this->fields[$alias]['values_cache'];
		
		return $this->SetValuesCache($alias,$this->FindValues($alias,isset($this->fields[$alias]['values'])?$this->fields[$alias]['values']:NULL));
	}

	public function SetValuesCache($alias,$vals) {
		return $this->fields[$alias]['values_cache'] = $vals;
	}

	public function FindValues($aliasName,$values,$stringify = FALSE) {
		if (is_callable($values)) return $values;

		$arr = NULL;

		if (is_array($values)) {
			$arr = $values;
		} elseif (IsSelectStatement($values)) {
			$arr = array();
			$result = database::query($values);
			while ($result != false && (($row = $result->fetch(PDO::FETCH_NUM)) !== FALSE)) {
				if (isset($row[1])) {
					// key value pair
					$arr[$row[0]] = $row[1];
				} else {
					$arr[$row[0]] = $row[0];
				}
			}
		} elseif (($values===true || is_string($values)) && isset($this->fields[$aliasName]['vtable'])) {
			$tbl = $this->fields[$aliasName]['vtable'];
			$obj =& utopia::GetInstance($tbl['tModule']);
			$pk = $obj->GetPrimaryKey();
			$table = $tbl['table'];
			$arr = GetPossibleValues($table,$pk,$this->fields[$aliasName]['field'],$values);
			if ($this->fields[$aliasName]['tablename'] === $this->sqlTableSetup['alias'] && $arr) 
				$arr = array_combine(array_values($arr),array_values($arr));
		}
		
		if ($stringify && is_array($arr) && $arr) {
			$arr = array_combine(array_values($arr),array_values($arr));
		}
		return $arr;
	}

	public function SetFieldOptions($alias,$newoptions) {
		$this->SetFieldProperty($alias,'options',$newoptions);
	}

	private $spacerCount = NULL;
	public function AddSpacer($text = '&nbsp;',$titleText = '') {
		if ($this->spacerCount === NULL) $this->spacerCount = 0;
		$this->AddField("__spacer_{$this->spacerCount}__","'$text'",'',"$titleText");
		$this->spacerCount = $this->spacerCount + 1;
	}

	public function CharAlign($alias,$char) {
		$this->fields[$alias]['charalign'] = $char;
	}

	public function SetFormat($alias,$format,$condition = TRUE) {
		$this->fields[$alias]['formatting'][] = array('format' =>$format, 'condition' => $condition);
	}

	public $layoutSections = array();
	public $cLayoutSection = null;
	public function NewSection($title,$order = null) {
		if ($order === null) $order = count($this->layoutSections);
		$a = array('title'=>$title,'order'=>$order);
		$found = false;
		foreach ($this->layoutSections as $k => $i) {
			if ($i['title'] == $title) {
				$this->layoutSections[$k] = $a; $found = $k; break;
			}
		}
		$this->cLayoutSection = $found;
		if ($found === FALSE) {
			$this->layoutSections[] = $a;
			$this->cLayoutSection = count($this->layoutSections) -1;
		}
		array_sort_subkey($this->layoutSections,'order');
	}
	public function RenameSection($old,$new) {
		foreach ($this->layoutSections as $k => $i) {
			if ($i['title'] == $old) {
				$this->layoutSections[$k]['title'] = $new; break;
			}
		}
	}

	private $defaultStyles = array();
	public function FieldStyles_SetDefault($inputType,$style) {
		if (!is_array($style)) { ErrorLog("Field Style is not an array ($field)"); return; }
		$this->defaultStyles[$inputType] = $style;
	}

	public function FieldStyles_Set($field,$style) {
		if (!is_array($style)) { ErrorLog("Field Style is not an array ($field)"); return; }
		$this->fields[$field]['style'] = $style;
	}

	public function FieldStyles_Add($field,$style) {
		if (!is_array($style)) { ErrorLog("Field Style is not an array ($field)"); return; }
		foreach ($style as $key=>$val)
		$this->fields[$field]['style'][$key] = $val;
	}

	public function FieldStyles_Unset($field,$key) {
		unset($this->fields[$field]['style'][$key]);
	}

	public function FieldStyles_Get($field,$value=NULL) {
		if (strpos($field,':') !== FALSE) list($field) = explode(':',$field);
		if (!isset($this->fields[$field])) return null;
		$inputType = $this->fields[$field]['inputtype'];
		$defaultStyles = array_key_exists($inputType,$this->defaultStyles) ? $this->defaultStyles[$inputType] : array();
		$specificStyles = $this->GetFieldProperty($field,'style'); if (!$specificStyles) $specificStyles = array();
		$conditionalStyles = array();

		if (array_key_exists('style_fn',$this->fields[$field]) && is_callable($this->fields[$field]['style_fn'][0])) {
			$arr = $this->fields[$field]['style_fn'][1];
			if (is_array($arr)) array_unshift($arr,$value); else $arr = array($value);
			$conditionalStyles = call_user_func_array($this->fields[$field]['style_fn'][0],$arr);
		}
		if (!$conditionalStyles) $conditionalStyles = array();

		$styles = array_merge($defaultStyles,$specificStyles,$conditionalStyles);
		if ($inputType == itDATE && !array_key_exists('width',$styles)) $styles['width'] = '8.5em';
		
		// if width/height has no delimiter, append 'px'
		if (isset($styles['width']) && is_numeric($styles['width'])) $styles['width'] = $styles['width'].'px';
		if (isset($styles['height']) && is_numeric($styles['height'])) $styles['height'] = $styles['height'].'px';
		
		return $styles;
	}

	public function ConditionalStyle_Set($field,$callback) {
		$numargs = func_num_args();
		$arr = array();
		for ($i = 2; $i < $numargs; $i++)
		$arr[] = func_get_arg($i);
		$this->fields[$field]['style_fn'] = array($callback,$arr);
	}

	public function ConditionalStyle_Unset($field) {
		unset($this->fields[$field]['style_fn']);
	}

	public function SetMetaValue($fieldAlias,$newValue,&$pkVal=NULL) {
		if ($pkVal == NULL) {
			$newValue = json_encode(array($fieldAlias=>$newValue));
		} else {
			$rec = $this->LookupRecord($pkVal);
			$metadata = json_decode($rec['__metadata'],true);
			$metadata[$fieldAlias] = $newValue;
			$newValue = json_encode($metadata);
		}
		return $this->UpdateField('__metadata',$newValue,$pkVal);
	}

	private $includeMeta = false;
	public function AddMetaField($name,$visiblename=NULL,$inputtype=itNONE,$values=NULL) {
		if (!$this->includeMeta) {
			$this->includeMeta = true;
			$this->AddField('__metadata','__metadata');
		}
		$this->AddField($name,"''",NULL,$visiblename,$inputtype,$values);
		$this->fields[$name]['ismetadata'] = true;
	}

	public function AddForeignField($aliasName,$fieldName,$tableAlias=NULL,$visiblename=NULL,$inputtype=itNONE,$values=NULL) {
		$this->AddField($aliasName,$fieldName,$tableAlias,$visiblename,$inputtype,$values);
		$this->fields[$aliasName]['foreign'] = true;
	}
	public function &AddField($aliasName,$fieldName,$tableAlias=NULL,$visiblename=NULL,$inputtype=itNONE,$values=NULL) {//,$options=0,$values=NULL) {
		$this->_SetupFields();
		if ($tableAlias === NULL) $tableAlias = $this->sqlTableSetup['alias'];
		
		$this->fields[$aliasName] = array(
			'alias'       => $aliasName,
			'tablename'   => $tableAlias,
			'visiblename' => $visiblename,
			'inputtype'   => $inputtype,
			'options'     => ALLOW_ADD | ALLOW_EDIT, // this can be re-set using $this->SetFieldOptions
			'field'       => $fieldName,
			'foreign'     => false,
		);
		$this->fields[$aliasName]['order'] = count($this->fields);
		
		$before = $this->insertBefore;
		if (is_string($before) && isset($this->fields[$before])) {
			$this->fields[$aliasName]['order'] = $this->fields[$before]['order'] - 0.5;
		}

		if (is_array($fieldName)) {
			$this->fields[$aliasName]['field'] = "";
			$this->AddPreProcessCallback($aliasName, $fieldName);
		}
		if ($tableAlias) $this->fields[$aliasName]['vtable'] = $this->sqlTableSetupFlat[$tableAlias];

		switch ($this->GetFieldType($aliasName)) {
			case ftFILE:
			case ftIMAGE:
				$this->AddField($aliasName.'_filename', $fieldName.'_filename', $tableAlias);
				$this->AddField($aliasName.'_filetype', $fieldName.'_filetype', $tableAlias);
				break;
			case ftCURRENCY:
				$list = uLocale::ListLocale();
				$this->AddField($aliasName.'_locale', $fieldName.'_locale', $tableAlias,(count($list)>1 ? 'Currency' : NULL),itCOMBO,$list);
				$this->AddPreProcessCallback($aliasName,array('utopia','convCurrency'));
				break;
			case ftDATE:
				$this->AddPreProcessCallback($aliasName,array('utopia','convDate'));
				break;
			case ftTIME:
				$this->AddPreProcessCallback($aliasName,array('utopia','convTime'));
				break;
			case ftDATETIME:
			case ftTIMESTAMP:
				$this->AddPreProcessCallback($aliasName,array('utopia','convDateTime'));
				break;
		}
		// values here
		if ($values === NULL) switch ($inputtype) {
			//case itNONE: // commented to prevent huge memory usage on BLOB fields.  Set Values to true if you need it!
			case itCOMBO:
			case itOPTION:
			case itSUGGEST:
			case itSUGGESTAREA:
				$values = true;
			default:
				break;
		}
		$this->fields[$aliasName]['values'] = $values;

		if ($visiblename !== NULL) {
			if (!$this->layoutSections) $this->NewSection('');
			$this->fields[$aliasName]['layoutsection'] = $this->cLayoutSection;
		}
		return $this->fields[$aliasName];
	}
	private $insertBefore = null;
	public function SetAddFieldPosition($before=null) {
		$this->insertBefore = $before;
	}

	public function GetFields($visibleOnly=false,$layoutSection=NULL) {
		$arr = $this->fields;
		array_sort_subkey($arr,'order');
		foreach ($arr as $fieldName=>$fieldInfo) {
			if ($visibleOnly && $fieldInfo['visiblename']===NULL) unset($arr[$fieldName]);
			if ($layoutSection !== NULL && array_key_exists('layoutsection',$fieldInfo) && $fieldInfo['layoutsection'] !== $layoutSection) unset($arr[$fieldName]);
		}
		return $arr;
	}

	public function AssertField($field,$reference = NULL) {
		if (isset($this->fields[$field])) return TRUE;
		if (IsSelectStatement($field)) return FALSE;

		$found = useful_backtrace(0);

		ErrorLog("Field ($field) does not exist for {$found[0][0]}:{$found[0][1]} called by {$found[1][0]}:{$found[1][1]}.".($reference ? " Ref: $reference" : ''));
	}

	public function AddOnUpdateCallback($alias,$callback) {
		$this->_SetupFields();
		if (!$this->AssertField($alias)) return FALSE;
		//        echo "add callback ".get_class($this).":$alias";
		$numargs = func_num_args();
		$arr = array();
		for ($i = 2; $i < $numargs; $i++)
		$arr[] = func_get_arg($i);
		$this->fields[$alias]['onupdate'][] = array($callback,$arr);
		return TRUE;
	}

	/** Instructs the core to send information to a custom function.
	 * Accepts any number of args, passed to the callback function when the PreProcess function is called.
	 * --  callback($fieldValue,...)
	 * @param string Field Alias to attach the callback to.
	 * @param function Reference to the function to call.
	 * @param ... additional data
	 */
	public function AddPreProcessCallback($alias,$callback) {
		if (!$this->FieldExists($alias)) return FALSE;
		if (count($callback) > 2) {
			$arr = array_splice($callback,2);
		} else {
			$numargs = func_num_args();
			$arr = array();
			for ($i = 2; $i < $numargs; $i++)
				$arr[] = func_get_arg($i);
		}
		$this->fields[$alias]['preprocess'][] = array($callback,$arr);
	}

	public function AddDistinctField($alias) {
		if (!$this->AssertField($alias)) return FALSE;
		// get original field
		$original = $this->fields[$alias];
		$this->AddField("distinct_$alias",'count('.$original['tablename'].'.'.$original['field'].')','',"count($alias)");
		$this->AddFilter("distinct_$alias",ctGTEQ,itNONE,1);
		//	$this->AddGrouping("$alias");
	}

	public $grouping = NULL;
	public function AddGrouping($alias,$clear = false) {
		if (!$this->grouping || $clear) $this->grouping = array();
		//	$this->grouping[] = (array_key_exists($alias,$this->fields) ? $this->fields[$alias]['tablename'].'.' : '')."$alias $direction";
		$this->grouping[] = "`$alias`";
	}

	public $ordering = NULL;
	public function AddOrderBy($fieldName,$direction = 'ASC') {
		$fieldName = trim($fieldName);
		if ($this->ordering === NULL) $this->ordering = array();

		if (strpos($fieldName,',') !== FALSE) {
			foreach(explode(',',$fieldName) as $f) {
				$f = trim($f);
				$dir = 'ASC';
				if (strpos($f,' ') !== FALSE) list($f,$dir) = explode(' ',$f);
				$this->AddOrderBy($f,$dir);
			}
			return;
		}
		if (strpos($fieldName,' ') !== FALSE) {
			list($fieldName,$direction) = explode(' ',$fieldName);
			$fieldName = trim($fieldName);
			$direction = trim($direction);
		}
		
		if ($this->FieldExists($fieldName)) $fieldName = "`$fieldName`";
		$this->ordering[] = "$fieldName $direction";
	}

	/*  FILTERS */
	// Filters work on a set/rule basis,  each set contains rules which are AND'ed, and each set is then OR'd together this creates full flexibility with filtering the results.
	// EG: (set1.rule1 and set1.rule2) or (set2.rule1)
	// create a new set by calling $this->NewFilterset();
	public function NewFiltersetWhere() {
		$this->filters[FILTER_WHERE][] = array();
	}
	public function NewFiltersetHaving() {
		$this->filters[FILTER_HAVING][] = array();
	}
	public function ClearFilters() {
		$this->filters = array(FILTER_WHERE=>array(),FILTER_HAVING=>array());
	}

	public function &AddFilterWhere($fieldName,$compareType,$inputType=itNONE,$value=NULL,$values=NULL,$title=NULL) {
		//	if (!array_key_exists($fieldName,$this->fields) && $inputType !== itNONE) { ErrorLog("Cannot add editable WHERE filter on field '$fieldName' as the field does not exist."); return; }
		if (!isset($this->filters[FILTER_WHERE]) || count(@$this->filters[FILTER_WHERE]) == 0) $this->NewFiltersetWhere();
		return $this->AddFilter_internal($fieldName,$compareType,$inputType,$value,$values,FILTER_WHERE,$title);
	}

	public function &AddFilter($fieldName,$compareType,$inputType=itNONE,$value=NULL,$values=NULL,$title=NULL) {
		if (preg_match_all('/{([^}]+)}/',$fieldName,$matches)) {
			foreach ($matches[1] as $match) {
				if (isset($this->fields[$match])) return $this->AddFilterWhere($fieldName,$compareType,$inputType,$value,$values,$title);
			}
		}

		if (array_key_exists($fieldName,$this->fields) && stripos($this->fields[$fieldName]['field'],' ') === FALSE && !$this->UNION_MODULE && isset($this->fields[$fieldName]['vtable']))
			return $this->AddFilterWhere($fieldName,$compareType,$inputType,$value,$values,$title);

		//	if (!array_key_exists($fieldName,$this->fields)) { ErrorLog("Cannot add HAVING filter on field '$fieldName' as the field does not exist.");ErrorLog(print_r(useful_backtrace(),true)); return; }
		if (!isset($this->filters[FILTER_HAVING]) || count(@$this->filters[FILTER_HAVING]) == 0) $this->NewFiltersetHaving();
		return $this->AddFilter_internal($fieldName,$compareType,$inputType,$value,$values,FILTER_HAVING,$title);
	}

	private $filterCount = 0;
	public function GetNewUID($fieldName) {
		$this->filterCount++;
		return $this->GetModuleId().'_'.$this->filterCount;
	}

	// private - must use addfilter or addfilterwhere.
	private function &AddFilter_internal($fieldName,$compareType,$inputType=itNONE,$dvalue=NULL,$values=NULL,$filterType=NULL,$title=NULL) {
		// if no filter, or filter has default, or filter is link - create new filter
		$fd =& $this->FindFilter($fieldName,$compareType,$inputType);
		if (!$fd || $fd['default'] !== NULL) {
			$uid = $this->GetNewUID($fieldName);
			if ($filterType == NULL) // by default, filters are HAVING unless otherwise specified
				$filterset =& $this->filters[FILTER_HAVING];
			else
				$filterset =& $this->filters[$filterType];

			if ($filterset == NULL) $filterset = array();  // - now manually called NewFilterset####()

			$fieldData = array();
			$fieldData['uid'] = $uid;
			$filterset[count($filterset)-1][] =& $fieldData;
		}  else $fieldData =& $fd;
		
		if ($values === NULL) switch ($inputType) {
			case itNONE:
			case itCOMBO:
			case itOPTION:
			case itSUGGEST:
			case itSUGGESTAREA:
				$values = true;
			default:
				break;
		}

		$value = $dvalue;
//		if ($inputType !== itNONE && $value === NULL && array_key_exists($fieldName,$_GET))
//			$value = $_GET[$fieldName];
		
		$fieldData['fieldName'] = $fieldName;
		$fieldData['type'] = $filterType;

		$fieldData['ct'] = $compareType;
		$fieldData['it'] = $inputType;

		$fieldData['title']= $title;
		$fieldData['values']= $values;
		$fieldData['default'] = $dvalue;
		$fieldData['value'] = $value;

		if ($inputType != itNONE) $this->hasEditableFilters = true;

		return $fieldData;//['uid'];
	}

	// returns false if filter not found, otherwise returns filter information as array
	public function HasFilter($field) {
		if (isset($this->filters[FILTER_HAVING]))
		foreach ($this->filters[FILTER_HAVING] as $filterset) {
			if (isset($filterset[$field])) return $filterset[$field];
		}
		if (isset($this->filters[FILTER_WHERE]))
		foreach ($this->filters[FILTER_WHERE] as $filterset) {
			if (isset($filterset[$field])) return $filterset[$field];
		}

		return false;
	}

	public function FieldExists($fieldName) {
		return isset($this->fields[$fieldName]);
	}

	public function SetFieldProperty($fieldName,$propertyName,$propertyValue) {
		if (!isset($this->fields[$fieldName])) { ErrorLog(get_class($this)."->SetFieldProperty($fieldName,$propertyName). Field does not exist."); return; }
		$this->fields[$fieldName][$propertyName] = $propertyValue;
	}

	public function GetFieldProperty($fieldName,$propertyName) {
		//		if (!$this->AssertField($fieldName)) return NULL;
		if (!isset($this->fields[$fieldName])) return NULL;
		if (!isset($this->fields[$fieldName][$propertyName])) return NULL;

		return $this->fields[$fieldName][$propertyName];//[strtolower($propertyName)];
	}

	public function SetFieldType($alias,$type) {
		$this->_SetupFields();
		$ret = $this->GetFieldType($alias);
		$this->SetFieldProperty($alias,'datatype',$type);
		return $ret;
	}

	public function GetFieldType($alias) {
		$type = $this->GetFieldProperty($alias,'datatype');
		if (!$type) $type = $this->GetTableProperty($alias,'type');

		return $type;
	}

	public $hideFilters = FALSE;
	public function HideFilters() {
		$this->hideFilters = TRUE;
	}

	public function GetFieldLookupString($alias,$fieldData) {
		$fieldName = $fieldData['field'];
		if (empty($fieldName)) return "''";
		if ($fieldData['tablename'] === NULL) return;

		/* THIS FUNCTION HAS BEEN SIMPLIFIED!
		 * field is ReplacePragma if field has "is_function" property, or starts with "(", single quote, or double quote
		 * else, field is CONCAT
		 */
		 
		$chr1 = substr($fieldName,0,1);
		if (isset($fieldData['vtable']) && is_subclass_of($fieldData['vtable']['tModule'],'iLinkTable') && $this->sqlTableSetup['alias'] !== $fieldData['tablename']) {
			$toAdd = 'GROUP_CONCAT(DISTINCT `'.$fieldData['tablename'].'_ufullconcat`.`'.$fieldName.'` SEPARATOR 0x1F)';
		} elseif (!preg_match('/{[^}]+}/',$fieldData['field'])) {
			if ($chr1 == '(' || $chr1 == "'" || $chr1 == '"')
				$toAdd = $fieldData['field'];
			else
				$toAdd = "`{$fieldData['tablename']}`.`{$fieldData['field']}`";
		} elseif ($this->GetFieldProperty($alias, 'is_function') || $chr1 == '(' || $chr1 == "'" || $chr1 == '"') {
			$toAdd = ReplacePragma($fieldData['field'], $fieldData['tablename']);
		} else {
			$toAdd = CreateConcatString($fieldName, $fieldData['tablename']);
		}

		return "$toAdd";
	}

	public function GetFromClause() {
		$from = "{$this->sqlTableSetup['table']} AS {$this->sqlTableSetup['alias']}";
		$paraCount = parseSqlTableSetupChildren($this->sqlTableSetup,$from);
		//		for ($i = 0; $i < $paraCount; $i++)
		//			$from = '('.$from;
		if ($from == ' AS ') return '';
		return $from;
	}

	public function GetSelectStatement() {
		// init fields, get primary key, its required by all tables anyway so force it...
		//grab the table alias and primary key from the alias's tabledef

		$flds = array();

		//		$tblJoins = array();
		//		$tblInc = 1;

		foreach ($this->fields as $alias => $fieldData) {
			$str = $this->GetFieldLookupString($alias,$fieldData);
			if (!empty($str)) $flds[] = $str." as `$alias`";
		}

		// table joins should be grouped by the link field.... eg:
		// if table1.field1 is linked to table2.field2 in 2 lookups
		// there is no need to create 2 table aliases, as they will return the same result set.


		//		$joins = '';
		//		foreach ($tblJoins as $tblJoin) {
		//			$joins .= " LEFT OUTER JOIN {$tblJoin['table']} {$tblJoin['ident']} ON {$tblJoin['ident']}.{$tblJoin['lookup']} = ".$this->GetPrimaryTable().".{$tblJoin['linkField']} ";
		//		}

		// now create function to turn the sqlTableSetup into a FROM clause
		$from = $this->GetFromClause();

		$distinct = flag_is_set($this->GetOptions(),DISTINCT_ROWS) ? ' DISTINCT' : '';
		$qry = "SELECT$distinct ".join(",\n",$flds);
		if ($from) $qry .= " \nFROM ".$from;//$this->GetPrimaryTable().$joins;
		return $qry;
	}

	public function &FindFilter($fieldName, $compareType=NULL, $inputType = NULL, $set = NULL) {
		//$this->SetupParents();
		//		$this->_SetupFields();
		//		echo "FindFilter($fieldName, $compareType, $inputType): ";
		//print_r($this->filters);
		foreach ($this->filters as $ftypeID => $filterType) {
			if ($set && $ftypeID !== $set) continue;
			foreach ($filterType as $fsetID => $filterset) {
				if (is_array($filterset)) foreach ($filterset as $arrID => $filterInfo) {
					if ($filterInfo['fieldName'] != $fieldName) continue;
					if ($compareType !== NULL && $filterInfo['ct'] !== $compareType) continue;
					if ($inputType !== NULL && $filterInfo['it'] !== $inputType) continue;
					return $this->filters[$ftypeID][$fsetID][$arrID];
				}
			}
		}
		//		echo "not found<br/>";
		$null = NULL;
		return $null;
	}

	public function &GetFilterInfo($uid) {
		//		echo get_class($this).".GetFilterInfo($uid)<br/>";
		foreach ($this->filters as &$filterTypeArray) {
			foreach ($filterTypeArray as &$filterset) {
				if (!is_array($filterset)) continue;
				foreach ($filterset as &$filterInfo) {
					if ($filterInfo['uid'] == $uid)	return $filterInfo;
				}
			}
		}
		$null = NULL;
		return $null;
	}
	
	public function RemoveFilter($uid) {
		foreach ($this->filters as &$filterTypeArray) {
			foreach ($filterTypeArray as &$filterset) {
				if (!is_array($filterset)) continue;
				foreach ($filterset as $k => &$filterInfo) {
					if ($filterInfo['uid'] == $uid)	{
						unset($filterset[$k]);
						return true;
					}
				}
			}
		}
	}

	public function GetFilterValue($uid, $refresh = FALSE) {
		$filterData = $this->GetFilterInfo($uid);
		if (!is_array($filterData)) return NULL;

		// ptime static filter value
		// this line grabs STATIC filters (filters set by code), this enforced if the input type is null
		//if (isset($filterData['default'])) $defaultValue = $filterData['default'];
		$defaultValue = array_key_exists('value',$filterData) ? $filterData['value'] : $filterData['default'];

		// for union modules, we cannot get a value form currentmodule because it is itself, part of the query
		if ($filterData['it'] === itNONE && utopia::GetCurrentModule() !== get_class($this) && (!isset($this->UNION_MODULE) || $this->UNION_MODULE !== TRUE)) {
			if (array_key_exists('linkFrom',$filterData)) {
				list($linkParent,$linkFrom) = explode(':',$filterData['linkFrom']);
				// linkparent is loaded?  if not then we dont really want to use it as a filter.....
				if ($linkParent == utopia::GetCurrentModule()) {
					$linkParentObj =& utopia::GetInstance($linkParent);
					$row = $linkParentObj->GetCurrentRecord($refresh);
					if (!$row && !$refresh) $row = $linkParentObj->GetCurrentRecord(true);

					if (is_array($row) && array_key_exists($linkFrom,$row)) {
						return $row[$linkFrom];
					} else {// if the filter value of the parent is null (if we're updating for example), then we want to get the value of the filter
						$fltrLookup =& $linkParentObj->FindFilter($linkFrom,ctEQ);
						$val = NULL;
						// stop lookup callbacks
						if (is_array($fltrLookup) && array_key_exists('linkFrom',$fltrLookup) && stristr($fltrLookup['linkFrom'],get_class($this)) === FALSE)
							$val = $linkParentObj->GetFilterValue($fltrLookup['uid']);

						if ($val!==NULL) return $val;
					}
				}
			}
		}
		
		$filters = GetFilterArray();
		if (isset($filters[$uid])) return $filters[$uid];

		return $defaultValue;
	}

	public function GetTableProperty($alias,$property) {
		if (!isset($this->fields[$alias])) return NULL;
		if (!isset($this->fields[$alias]['vtable'])) return NULL;

		$tabledef = $this->fields[$alias]['vtable']['tModule'];
		$fieldName = $this->fields[$alias]['field'];
		
		$obj =& utopia::GetInstance($tabledef);
		return $obj->GetFieldProperty($fieldName,$property);
	}

	// filterSection = [where|having]

	public function FormatFieldName($fieldName, $fieldType = NULL) {
		if ($fieldType === NULL)
		$fieldType = $this->GetFieldType($fieldName);

		// do field
		switch ($fieldType) {
			case ('date'): $fieldName = "(STR_TO_DATE($fieldName,'".FORMAT_DATE."'))"; break;
			case ('time'): $fieldName = "(STR_TO_DATE($fieldName,'".FORMAT_TIME."'))"; break;
			case ('datetime'):
			case ('timestamp'): $fieldName = "(STR_TO_DATE($fieldName,'".FORMAT_DATETIME."'))"; break;
			default: break;
		}
		return $fieldName;
	}

	public function GetFilterString($uid,&$args,$fieldNameOverride=NULL,$fieldTypeOverride=NULL){//,$filterSection) {
		$filterData = $this->GetFilterInfo($uid);
		if (!is_array($filterData)) return '';
		$fieldName = $fieldNameOverride ? $fieldNameOverride : $filterData['fieldName'];
		$compareType=$filterData['ct'];
		$inputType=$filterData['it'];

		if ($compareType === ctIGNORE) return '';
		
		$value = $this->GetFilterValue($uid);//$filterData['value'];

		$fieldToCompare = $fieldName;
		
		if (is_callable($fieldToCompare)) return call_user_func_array($fieldToCompare,array($value,&$args));

		if ($filterData['type'] == FILTER_WHERE) {
			if (isset($this->fields[$fieldToCompare])) $fieldToCompare = '{'.$fieldToCompare.'}';
			if (preg_match_all('/{([^}]+)}/',$fieldToCompare,$matches)) {
				foreach ($matches[1] as $match) {
					if (!isset($this->fields[$match])) continue;
					$replace = null;
					if (preg_match('/{[^}]+}/',$this->fields[$match]['field']) > 0 || $this->fields[$match]['foreign']) {
						$replace = '`'.$this->fields[$match]['vtable']['alias'].'`.`'.$this->fields[$match]['vtable']['pk'].'`';
					} else {
						$replace = '`'.$this->fields[$match]['vtable']['alias'].'`.`'.$this->fields[$match]['field'].'`';
					}
					if ($replace !== null) $fieldToCompare = str_replace('{'.$match.'}',$replace,$fieldToCompare);
				}
			}
		}

		//echo "$uid::$value<br/>";
		if (($value === NULL && $compareType !== ctCUSTOM) && (!isset($filterData['linkFrom']) || !$filterData['linkFrom'] || (!isset($_REQUEST['_f_'.$uid])))) return '';
		// set filter VALUE
		if ($compareType === ctLIKE && strpos($value,'%') === FALSE ) $value = "%$value%";

		// find field type from tabledef
		// set filter NAME

		// if where, ignore type
		$fieldName = $this->FormatFieldName($fieldName, $fieldTypeOverride);

		// do value
		switch (true) {
			case $compareType == ctMATCH:
				// any fields
				$args = array_merge($args, (array)$value);
				return 'MATCH ('.$fieldToCompare.') AGAINST (?)';
				break;
			case $compareType == ctMATCHBOOLEAN:
				// any fields
				$args = array_merge($args, (array)$value);
				return 'MATCH ('.$fieldToCompare.') AGAINST (? IN BOOLEAN MODE)';
				break;
			case $compareType == ctCUSTOM:
				if (($count = preg_match_all('/(?<!\\\)\?/',$fieldToCompare,$_))) { // has unescaped slashes, add values to args
					$value = (array)$value;
					$vcount = count($value);
					if ($count === 1 && $vcount > 1) { // if count ==1 and count($value) > 1, repeat fTC and values for count(value)
						$f = array();
						for ($i = 0; $i < $vcount; $i++) $f[] = $fieldToCompare;
						$fieldToCompare = '('.implode(' OR ',$f).')';
					} else if ($count > 1 && $vcount === 1) { // if count >1 and count(value) 1, repeat value in args
						$v = array();
						for ($i = 0; $i < $count; $i++) $v[] = $value[0];
						$value = $v;
					}
					$args = array_merge($args, $value);
				}
				return $fieldToCompare;
				break;
			case $compareType == ctANY:
				$constants = get_defined_constants(true);
				foreach ($constants['user'] as $cName => $cVal) {
					if (strtolower(substr($cName,0,2))=='ct' && stripos($value,$cVal) !== FALSE) {
						$val = $value;
						break;
					}
				}
				if (!$val) { $val = "= ?"; $args[] = $value; }
				break;
			case $compareType == ctIS:
			case $compareType == ctISNOT:
				$val = $value; break;
			case $compareType == ctIN:
				if (IsSelectStatement($fieldName)) return trim("$val $compareType $fieldName");
				$vals = explode(',',$value);
				$args = array_merge($args,$vals);
				foreach ($vals as $k=>$v) $vals[$k] = '?';
				$val = '('.join(',',$vals).')';
				break;
				// convert dates to mysql version for filter
			case ($inputType==itDATE): $val = "(STR_TO_DATE(?, '".FORMAT_DATE."'))"; $args[] = $value; break;
			case ($inputType==itTIME): $val = "(STR_TO_DATE(?, '".FORMAT_TIME."'))"; $args[] = $value; break;
			case ($inputType==itDATETIME): $val = "(STR_TO_DATE(?, '".FORMAT_DATETIME."'))"; $args[] = $value; break;
			default:
				$val = '?'; $args[] = $value;
				break;
		}

	//	$fieldToCompare = $fieldToCompare ? $fieldToCompare : $fieldName;

		return trim("$fieldToCompare $compareType $val");
	}

	public $extraWhere = NULL;
	public function GetWhereStatement(&$args) {
		$filters = $this->filters;

		$where = array();
		if (isset($filters[FILTER_WHERE]))
		foreach ($filters[FILTER_WHERE] as $filterset) {
			$setParts = array();
			if (!is_array($filterset)) continue;
			foreach ($filterset as $fData) { // loop each field in set
				if ($fData['type'] !== FILTER_WHERE) continue;
				$fieldName = $fData['fieldName'];

				// if the field doesnt exist in the primary table. -- should be ANY table used. and if more than one, should be specific.

				if (($filterString = $this->GetFilterString($fData['uid'],$args)) !== '')
					$setParts[] = "($filterString)";
			}
			if (count($setParts) >0) $where[] = '('.join(' AND ',$setParts).')';
		}
		$ret = join(' AND ',$where);
		if (empty($this->extraWhere)) return $ret;
    
		if (is_array($this->extraWhere)) {
			$extraWhere = array();
			foreach ($this->extraWhere as $field => $value) {
				$args[] = $value;
				$extraWhere[] = "($field = ?)";
			}
			return "($ret) AND (".implode(' AND ',$extraWhere).")";
		} elseif (is_string($this->extraWhere)) {
			return "($ret) AND (".$this->extraWhere.")";
		}

		return $ret;
	}

  public $extraHaving = NULL;
	public function GetHavingStatement(&$args) {
		$filters = $this->filters;

		$having = array();
		if (isset($filters[FILTER_HAVING]))
		foreach ($filters[FILTER_HAVING] as $filterset) {
			$setParts = array();
			if (!is_array($filterset)) continue;
			foreach ($filterset as $fData) { // loop each field in set
				if ($fData['type'] !== FILTER_HAVING) continue;
				$fieldName = $fData['fieldName'];

				if (($filterString = $this->GetFilterString($fData['uid'],$args)) !== '')
					$setParts[] = "($filterString)";
			}
			if (count($setParts) >0) $having[] = '('.join(' AND ',$setParts).')';
		}
		$ret = join(' OR ',$having);

		if (empty($this->extraHaving)) return $ret;
		if ($ret) $ret = "($ret) AND ";

		if (is_array($this->extraHaving)) {
			$extraWhere = array();
			foreach ($this->extraHaving as $field => $value) {
				$args = $value;
				$extraWhere[] = "($field = ?)";
			}
			if (count($extraWhere) > 0) return "$ret (".implode(' AND ',$extraWhere).")";
		} elseif (is_string($this->extraHaving) && $this->extraHaving) {
			return "$ret (".$this->extraHaving.")";
		}

		return $ret;
	}

	public function GetOrderBy($as_array=false) {
		$sortKey = '_s_'.$this->GetModuleId();
		if (isset($_GET[$sortKey])) {			
			$this->ordering = NULL;
			$this->AddOrderBy($_GET[$sortKey]);
		}
		if (empty($this->ordering)) return 'NULL';
		if (is_array($this->ordering) && !$as_array) return join(', ',$this->ordering);

		return $this->ordering;
	}

	public function GetGrouping() {
		if (empty($this->grouping)) return '';
		if (is_array($this->grouping)) return join(', ',$this->grouping);
		return $this->grouping;
	}

	public $limit = NULL;

	private $queryChecksum = NULL;
	private $explainQuery = false;
	/**
	 * Get a dataset based on setup.
	 * @returns MySQL Dataset
	 */
	public function &GetDataset($filter=null,$clearFilters=false) {
		$this->_SetupParents();
		$this->_SetupFields();
		if ($filter===NULL) $filter = array();
		if (!is_array($filter)) $filter = array($this->GetPrimaryKey()=>$filter);
		$fltrs = $this->filters;
		if ($clearFilters) $this->ClearFilters();
		foreach ($filter as $field => $val) {
			if (is_numeric($field)) { // numeric key is custom filter
				$this->AddFilter($val,ctCUSTOM);
				continue;
			}
			if (($fltr =& $this->GetFilterInfo($field))) { // filter uid exists
				$fltr['value'] = $val;
				continue;
			}
			if (is_array($val)) {
				$fltr = $this->AddFilter($field,$val['ct'],itNONE,$val['val']);
				continue;
			}
			$this->AddFilter($field,ctEQ,itNONE,$val);
		}
		$args = array(); $query = $this->GetSqlQuery($args);
		$this->dataset = new uDataset($query,$args,$this);
		
		$this->filters = $fltrs;

		return $this->dataset;
	}
	public function GetSqlQuery(&$args) {
		$this->_SetupFields();

		// GET SELECT
		$select = $this->GetSelectStatement();
		// GET WHERE
		$where = $this->GetWhereStatement($args); $where = $where ? " WHERE $where" : ''; // uses WHERE modifier
		// GET GROUPING
		$group = $this->GetGrouping(); $group = $group ? " GROUP BY $group" : '';
		// GET HAVING
		$having = $this->GetHavingStatement($args); $having = $having ? " HAVING $having" : ''; // uses HAVING modifier to account for aliases
		// GET ORDER
		$order = $this->GetOrderBy(); $order = $order ? " ORDER BY $order" : '';

		$having1 = $this->GetFromClause() ? $having : '';
		$order1 = $this->GetFromClause() ? $order : '';
		$query = "($select$where$group$having1$order1)";

		if (is_array($this->UnionModules)) {
			foreach ($this->UnionModules as $moduleName) {
				$obj =& utopia::GetInstance($moduleName);
				$obj->_SetupFields();
				$select2 = $obj->GetSelectStatement();
				$where2 = $obj->GetWhereStatement($args); $where2 = $where2 ? " WHERE $where2" : '';
				$group2 = $obj->GetGrouping(); $group2 = $group2 ? " GROUP BY $group2" : '';
				$having2 = $obj->GetHavingStatement($args);
				$having2 = $having2 ? $having.' AND ('.$having2.')' : $having;
				//				if (!empty($having2)) $having2 = $having.' AND ('.$having2.')';
				//				else $having2 = $having;
				$order2 = $obj->GetOrderBy(); $order2 = $order2 ? " ORDER BY $order2" : '';
				$query .= "\nUNION\n($select2$where2$group2$having2$order2)";
			}
			$query .= " $order";
		}
		return $query;
	}

	public function GetLimit(&$limit=null,&$page=null) {
		if ($limit === '') $limit = NULL;
		if ($limit === null) {
			$limitKey = '_l_'.$this->GetModuleId();
			if (isset($_GET[$limitKey])) $limit = $_GET[$limitKey];
		}
		if ($limit === null) $limit = $this->limit;
		if ($limit === null) $limit = 10;
		
		$pageKey = '_p_'.$this->GetModuleId();
		if ($page === NULL) $page = isset($_GET[$pageKey]) ? (int)$_GET[$pageKey] : 0;
		
		if (!is_numeric($limit) || !$limit) $limit = NULL;
	}
	public function ApplyLimit(&$rows,$limit=null) {
		// deprecated
	}

	public function GetCurrentRecord($refresh = FALSE) {
		if (!$refresh) return $this->currentRecord;

		if ($this->currentRecord !== NULL)
			return $this->LookupRecord($this->currentRecord[$this->GetPrimaryKey()]);

		return $this->currentRecord;
	}

	public function LookupRecord($filter=NULL,$clearFilters=false) {
		$ds = $this->GetDataset($filter,$clearFilters);
		if (!$ds->CountRecords()) return NULL;
		$row = $ds->GetFirst();
		if ($filter===NULL && $clearFilters === FALSE) $this->currentRecord = $row;
		return $row;
	}

	public function GetRowWhere($pkValue = NULL) {
		if (!empty($pkValue)) return '`'.$this->GetPrimaryKeyTable()."` = '".$pkValue."'";
		return '';
	}
	
	public function GetRawData($filters=null) {
		$this->_SetupFields();
		$title = $this->GetTitle();

		//TODO:sections	
		$layoutSections = $this->layoutSections;
		
		$pk = $this->GetPrimaryKey();
		
		$fieldDefinitions = array();
		foreach ($this->fields as $fieldAlias => $fieldData) {
			$fieldDefinitions[$fieldAlias] = array('title'=>$fieldData['visiblename'],'type'=>$fieldData['inputtype']);
		}
		
		$data = $this->GetDataset($filters)->fetchAll();
		
		return array(
			'definition' => array('title'=>$title,'fields'=>$fieldDefinitions,'pk'=>$pk),
			'data' => $data,
		);
	}	

//	private $navCreated = FALSE;

  // sends ARGS,originalValue,pkVal,processedVal
	public function PreProcess($fieldName,$value,$rec=NULL,$forceType = NULL) {
		$pkVal = !is_null($rec) ? $rec[$this->GetPrimaryKey()] : NULL;
		if (is_string($value)) $value = mb_convert_encoding($value, 'HTML-ENTITIES', CHARSET_ENCODING);
		$originalValue = $value;
		if (isset($this->fields[$fieldName]['ismetadata'])) {
			$value = utopia::jsonTryDecode($value);
		}
		$suf = ''; $pre = ''; $isNumeric=true;
		if ($forceType === NULL) $forceType = $this->GetFieldType($fieldName);
		switch ($forceType) {
			case ftFILE:
				$filename = '';
				$link = uBlob::GetLink(get_class($this),$fieldName,$pkVal);
				if ($rec && array_key_exists($fieldName.'_filename',$rec) && $rec[$fieldName.'_filename']) $filename = '<b><a target="_blank" href="'.$link.'">'.$rec[$fieldName.'_filename'].'</a></b> - ';
				if (!strlen($value)) $value = '';
				else $value = $filename.round(strlen($value)/1024,2).'Kb<br/>';
				break;
			case ftIMAGE:
				if (!$value) break;
				$style = $this->FieldStyles_Get($fieldName);
				$w = isset($style['width']) ? intval($style['width']) : null;
				$h = isset($style['height']) ? intval($style['height']) : null;
				$value = $this->DrawSqlImage($fieldName,$pkVal,$w,$h,array('style'=>$style));
				break;
			case ftPERCENT:
				$dp = $this->GetFieldProperty($fieldName,'length');
				if (strpos($dp,',') != FALSE) $dp = substr($dp,strpos($dp,',')+1);
				else $dp = 2;
				if (is_numeric($value))
					$value = number_format($value,$dp).'%';
				break;
			case ftFLOAT:
				$dp = $this->GetFieldProperty($fieldName,'length');
				if (is_numeric($value) && strpos($dp,',') != FALSE)
					$value = number_format($value,substr($dp,strpos($dp,',')+1));
				break;
			case ftUPLOAD:
				if ($value) {
					$value = $this->GetUploadURL($fieldName,$pkVal);
					//$value = utopia::GetRelativePath($value);
					$value = "<a href=\"$value\">Download</a>";
				}
				break;
			default: $isNumeric = false;
		}

		if (array_key_exists($fieldName,$this->fields) && array_key_exists('preprocess',$this->fields[$fieldName])) {
			foreach ($this->fields[$fieldName]['preprocess'] as $callback) {
				$args = $callback[1];
				$callback = $callback[0];

				if (is_string($callback)) {
					$method = new ReflectionFunction($callback);
				} elseif (is_array($callback)) {
					$method = new ReflectionMethod($callback[0],$callback[1]);
				}
				$num = $method->getNumberOfParameters();

				if ($args == NULL) $args = array();
				array_unshift($args,$originalValue,$pkVal,$value,$rec,$fieldName);
				array_splice($args,$num); // strip out any extra args than the function can take (stops 'wrong param count' error)

				$nv = call_user_func_array($callback,$args);
				if ($nv !== null) $value = $nv;
			}
		}

		return $value;
	}

	public function GetUploadURL($fieldAlias,$pkVal) {
		$filters = array(
			'__ajax'=>'getUpload',
			'f'		=>$fieldAlias,
			'p'		=>$pkVal
		);

		return $this->GetURL($filters);
	}

	// TODO: Requests for XML data (ajax)
	// to be used later with full AJAX implimentation
	public function CreateXML() {
		// call parent createxml
		// parse and inject child links into 'dataset' xml object
	}

	public function GetFilterBox($filterInfo,$attributes=NULL,$spanAttributes=NULL) {
		if (is_string($filterInfo))
			$filterInfo = $this->GetFilterInfo($filterInfo);
		// already filtered?

		if ($filterInfo['it'] === itNONE) return '';
		$fieldName = $filterInfo['fieldName'];

		$default = $this->GetFilterValue($filterInfo['uid']);

		$pre = '';
		$emptyVal = '';
		if (!empty($filterInfo['title'])) {
			$emptyVal = $filterInfo['title'];
		} elseif (isset($this->fields[$fieldName])) {
			switch ($filterInfo['ct']) {
				case ctGT:
				case ctGTEQ:
					$emptyVal = 'From'; break;
				case ctLT:
				case ctLTEQ:
					$emptyVal = 'To'; break;
				case ctEQ:
				case ctLIKE:
					$emptyVal = 'Search'; break;
				default:
					$emptyVal = $this->fields[$fieldName]['visiblename'].' '.htmlentities($filterInfo['ct']); break;
			}
		}

		$vals = $this->FindValues($fieldName,$filterInfo['values']);
		if ($filterInfo['it'] == itSUGGEST || $filterInfo['it'] == itSUGGESTAREA) {
			if (isset($values[$default])) $default = $values[$default];
			$vals = cbase64_encode(get_class($this).':'.$fieldName);
		}

		if (!$attributes) $attributes = array();
		$attributes['placeholder'] = strip_tags($emptyVal);
		if (array_key_exists('class',$attributes)) $attributes['class'] .= 'uFilter';
		else $attributes['class'] = 'uFilter';

		if ($filterInfo['it'] == itDATE) {
			if (!array_key_exists('style',$attributes)) $attributes['style'] = array();
			if (is_array($attributes['style']) && !array_key_exists('width',$attributes['style'])) {
				$attributes['style']['width'] = '8.5em';
			}
		}

		$spanAttr = BuildAttrString($spanAttributes);

		return '<span '.$spanAttr.'>'.$pre.utopia::DrawInput('_f_'.$filterInfo['uid'],$filterInfo['it'],$default,$vals,$attributes,false).'</span>';
	}

	public function ProcessUpdates($sendingField,$fieldAlias,$value,&$pkVal=NULL,$isFile=false) {
		$this->_SetupFields();
		
		// can we access this field?
		if ($pkVal === NULL || $this->LookupRecord($pkVal)) {
			if ($fieldAlias === '__u_delete_record__')
				$this->DeleteRecord($pkVal);
			elseif ($isFile)
				$this->UploadFile($fieldAlias,$value,$pkVal);
			else
				$this->UpdateField($fieldAlias,$value,$pkVal);
		}
	
		// reset all fields.
		$this->ResetField($fieldAlias,$pkVal);
		foreach ($this->fields as $alias => $field) {
			if (!isset($field['preprocess']) && (isset($this->fields[$fieldAlias]) && $field['field'] !== $this->fields[$fieldAlias]['field'])) continue;
			$this->ResetField($alias,$pkVal);
		}
	}

	public function DeleteRecord($pkVal) {
		AjaxEcho('//'.get_class($this)."@DeleteRecord($pkVal)");
		if (!flag_is_set($this->GetOptions(),ALLOW_DELETE)) { throw new Exception('Module does not allow record deletion.'); }
		
		if (uEvents::TriggerEvent('BeforeDeleteRecord',$this,array($pkVal)) === FALSE) return FALSE;
		
		$table = TABLE_PREFIX.$this->GetTabledef();
		database::query("DELETE FROM $table WHERE `{$this->GetPrimaryKeyTable()}` = ?",array($pkVal));
		
		uEvents::TriggerEvent('AfterDeleteRecord',$this,array($pkVal));
		
		return TRUE;
	}

	public function UploadFile($fieldAlias,$fileInfo,&$pkVal = NULL) {
		//$allowedTypes = $this->GetFieldProperty($fieldAlias, 'allowed');
		if (uEvents::TriggerEvent('BeforeUploadFile',$this,array($fieldAlias,$fileInfo,&$pkVal)) === FALSE) return FALSE;
		
		if (!file_exists($fileInfo['tmp_name'])) { AjaxEcho('alert("File too large. Maximum File Size: '.utopia::ReadableBytes(utopia::GetMaxUpload()).'");'); return; }
		$type = getSqlTypeFromFieldType($this->GetFieldType($fieldAlias));
		if (strpos($type,'blob') === FALSE && strpos($type,'text') === FALSE) {
			$targetFile = get_class($this).'/'.date('Y-m-d').'/'.$pkVal.'/'.time().'_'.$fileInfo['name'];
			$filename = uUploads::UploadFile($fileInfo,$targetFile);
			if (!$filename) return;
			$this->UpdateField($fieldAlias,$filename,$pkVal);
		} else {
			$value = file_get_contents($fileInfo['tmp_name']);
			$this->UpdateField($fieldAlias.'_filename',$fileInfo['name'],$pkVal);
			$this->UpdateField($fieldAlias.'_filetype',$fileInfo['type'],$pkVal);
			$this->UpdateField($fieldAlias,$value,$pkVal);
		}
		
		if (uEvents::TriggerEvent('AfterUploadFile',$this,array($fieldAlias,$fileInfo,&$pkVal)) === FALSE) return FALSE;
	}

	public function OnNewRecord($pkValue) {}
	public function OnParentNewRecord($pkValue) {}

	public function UpdateFields($fieldsVals,&$pkVal=NULL) {
		foreach ($fieldsVals as $field => $val) {
			if ($this->UpdateField($field,$val,$pkVal) === FALSE) return FALSE;
		}
	}
	
	public function UpdateFieldRaw($fieldAlias,$newValue,&$pkVal=NULL) {
		$fieldType = $this->SetFieldType($fieldAlias,ftRAW);
		$ret = $this->UpdateField($fieldAlias,$newValue,$pkVal);
		$this->SetFieldType($fieldAlias,$fieldType);
		return $ret;
	}
	
	// returns a string pointing to a new url, TRUE if the update succeeds, false if it fails, and null to refresh the page
	private $noDefaults = FALSE;
	public function UpdateField($fieldAlias,$newValue,&$pkVal=NULL) {
		//AjaxEcho('//'.str_replace("\n",'',get_class($this)."@UpdateField($fieldAlias,,$pkVal)\n"));
		if ($pkVal === NULL && !flag_is_set($this->GetOptions(),ALLOW_ADD)) { throw new Exception('Module does not allow adding records.'); }
		if ($pkVal !== NULL && !flag_is_set($this->GetOptions(),ALLOW_EDIT)) { throw new Exception('Module does not allow editing records.'); }

		$this->_SetupFields();
		if (!array_key_exists($fieldAlias,$this->fields)) return FALSE;
		$tableAlias	= $this->fields[$fieldAlias]['tablename'];

		if (!$tableAlias) return FALSE; // cannot update a field that has no table

		if (($this->fields[$fieldAlias]['options'] & PERSISTENT != PERSISTENT) && uEvents::TriggerEvent('CanAccessModule',$this) === FALSE) return FALSE;
		if (uEvents::TriggerEvent('BeforeUpdateField',$this,array($fieldAlias,$newValue,&$pkVal)) === FALSE) return FALSE;
		
		$oldPkVal = $pkVal;
		$fieldPK = $this->GetPrimaryKey($fieldAlias);
		
		$tbl		= $this->fields[$fieldAlias]['vtable'];
		$values		= $this->GetValues($fieldAlias,$pkVal);

	/*	if ($newValue !== NULL && $newValue !== '' && is_numeric($newValue) && $this->fields[$fieldAlias]['inputtype'] == itSUGGEST || $this->fields[$fieldAlias]['inputtype'] == itSUGGESTAREA) {
			$valSearch = (is_assoc($values)) ? array_flip($values) : $values;
			$srch = array_search($newValue, $valSearch);
			if ($srch !== FALSE) $newValue = $srch;
		}*/
		if ($this->fields[$fieldAlias]['inputtype'] == itMD5 || $this->fields[$fieldAlias]['inputtype'] == itPASSWORD) {
			if (empty($newValue)) return FALSE;
			$newValue = md5($newValue);
		}
		$originalValue = $newValue;

		$field = $this->fields[$fieldAlias]['field'];
		$table		= $tbl['tModule'];
		$tablePk	= $tbl['pk'];
		
		$preModPk	= NULL;
		if (array_key_exists('parent',$tbl)) {
			foreach ($tbl['joins'] as $fromField=>$toField) {
				if ($fromField == $this->sqlTableSetupFlat[$tbl['parent']]['pk']) { // if the table join is linked to the parents primary key, then update that record
					$tableObj =& utopia::GetInstance($table);
					// find target PK value
					$row = $this->LookupRecord($pkVal);
					
					// is link table?
					if ($tableObj instanceof iLinkTable) {
						if (!is_array($newValue)) $newValue = array($newValue);
						// do link table stuff
						// delete all where tofield is oldpk
						database::query('DELETE FROM `'.$tableObj->tablename.'` WHERE `'.$toField.'` = ?',array($oldPkVal));
						foreach ($newValue as $v) {
							$n = null;
							$tableObj->UpdateField($toField,$oldPkVal,$n); //set left
							$tableObj->UpdateField($field,$v,$n); //set right
						}
						return true;
					}
					$preModPk = $pkVal;
					$pkVal = $row[$this->GetPrimaryKeyField($fieldAlias)];
					if ($pkVal === NULL) { // initialise a row if needed
						$tableObj->UpdateField($toField,$oldPkVal,$pkVal);
					}
					break; // if linkFrom is the primary key of our main table then we don't update the parent table.
				}
			}
			foreach ($tbl['joins'] as $fromField=>$toField) {
				if ($toField == $tablePk) {
					$field = $fromField;
					$tbl = $this->sqlTableSetupFlat[$tbl['parent']];
					$table		= $tbl['tModule'];
					$tablePk	= $tbl['pk'];
					break;
				}
			}
		}
		
		if ((preg_match('/{[^}]+}/',$field) > 0) || IsSelectStatement($field) || is_array($field)) {
			$this->ResetField($fieldAlias,$pkVal);
			return FALSE; // this field is a pragma, select statement or callback
		}
		

		if (isset($this->fields[$fieldAlias]['ismetadata']) && $this->fields[$fieldAlias]['ismetadata']) {
			return $this->SetMetaValue($fieldAlias,$newValue,$pkVal);
		}
		
		$fieldType = $this->GetFieldType($fieldAlias);

		// lets update the field
		$tableObj =& utopia::GetInstance($table);
		try {
			$ret = $tableObj->UpdateField($field,$newValue,$pkVal,$fieldType) === FALSE ? FALSE : TRUE;
		} catch (Exception $e) {
			$ret = false;
			switch ($e->getCode()) {
				case 1062: // duplicate key
					uNotices::AddNotice('An entry already exists with this value.',NOTICE_TYPE_ERROR);
					break;
				default: throw $e;
			}
		}
		if ($preModPk !== NULL) $pkVal = $preModPk;
		
		if ($oldPkVal === NULL) {
			// new record added
			// update default values
			if (!$this->noDefaults) {
				$this->noDefaults = true;
				foreach ($this->fields as $dalias => $fieldData) {
					if ($fieldAlias == $dalias) continue; // dont update the default for the field which is being set.
					$default = $this->GetDefaultValue($dalias);
					if (!empty($default)) {
						//echo "//setting default for $dalias to $default PK $pkVal\n";
						$this->UpdateField($dalias,$default,$pkVal);
					}
				}
				$this->noDefaults = false;
			}

			// new record has been created.  pass the info on to child modules, incase they need to act on it.
			uEvents::TriggerEvent('OnNewRecord',$this,$pkVal);
		}
		
		if (array_key_exists('onupdate',$this->fields[$fieldAlias])) {
			foreach ($this->fields[$fieldAlias]['onupdate'] as $callback) {
				list($callback,$arr) = $callback;
				//echo "$callback,".print_r($arr,true);
				if (is_string($callback))// $callback = array($this,$callback);
				$callback = array($this,$callback);
				array_unshift($arr,$pkVal);
				$newRet = call_user_func_array($callback,$arr);
				if ($ret === TRUE) $ret = $newRet;
			}
		}

		$this->ResetField($fieldAlias,$pkVal);
		if ($oldPkVal !== $pkVal) $this->ResetField($fieldAlias,$oldPkVal);
		
		if (uEvents::TriggerEvent('AfterUpdateField',$this,array($fieldAlias,$newValue,&$pkVal)) === FALSE) return FALSE;

		return $ret;
	}
	
	public function MergeFields(&$string,$row) {
		$fields = $this->fields;
		if (preg_match_all('/{([a-z]+)\.([^{]+)}/Ui',$string,$matches,PREG_PATTERN_ORDER)) {
			$searchArr = $matches[0];
			$typeArr = isset($matches[1]) ? $matches[1] : false;
			$varsArr = isset($matches[2]) ? $matches[2] : false;
			$row['_module_url'] = $this->GetURL($row[$this->GetPrimaryKey()]);
			foreach ($searchArr as $k => $search) {
				$field = $varsArr[$k];
				$qs = null;
				if (strpos($field,'?') !== FALSE) list($field,$qs) = explode('?',$field,2);
				if (!array_key_exists($field,$row)) continue;
				if ($qs) {
					parse_str(html_entity_decode($qs),$qs);
					$this->FieldStyles_Add($field,$qs);
				}
				switch ($typeArr[$k]) {
					case 'u':
						$replace = $this->PreProcess($field,$row[$field],$row);
						$replace = UrlReadable($replace);
						$string = str_replace($search,$replace,$string);
						break;
					case 'd':
						$replace = $this->GetCell($field,$row);
						$string = str_replace($search,$replace,$string);
						break;
					case 'field':
						$replace = $this->PreProcess($field,$row[$field],$row);
						$string = str_replace($search,$replace,$string);
						break;
					default:
				}
			}
		}
		$this->fields = $fields;
		while (utopia::MergeVars($string));
	}

	public function DrawSqlImage($fieldAlias,$pkVal,$width=NULL,$height=NULL,$attr=NULL,$link=false,$linkW=NULL,$linkH=NULL,$linkAttr=NULL) {
		if (!is_array($attr)) $attr = array();
		if (!array_key_exists('alt',$attr)) $attr['alt'] = '';
		if ($width) $attr['width'] = intval($width); if ($height) $attr['height'] = intval($height);
		$attr = BuildAttrString($attr);
		
		$url = uBlob::GetLink(get_class($this),$fieldAlias,$pkVal);
		
		$imgQ = http_build_query(array('w'=>$width,'h'=>$height)); if ($imgQ) $imgQ = '?'.$imgQ;
		if (!$link) return "<img$attr src=\"$url$imgQ\">";

		$linkQ = http_build_query(array('w'=>$linkW,'h'=>$linkH)); if ($linkQ) $linkQ = '?'.$linkQ;
		if ($link === TRUE) $linkUrl = $url.$linkQ;
		else $linkUrl = $link;

		$linkAttr = BuildAttrString($linkAttr);
		return "<a$linkAttr href=\"$linkUrl\" target=\"_blank\"><img$attr src=\"$url\"></a>";
	}
	
	public function GetCell($fieldName, $row, $url = '', $inputTypeOverride=NULL, $valuesOverride=NULL) {
		if (is_array($row) && array_key_exists('__module__',$row) && $row['__module__'] != get_class($this)) {
			$obj =& utopia::GetInstance($row['__module__']);
			return $obj->GetCell($fieldName,$row,$url,$inputTypeOverride,$valuesOverride);
		}
		if ($this->UNION_MODULE)
			$pkField = '__module_pk__';
		else
			$pkField = $this->GetPrimaryKey();
		$fldId = $this->GetEncodedFieldName($fieldName,$row[$pkField]);
		$celldata = $this->GetCellData($fieldName,$row,$url,$inputTypeOverride,$valuesOverride);
		return "<!-- NoProcess --><span class=\"$fldId\">$celldata</span><!-- /NoProcess -->";
	}

	public function GetCellData($fieldName, $row, $url = '', $inputTypeOverride=NULL, $valuesOverride=NULL) {
		if (is_array($row) && array_key_exists('__module__',$row) && $row['__module__'] != get_class($this)) {
			$obj =& utopia::GetInstance($row['__module__']);
			return $obj->GetCellData($fieldName,$row,$url,$inputTypeOverride,$valuesOverride);
		}
		$pkVal = NULL;
		if (is_array($row)) {
			if ($this->UNION_MODULE)
				$pkField = '__module_pk__';
			else
				$pkField = $this->GetPrimaryKey();
			$pkVal = $row[$pkField];
		}

		//		echo "// start PP for $fieldName ".(is_array($row) && array_key_exists($fieldName,$row) ? $row[$fieldName] : '')."\n";
		$value = (is_array($row) && array_key_exists($fieldName,$row)) ? $row[$fieldName] : '';
		if ($value === '' && isset($this->fields[$fieldName]) && preg_match('/^\'(.+?)\'/', $this->fields[$fieldName]['field'],$match)) $value = $match[1];
		$value = $this->PreProcess($fieldName,$value,$row);
		
		$fieldData = array();
		if (isset($this->fields[$fieldName])) $fieldData = $this->fields[$fieldName];
		if (!$fieldData && strpos($fieldName,':') !== FALSE) {
			list($vname) = explode(':',$fieldName);
			if (isset($this->fields[$vname])) $fieldData = $this->fields[$vname];
		}
		
		$inputType = !is_null($inputTypeOverride) ? $inputTypeOverride : (isset($fieldData['inputtype']) ? $fieldData['inputtype'] : itNONE);
		if ($inputType !== itNONE && (($row !== NULL && flag_is_set($this->GetOptions(),ALLOW_EDIT)) || ($row === NULL  && flag_is_set($this->GetOptions(),ALLOW_ADD)))) {
			$attr = !empty($url) ? array('ondblclick'=>'javascript:nav(\''.$url.'\')') : NULL;
			$ret = $this->DrawSqlInput($fieldName,$value,$pkVal,$attr,$inputType,$valuesOverride);
		} else {
			if ($url) {
				$class = $this->GetFieldProperty($fieldName,'button') ? ' class="btn"' : '';
				$ret = "<a$class href=\"$url\">$value</a>";
			} else {
				$ret = $value;
			}
		}
		return $ret;
	}

	static $targetChildren = array();
	public function GetTargetURL($field,$row,$includeFilter = true) {
		$fURL = $this->GetFieldProperty($field,'url');
		if ($fURL) return $fURL;

		// check union module
		$searchModule = is_array($row) && array_key_exists('__module__',$row) ? $row['__module__'] : get_class($this);
		//		print_r($GLOBALS['children']);
		//echo "$searchModule<br/>";
		$children = isset(self::$targetChildren[$searchModule]) ? self::$targetChildren[$searchModule] : utopia::GetChildren($searchModule);

		$info = NULL;
		// get specific field
		foreach ($children as $links) {
			foreach ($links as $link) {
				if (!isset($link['parentField'])) continue;
				if ($link['parentField'] == $field) { $info = $link; break; }
			}
		}
		// if not found, check for fallback
		if (!$info) {
			if ($field !== '*') return $this->GetTargetURL('*',$row,$includeFilter);
			return NULL;
		}

		if ($includeFilter) {
			$targetFilter = $this->GetTargetFilters($field,$row);
		} else {
			$targetFilter = NULL;
		}
		
		$obj =& utopia::GetInstance($info['moduleName']);
		return $obj->GetURL($targetFilter);
	}

	public function GetTargetFilters($field,$row) {
		//        ErrorLog("GTF($field,$row)");
		if ($row == NULL) return NULL;
		$searchModule = is_array($row) && array_key_exists('__module__',$row) ? $row['__module__'] : get_class($this);

		$children = isset(self::$targetChildren[$searchModule]) ? self::$targetChildren[$searchModule] : utopia::GetChildren($searchModule);

		$info = NULL;
		// get specific field
		foreach ($children as $links) {
			foreach ($links as $link) {
				if (!isset($link['parentField'])) continue;
				if ($link['parentField'] == $field) { $info = $link; break; }
			}
		}
		// if not found, check for fallback
		if (!$info) {
			if ($field !== '*') return $this->GetTargetFilters('*',$row);
			return NULL;
		}

		//echo "<br/>$field:";
		// fieldLinks: array: parentField => childField
		// need to replace the values
		$targetModule = $info['moduleName'];
		$newFilter = array();
//		$additional = array();
		//print_r($info['fieldLinks']);
		foreach ($info['fieldLinks'] as $linkInfo) {
			$value = NULL;
			// fromfield == mortgage_id
			// module_pk == VAL:note_id
			if (!$this->FieldExists($linkInfo['fromField']) and ($fltr =& $this->FindFilter($linkInfo['fromField']))) {
				// no field exists, but we do have a filter, we should move that over
				$value = $this->GetFilterValue($fltr['uid']);

				// if its a union AND the fromfield equals the modules primarykey then show the module_pk
				// NO,  if its a union, loop thru that modules fields to find the number of the fromField. then get the value of the corresponding number in this union parent module
			} elseif (array_key_exists('__module__',$row)) {
				$obj =& utopia::GetInstance($row['__module__']);
				$unionFields = $obj->fields;
				$uFieldCount = 0;
				$keys = array_keys($unionFields);
				foreach ($keys as $uFieldAlias) {
					if ($uFieldAlias == $linkInfo['fromField']) break;
					$uFieldCount++;
				}

				$ourKeys = array_keys($this->fields);
				$correspondingKey = $ourKeys[$uFieldCount];
				$value = $row[$correspondingKey];
			} else {
				//$value = $row[$linkInfo['fromField']]; // use actual value, getting the real value on every field causes a lot of lookups, the requested field must be the field that stores the actual value
				/* */
				$tableModule = $this->fields[$linkInfo['fromField']]['vtable']['tModule'];
				if ($this->GetRootField($linkInfo['fromField']) == $this->fields[$linkInfo['fromField']]['field']) {
					//if ($tableModule == $this->GetTabledef()) {
					$value = $row[$linkInfo['fromField']];
				} else {
					$value = $this->GetRealValue($linkInfo['fromField'],$row[$this->GetPrimaryKey()]);
				} /* */
				//ErrorLog(print_r($linkInfo,true));
			}
			//echo $value."<br/>";
			if ($value !== NULL)
				$newFilter['_f_'.$linkInfo['toField']] = $value;
		}
		return $newFilter;
		//print_r(array_merge($newFilter,$additional));
		return array_merge($newFilter,$additional);
		// now returns an array
		$extra = array();
		if (is_array($additional)) foreach ($additional as $key => $val)
		$extra[] = "$key=$val";
		array_unshift($extra,FilterArrayToString($newFilter));
		return join('&amp;',$extra);
	}

	public function ResetField($fieldAlias,$pkVal = NULL) {
		if (!$this->FieldExists($fieldAlias)) return;
		if (uEvents::TriggerEvent('BeforeResetField',$this,$fieldAlias) === FALSE) return FALSE;
		//AjaxEcho("//".get_class($this)."@ResetField($fieldAlias~$pkVal)\n");
		// reset the field.

		$enc_name = $this->GetEncodedFieldName($fieldAlias,$pkVal);
		$newRec = is_null($pkVal) ? NULL : $this->LookupRecord($pkVal,true);
		
		$data = $this->GetCellData($fieldAlias,$newRec,$this->GetTargetURL($fieldAlias,$newRec));
		
		utopia::AjaxUpdateElement($enc_name,$data);
		//$ov = base64_encode($data);
		//AjaxEcho("$('div#$enc_name').html(Base64.decode('$ov'));\n");
	}
}

/**
 * List implimentation of uDataModule.
 * Default module for displaying results in a list format. Good for statistics and record searches.
 */
abstract class uListDataModule extends uDataModule {
	public $injectionFields = array();

	//private $maxRecs = NULL;
	//public function SetMaxRecords($value) {
//		$this->maxRecs = $value;
//	}

	public function UpdateField($fieldAlias,$newValue,&$pkVal = NULL) {
		$isNew = ($pkVal === NULL);

		if ($isNew && !$this->CheckMaxRows(1)) {
			$this->ResetField($fieldAlias,NULL); // reset the "new record" field
			return;
		}

		$ret = parent::UpdateField($fieldAlias,$newValue,$pkVal);
		if ($ret === FALSE) return $ret;

		$enc_name = $this->GetEncodedFieldName($fieldAlias);
		if ($isNew) { // && $ret !== FALSE
			//AjaxEcho("//$fieldAlias: pk=$pkVal\n");
			// add the row
			$newRec = $this->LookupRecord($pkVal);
			$ov = base64_encode($this->DrawRow($newRec));
			AjaxEcho("$('.$enc_name').each(function(){ $(this).closest('TABLE.datalist').children('TBODY').append(Base64.decode('$ov'));});\n");
		}

		return $ret;
	}

	public function ResetField($fieldAlias,$pkVal = NULL) {
		// reset the field.
		if ($pkVal && !$this->LookupRecord($pkVal)) {
			$enc_name = $this->GetEncodedFieldName($fieldAlias,$pkVal);
			$this->SendHideRow($pkVal);
			return;
		}
		parent::ResetField($fieldAlias,$pkVal);
	}

	public function DeleteRecord($pkVal) {
		parent::DeleteRecord($pkVal);
		$this->SendHideRow($pkVal);
		$this->CheckMaxRows();
	}

	public function SendHideRow($pk) {
		$rowclass = cbase64_encode(get_class($this).':'.$pk);
		AjaxEcho("$('tr.$rowclass').hide();");
	}

	public function CheckMaxRows($mod = 0) {
		AjaxEcho('// max recs: '.$this->GetMaxRows());
		$rows = $this->GetDataset()->CountRecords();
		if (!$this->GetMaxRows() || $rows + $mod < $this->GetMaxRows()) {
			AjaxEcho('$(".newRow").show();');
			return TRUE;
		}
		AjaxEcho('$(".newRow").hide();');
		if ($rows + $mod == $this->GetMaxRows()) return TRUE;
		return FALSE;
	}
	
	public function GetMaxRows() {
		return NULL;//$this->maxRecs;
	}
	
	public $limit = 50;
	
	public function ShowData($rows = null, $tabTitle = null,$tabOrder = null) {
		//	echo "showdata ".get_class($this)."\n";
		array_sort_subkey($this->fields,'order');

		$dataset = $this->GetDataset();
		$num_rows = $dataset->CountRecords();
		$this->GetLimit($limit,$page);
		if (!$rows) $rows = $dataset->GetPage($page,$limit);
		if (!$tabTitle) $tabTitle = $this->GetTitle();
		if (!$tabOrder) $tabOrder = $this->GetSortOrder();
		
		$children = utopia::GetChildren(get_class($this));
		foreach ($children as $childModule => $links) {
			foreach ($links as $link) {
				$obj =& utopia::GetInstance($link['moduleName']);
				if (!flag_is_set($this->GetOptions(),ALLOW_ADD)
						&& flag_is_set($obj->GetOptions(),ALLOW_ADD)
						&& is_subclass_of($link['moduleName'],'uSingleDataModule')
						&& ($link['parentField'] === NULL || $link['parentField'] === '*')) {
					$url = $obj->GetURL(array('_n_'.$obj->GetModuleId()=>'1'));
					utopia::LinkList_Add('list_functions:'.get_class($this),null,CreateNavButton('New '.$obj->itemName,$url,array('class'=>'btn-new')),1);
				}
			}
		}

		uEvents::TriggerEvent('OnShowDataList',$this);
		//		LoadChildren(get_class($this));
		// first draw header for list
		//		$fl = (flag_is_set($this->GetOptions(),ALLOW_FILTER)) ? ' filterable' : '';
		if (!isset($GLOBALS['inlineListCount'])) $GLOBALS['inlineListCount'] = 0;
		else $GLOBALS['inlineListCount']++;

		$tabGroupName = utopia::Tab_InitGroup($this->tabGroup);

		//$layoutID = utopia::tab_ //$tabGroupName.'-'.get_class($this)."_list_".$GLOBALS['inlineListCount'];
		$metadataTitle = ' {tabTitle:\''.$tabTitle.'\', tabPosition:\''.$this->GetSortOrder().'\'}';
		//echo "<div id=\"$layoutID\" class=\"draggable$metadataTitle\">";
		ob_start();
		if (!$this->isAjax) echo '<form action="" onsubmit="this.action = window.location" method="post"><input type="hidden" name="__ajax" value="updateField">';
		echo "<table class=\"".get_class($this)." layoutListSection datalist\">";

		/*		echo "<colgroup>";
		 // need first 'empty' column for buttons?
		 if (flag_is_set($this->GetOptions(),ALLOW_DELETE)) { echo "<col></col>"; }
		 foreach ($this->fields as $fieldName => $fieldData) {
			if ($fieldData['visiblename'] === NULL) continue;
			$attr = $this->GetFieldType($fieldName) == ftCURRENCY ? ' align="char" char="."' : '';
			echo "<col$attr></col>";
			}
			echo "</colgroup>\n";   */
		$sectionFieldTitles = array();
		// TODO: pagination for list record display
		if (!flag_is_set($this->GetOptions(),LIST_HIDE_HEADER)) {
			echo '<thead>';

			ob_start();
			// start of SECTION headers
			if (count($this->layoutSections) > 1) {
				echo '<tr>';
				// need first 'empty' column for buttons?
				if (flag_is_set($this->GetOptions(),ALLOW_DELETE)) { echo "<td>&nbsp;</td>"; }
				$sectionCount = 0;
				$sectionID = NULL;
				$keys = array_keys($this->fields);
				$lastFieldName = end($keys);
				foreach ($this->fields as $fieldName => $fieldData) {
					if ($fieldData['visiblename'] === NULL) continue;
					if ($sectionID === NULL) $sectionID = $fieldData['layoutsection'];

					if ($fieldData['layoutsection'] !== $sectionID) {// || $fieldName == $lastFieldName) {
						// write the section, and reset the count
						$sectionName = $this->layoutSections[$sectionID]['title'];
						$secClass = empty($sectionName) ? '' : ' sectionHeader';
						echo "<td colspan=\"$sectionCount\" class=\"$secClass\">".nl2br(htmlentities_skip($sectionName,'<>"'))."</td>";
						$sectionCount = 0;
						$sectionID = $fieldData['layoutsection'];
					}
					$sectionFieldTitles[$sectionID] = array_key_exists($sectionID,$sectionFieldTitles) ? $sectionFieldTitles[$sectionID] : !empty($fieldData['visiblename']);
					//if ($sectionCount == 0 && $sectionID > 0) $this->firsts[$fieldName] = true;
					$sectionCount++;
				}
				$sectionName = $this->layoutSections[$sectionID]['title'];
				$secClass = empty($sectionName) ? '' : ' sectionHeader';
				echo "<td colspan=\"$sectionCount\" class=\"$secClass\">".nl2br(htmlentities_skip($sectionName,'<>"'))."</td>";
				echo "</tr>";
			}

			// start of FIELD headers
			$colcount = 0;
			echo '<tr class="ui-tabs-nav ui-helper-reset ui-widget-header ui-corner-all">';
			if (flag_is_set($this->GetOptions(),ALLOW_DELETE)) { echo '<th class="ui-corner-top"></th>'; $colcount++; }
			foreach ($this->fields as $fieldName => $fieldData) {
				if ($fieldData['visiblename'] === NULL) continue;
				$colcount++;
				echo '<th class="ui-state-default ui-corner-top sortable" rel="'.$fieldName.'|'.$this->GetModuleId().'">';

				// sort?
				$o = $this->GetOrderBy(true);
				if (is_array($o)) foreach ($o as $order) {
					if (strpos($order,'`'.$fieldName.'`') !== FALSE) {
						$icon = 'ui-icon-triangle-1-s';
						if (stripos($order,'desc') !== FALSE) $icon = 'ui-icon-triangle-1-n';
						echo '<span class="ui-icon '.$icon.' left"></span>';
						break;
					}
				}

				// title
				echo nl2br(htmlentities_skip($fieldData['visiblename'],'<>"'));

				// filters
				ob_start();
				if (flag_is_set($this->GetOptions(),ALLOW_FILTER) && $this->hasEditableFilters === true && $this->hideFilters !== TRUE) {
					foreach ($this->filters as $fType) {
						foreach ($fType as $filterset) { //flag_is_set($fieldData['options'],ALLOW_FILTER)) {
							foreach ($filterset as $filterInfo) {
								if ($fieldName != $filterInfo['fieldName']) continue;
								if ($filterInfo['it'] === itNONE) continue;
								echo $this->GetFilterBox($filterInfo);
							}
						}
					}
				}
				$c = ob_get_contents();
				ob_end_clean();
				if ($c) echo '<div class="cb">'.$c.'</div>';

				echo "</th>";
			}
			echo '</tr>'; // close column headers

			$c = ob_get_contents();
			ob_end_clean();
			
			$pagination = '';
			if ($limit) {
				$pages = max(ceil($num_rows / $limit),1);
				ob_start();
					utopia::OutputPagination($pages,'_p_'.$this->GetModuleId());
					$pagination = ob_get_contents();
				ob_end_clean();
			}
			
			$pager = $num_rows > 100 ? '<span class="pager" style="float:right;"></span>' : '';
			$records = ($num_rows == 0) ? "There are no records to display." : 'Total Rows: '.$num_rows;
			$pager = '<div class="pagination right">'.$pagination.' '.utopia::DrawInput('_l_'.$this->GetModuleId(),itTEXT,$limit,NULL,array('class'=>'uFilter uLimit')).' per page</div>';
			if (!flag_is_set($this->GetOptions(),LIST_HIDE_STATUS)) {
				echo '<tr><td colspan="'.$colcount.'">{list.'.get_class($this).'}<span class="record-count">'.$records.'</span>'.$pager.'</td></tr>';
			}

			if ($num_rows > 0 || flag_is_set($this->GetOptions(),ALLOW_ADD) || $this->hasEditableFilters === true) echo $c;

			echo "</thead>\n";
		}

		//		if ($this->hasEditableFilters === true)
		//			echo "</form>";
		/*
		 // is filterable?
		 if (flag_is_set($this->GetOptions(),ALLOW_FILTER) && $this->hasEditableFilters === true) {
			echo "<thead><form method=get><input type=hidden name='uuid' value='".$this->GetUUID()."'>"; // must have UUID for filters
			echo "<th><input type=button onclick='rf(this)' value='F'></th>";
			//if (flag_is_set($this->GetOptions(),ALLOW_DELETE)) echo "<th></th>";
			$col = 1;
			foreach ($this->fields as $fieldName => $fieldData) {
			if (empty($fieldData['visiblename'])) continue;
			$class = 'filter-HVAL-'.$col;
			echo "<th class='filterheader' nowrap='nowrap'>";// class='$class'>";
			if (flag_is_set($fieldData['options'],ALLOW_FILTER))
			echo $this->GetFilterBox($fieldName,$col);
			echo "</th>";
			$col++;
			}
			echo "</form></thead>\n";
			}
			*/
		// now display data rows
		// process POST filters
		$total = array();
		$totalShown = array();

		timer_start('display rows');

		$gUrl = '';
		//		$gUrl = $this->GetTargetUrl('*');
		//		if (!empty($gUrl))
		//			$gUrl = " l_url='$gUrl'";

		$body = "<tbody$gUrl>";
		if ($num_rows == 0) {
		} else {
			//			if ($result != FALSE && mysql_num_rows($result) > 200)
			//				echo "<tr><td colspan=\"$colcount\">There are more than 200 rows. Please use the filters to narrow your results.</td></tr>";
			$i = 0;
			foreach ($rows as $row) {
				$i++;
				// move totals here
				$fields = $this->GetFields();
				foreach ($fields as $fieldName => $fieldData) {
					switch ($this->GetFieldType($fieldName)) {
						case ftNUMBER:
						case ftCURRENCY:
						case ftPERCENT:
							if (!array_key_exists($fieldName,$total)) $total[$fieldName] = 0;
							if (!array_key_exists($fieldName,$totalShown)) $totalShown[$fieldName] = 0;
							//$pkVal = $row[$this->GetPrimaryKey()];
							$preProcessValue = floatval(preg_replace('/[^0-9\.-]/','',$this->PreProcess($fieldName,$row[$fieldName],$row)));
							if ($i <= 150) $totalShown[$fieldName] += $preProcessValue;
							$total[$fieldName] += $preProcessValue;
							break;
						default: break;
					}
				}
				$body .= $this->DrawRow($row);
			}
		}
		$body .= "</tbody>";
		timer_end('display rows');
		$foot = '';
		if (flag_is_set($this->GetOptions(),ALLOW_ADD)) {
			$hideNew = ($this->GetMaxRows() && $num_rows >= $this->GetMaxRows()) ? ' style="display:none"' : '';
			$foot .= '<tr class="newRow"'.$hideNew.'>';
			if (flag_is_set($this->GetOptions(),ALLOW_DELETE)) $foot .= "<td class=\"new-ident\"></td>";
			foreach ($this->fields as $fieldName => $fieldData) {
				if ($fieldData['visiblename'] === NULL) continue;
				$classes=array();
				//if (array_key_exists($fieldName,$this->firsts)) $classes[] = 'sectionFirst';
				$class = count($classes) > 0 ? ' class="'.join(' ',$classes).'"' : '';
				//$this->PreProcess($fieldName,'');
				//$enc_name = $this->GetEncodedFieldName($fieldName);
				if (flag_is_set($fieldData['options'],ALLOW_ADD))
					$foot .= "<td$class>".$this->GetCell($fieldName, NULL).'</td>';
				//if (array_key_exists('inputtype',$fieldData) && $fieldData['inputtype'] !== itNONE && flag_is_set($fieldData['options'],ALLOW_ADD)) {
				//	$foot .= "<td$class>".$this->GetCell($fieldName, NULL).'</td>';
					//$foot .= "<td$class><div id=\"$enc_name\">".$this->DrawSqlInput($fieldName).'</td>';
				//} else
				// TODO: Default value not showing on new records (list)
				//$foot .= "<td$class><div id=\"$enc_name\">".$this->GetLookupValue($fieldName,$this->GetDefaultValue($fieldName)).'</td>';
			}
			$foot .= '</tr>';
		}

		if (!empty($total)) {
			$foot .= '<tr>';
			if (flag_is_set($this->GetOptions(),ALLOW_DELETE)) $foot .= "<td class=\"totals-ident\"></td>";
			foreach ($this->fields as $fieldName => $fieldData) {
				if ($fieldData['visiblename'] === NULL) continue;
				$classes=array();
				//if (array_key_exists($fieldName,$this->firsts)) $classes[] = 'sectionFirst';
				$class = count($classes) > 0 ? ' class="'.join(' ',$classes).'"' : '';
				if (flag_is_set($this->GetOptions(),SHOW_TOTALS) && array_key_exists($fieldName,$total)) {
					$foot .= "<td$class><b>";
					if ($totalShown[$fieldName] != $total[$fieldName])
					$foot .= htmlentities($this->PreProcess($fieldName,$totalShown[$fieldName])).'(shown)<br/>';
					$foot .= htmlentities($this->PreProcess($fieldName,$total[$fieldName]));
					$foot .= '</b></td>';
				} else
				$foot .= "<td$class></td>";
			}
			$foot .= '</tr>';
		}
		if (!empty($foot))
		echo "<tfoot>$foot</tfoot>";

		echo $body;
		// now finish table
		echo "</table>";//"</div>";
		if (!$this->isAjax) echo '</form>';

		$cont = ob_get_contents();
		ob_end_clean();

		utopia::Tab_Add($tabTitle,$cont,$this->GetModuleId(),$tabGroupName,false,$tabOrder);
		utopia::Tab_InitDraw($tabGroupName);
	}

	function DrawRow($row) {
		$pk = $row[$this->GetPrimaryKey()];
		$body = '<tr class="'.cbase64_encode(get_class($this).':'.$pk).'">';
		if (flag_is_set($this->GetOptions(),ALLOW_DELETE)) {
			//$delbtn = utopia::DrawInput($this->CreateSqlField('delete',$row[$this->GetPrimaryKey()],'del'),itBUTTON,'x',NULL,array('class'=>'btn btn-del','onclick'=>'if (!confirm(\'Are you sure you wish to delete this record?\')) return false; uf(this);'));
			$delbtn = $this->GetDeleteButton($row[$this->GetPrimaryKey()]);
			$body .= '<td style="width:1px">'.$delbtn.'</td>';
		}
		foreach ($this->fields as $fieldName => $fieldData) {
			if ($fieldData['visiblename'] === NULL) continue;
			$targetUrl = $this->GetTargetUrl($fieldName,$row);
			$classes=array();
			$class = count($classes) > 0 ? ' class="'.join(' ',$classes).'"' : '';
			$body .= '<td>'.$this->GetCell($fieldName,$row,$targetUrl).'</td>'; //"<td$class$hval$fUrl$fltr id=\"$fldId\">$cellData</td>";
		}
		$body .= "</tr>\n";
		return $body;
	}
}

/**
 * Single form implimentation of uDataModule.
 * Default module for displaying results in a form style. Good for Data Entry.
 */
abstract class uSingleDataModule extends uDataModule {
	public $itemName = 'Item';
	/*
	 public function CreateParentNavButtons() {
		foreach ($this->parents as $parentName => $linkArray){
		if ($parentName !== utopia::GetCurrentModule()) continue;
		foreach ($linkArray as $linkInfo) {
		if ($linkInfo['parentField'] !== NULL) continue; // is linked to fields in the list, skip it
		if (flag_is_set($this->GetOptions(),ALLOW_ADD)) { // create an addition button  --  && utopia::GetCurrentModule() == get_class($this)
		$filters = array('newrec'=>1); // set this filter so that the primary key is negative, this will force no policy to be found, and show a new form
		utopia::AppendVar('footer_left',CreateNavButton('New Record',$this->GetURL($filters),NULL,array('class'=>'btn btn-new')));
		}
		}
		}

		//		parent::CreateParentNavButtons();
		}*/
	public function DeleteRecord($pkVal) {
		parent::DeleteRecord($pkVal);
		AjaxEcho('history.go(-1);');
	}
	public function UpdateField($fieldAlias,$newValue,&$pkVal=NULL) {
		$oldPkVal = $pkVal;
		$ret = parent::UpdateField($fieldAlias,$newValue,$pkVal);
		
		if ($ret === NULL)
			AjaxEcho("window.location.reload(false);");
		elseif ($oldPkVal !== $pkVal) {
			// updated PK
			$url = $this->GetURL($pkVal);
			AjaxEcho("window.location.replace('$url');");
		}
		return $ret;
	}
	
	public $limit = 1;

	public function ShowData(){//$customFilter=NULL) {//,$sortColumn=NULL) {
		//	echo "showdata ".get_class($this)."\n";
		//check pk and ptable are set up
		if (is_empty($this->GetTabledef())) { ErrorLog('Primary table not set up for '.get_class($this)); return; }

		$row = null;
		$num_rows = 0;
		if (!isset($_GET['_n_'.$this->GetModuleId()])) {
			$dataset = $this->GetDataset();
			$num_rows = $dataset->CountRecords();
			$row = $dataset->GetFirst();
		}

		$pagination = '';
		$this->GetLimit($limit);
		if ($limit) {
			$pages = max(ceil($num_rows / $limit),1);
			ob_start();
				utopia::OutputPagination($pages,'_p_'.$this->GetModuleId());
				$pagination = ob_get_contents();
			ob_end_clean();
		}
		$records = ($num_rows == 0) ? "There are no records to display." : 'Total Rows: '.$num_rows;
		$pager = '<div class="right">'.$pagination.'</div>';
			
		uEvents::TriggerEvent('OnShowDataDetail',$this);

		if (flag_is_set($this->GetOptions(),ALLOW_DELETE) && $row) {
			$fltr =& $this->FindFilter($this->GetPrimaryKey(),ctEQ,itNONE);
			$delbtn = $this->GetDeleteButton($this->GetFilterValue($fltr['uid']),'Delete Record');
			utopia::AppendVar('footer_left',$delbtn);
		}

		//		if (!$this->IsNewRecord()) { // records exist, lets get the first.
		// pagination?
		//			if (mysql_num_rows($result) > 1) {
		// multiple records exist in this set, sort out pagination
		//			}
		//		}

		$order = $this->GetSortOrder();
		
		$extraCount = 1;
//		if (!flag_is_set($this->GetOptions(), NO_TABS))
		$tabGroupName = utopia::Tab_InitGroup($this->tabGroup);
		foreach ($this->layoutSections as $sectionID => $sectionInfo) {
			$sectionName = $sectionInfo['title'];
			if ($sectionName === '') {
				if ($sectionID === 0) $SN = 'General';
				else { $SN = "Extra ($extraCount)"; $extraCount++; }
			} else
			$SN = ucwords($sectionName);

			$out = '';
			if (!$this->isAjax) $out .= '<form action="" onsubmit="this.action = window.location" method="post">';
			$out .= "<table class=\"layoutDetailSection\">";

			$fields = $this->GetFields(true,$sectionID);
			$hasFieldHeaders = false;
			foreach ($fields as $fieldName => $fieldData) {
				$hasFieldHeaders = $hasFieldHeaders || !empty($fieldData['visiblename']);
			}

			$fieldCount = count($fields);
			foreach ($fields as $fieldName => $fieldData) {
				$targetUrl = $this->GetTargetUrl($fieldName,$row);

				$out .= "<tr>";
				if ($hasFieldHeaders)
					$out .= "<td class=\"fld\">".$fieldData['visiblename']."</td>";
				$out .= '<td>'.$this->GetCell($fieldName,$row,$targetUrl).'</td>';
				$out .= "</tr>";
			}
			$out .= "</table>";
			if (!$this->isAjax) $out .= '</form>';
			utopia::Tab_Add($SN,$out,$this->GetModuleId(),$tabGroupName,false,$order);
		}

		if ($num_rows > 1) echo '<div class="oh"><b>'.$records.'</b>'.$pager.'</div>';
		utopia::Tab_InitDraw($tabGroupName);
	}
}

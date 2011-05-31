<?php

class tabledef_CMS extends uTableDef {
  public $tablename = 'cms';
  public function SetupFields() {
    //$this->AddField('id',ftNUMBER);
    $this->AddField('cms_id',ftVARCHAR,150);
    $this->AddField('parent',ftVARCHAR,150);
    $this->AddField('rewrite',ftVARCHAR,200);
    $this->AddField('position',ftNUMBER);
    $this->AddField('nav_text',ftVARCHAR,66);
    $this->AddField('template',ftVARCHAR,50);
    $this->AddField('hide',ftBOOL);
    $this->AddField('noindex',ftBOOL);
    $this->AddField('nofollow',ftBOOL);
    $this->AddField('title',ftVARCHAR,66);  // google only shows 66 chars in title
    $this->AddField('description',ftVARCHAR,150); // google only shows 150 chars in description
    $this->AddField('content',ftTEXT);

    $this->AddField('updated',ftTIMESTAMP);
    $this->SetFieldProperty('updated','extra','ON UPDATE CURRENT_TIMESTAMP');
    $this->SetFieldProperty('updated','default','current_timestamp');

    $this->SetPrimaryKey('cms_id');
    $this->SetFieldProperty('position','default',999);
  }
}

class uCMS_List extends uDataModule {
	public function GetTitle() { return 'Page Editor'; }
	public function GetOptions() { return IS_ADMIN | ALLOW_DELETE | ALLOW_FILTER | ALLOW_EDIT; }
	public function GetTabledef() { return 'tabledef_CMS'; }
	public function GetSortOrder() { return GetCurrentModule() == 'uCMS_Edit' ? -500 : parent::GetSortOrder(); }
	public function SetupFields() {
		$this->CreateTable('cms');
		$this->AddField('cms_id','cms_id','cms','Page ID');
//		$this->AddField('is_homepage','is_homepage','cms','Home',itCHECKBOX);
		$this->AddField('is_home','(({parent} = \'\' OR {parent} IS NULL) AND ({position} IS NULL OR {position} = 0))','cms');
		$this->AddField('parent','parent','cms','Parent');
		$this->AddField('position','position','cms','position');
		$this->AddField('title','title','cms','Page Title');
		$this->AddField('nav_text','nav_text','cms');
		$this->AddField('hide','hide','cms','Parent');
	}
	public function SetupParents() {
		$templates = glob(PATH_ABS_TEMPLATES.'*'); // find all templates
		$nTemplates = array('Default Template'=>TEMPLATE_DEFAULT,'No Template'=>TEMPLATE_BLANK);
		if (is_array($templates)) foreach ($templates as $k => $v) {
		        if ($v == '.' || $v == '..' || !is_dir($v)) {
		                unset($templates[$k]);
		                continue;
		        }
		        $nTemplates[basename($v)] = basename($v);
		        //unset($templates[$k]);
		        //$templates[$k] = basename($v);
		}
		//$templates = is_array($templates) ? array_values($templates) : array();
		utopia::SetVar('TEMPLATE_LIST',$nTemplates);
		unset($nTemplates['Default Template']);
		$nTemplates['No Template'] = '';
		modOpts::AddOption('CMS','default_template','Default Template','',itCOMBO,$nTemplates);


		$this->AddParent('internalmodule_Admin');
		$this->AddParent('uCMS_Edit');
		$this->RegisterAjax('reorderCMS',array($this,'reorderCMS'));
	}
	public function ProcessUpdates_del($sendingField,$fieldAlias,$value,&$pkVal = NULL) {
		parent::ProcessUpdates_del($sendingField,$fieldAlias,$value,$pkVal);
		AjaxEcho('window.location.reload();');
	}
	public function RunModule() {
		$tabGroupName = utopia::Tab_InitGroup();
		ob_start();

		$m = utopia::ModuleExists('uCMS_Edit');
		$obj = utopia::GetInstance('uCMS_Edit');
		$newUrl = $obj->GetURL(array($m['module_id'].'_new'=>1));
		$relational = $this->GetNestedArray();
		echo '<table style="width:100%"><tr><td id="tree" style="position:relative;vertical-align:top">';
		echo '<div style="white-space:nowrap"><a class="btn" style="font-size:0.8em" href="'.$newUrl.'">New Page</a><a class="btn" style="font-size:0.8em" href="javascript:t()">Toggle Hidden</a>';

		$modOptsObj = utopia::GetInstance('modOpts');
		$modOptsObj->_SetupFields();
		$row = $modOptsObj->LookupRecord('CMS::default_template');//$this->GetCell($fieldName,$row,$targetUrl)
		echo '<br>Default Template: '.$modOptsObj->GetCell('value',$row,NULL);

		echo '<hr><div style="font-size:0.8em">Click a page below to preview it.</div>';
		self::DrawChildren($relational);
		echo '</div></td>';
		echo '<td style="width:100%;height:100%;vertical-align:top"><iframe style="width:100%;height:100%;min-height:600px" id="previewFrame"></iframe></td></tr></table>';

		treeSort::Init();
		utopia::AddCSSFile(utopia::GetRelativePath(dirname(__FILE__).'/cms.css'));
		echo <<<FIN
		<script>
		var hidden=true;
		function t() {
			if (hidden) $('.hiddenItem').not('#ui-treesort-placeholder').show();
			else $('.hiddenItem').hide();
			hidden = !hidden;
		}
		function RefreshIcons() {
//			$('.ui-treesort-item > .ui-icon').attr('class','');
			$('.ui-treesort-folder').each(function () {
				var icon = $('.ui-icon',this);
				if (!icon.length) icon = $('<span class="ui-icon" style="position:absolute;left:-16px;top:0;bottom:0;width:16px"></span>');
				if ($('ul:visible',this).length)
					icon.removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
				else
					icon.removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
				$(this).prepend(icon);
			});
		}
		function dropped() {
			RefreshIcons();
			data = serialiseTree();
			$.post('?__ajax=reorderCMS',{data:data});
		}
		function serialiseTree() {
			var data = {};
			$('#tree li').each(function () {
				var parent = $(this).parents('.ui-treesort-item:first').attr('id');
				if (!parent) parent = '';
				data[$(this).attr('id')] = parent+':'+$(this).parents('ul:first').children('li').index(this);
			});
			return data;
		}
		$('#tree ul').not($('#tree ul:first')).hide();
		$('#tree').treeSort({init:RefreshIcons,change:dropped});
		$('.ui-icon-triangle-1-e, .ui-icon-triangle-1-s').live('click',function (e) {
			$(this).parent('li').children('ul').toggle();
			RefreshIcons();
			e.stopPropagation();
		});
		</script>
FIN;
		$c = ob_get_contents();
		ob_end_clean();
		utopia::Tab_Add('Page Editor',$c,$tabGroupName,false);
		utopia::Tab_InitDraw($tabGroupName);
	}
	static function DrawChildren($children) {
		if (!$children) return;
		array_sort_subkey($children,'position');
		$editObj = utopia::GetInstance('uCMS_Edit');
		$listObj = utopia::GetInstance('uCMS_List');
		$viewObj = utopia::GetInstance('uCMS_View');

		echo '<ul class="cmsTree">';
		foreach ($children as $child) {
			$hide = $child['hide'] ? ' hiddenItem' : '';

			$editLink = $editObj->GetURL(array('cms_id'=>$child['cms_id']));
			$delLink = $listObj->CreateSqlField('del',$child['cms_id'],'del');
			$data = '';//($child['dataModule']) ? ' <img title="Database Link ('.$child['dataModule'].')" style="vertical-align:bottom;" src="styles/images/data16.png">' : '';

			echo '<li id="'.$child['cms_id'].'" class="cmsItem'.$hide.'">';
			echo $child['title'].$data;
			echo '<div class="cmsItemActions">';
			echo $listObj->GetDeleteButton($child['cms_id']);
			echo '<a class="btn btn-edit" href="'.$editLink.'" title="Edit \''.$child['cms_id'].'\'"></a>';
			echo '</div>';
			self::DrawChildren($child['children'],$child['cms_id']);
			echo '</li>';
		}
		echo '</ul>';
	}

	public function GetNestedArray($parent='') {
		$rows = $this->GetRows();

		$relational = array();
		foreach ($rows as $row) {
			$row['children'] = array();
			$relational[$row['cms_id']] = $row;
		}
		array_sort_subkey($relational,'position');
		$unset = array();
		foreach ($relational as $k=>$i) {
			if ($i['parent'] && array_key_exists($i['parent'],$relational)) {
				$unset[] = $k;
				$relational[$i['parent']]['children'][$k] =& $relational[$k];
			}
		}
		$relational = $relational;
		foreach ($unset as $u) {
			unset($relational[$u]);
		}

		return self::findkey($relational,$parent);
	}
	static function findKey($array,$key = '') {
		if (!$key) return $array;
		$key = strtolower($key);
		$array = array_change_key_case($array,CASE_LOWER);

		if (array_key_exists($key, $array)) return $array[$key];

		foreach ($array as $v) {
			$found = self::findkey($v['children'],$key);
			if ($found) return $found;
		}
		return false;
	}

	public function reorderCMS() {
		utopia::cancelTemplate();
		if (!$_POST['data']) return;
		foreach ($_POST['data'] as $cms_id => $val) {
			list($newParent,$pos) = explode(':',$val);
			$obj = utopia::GetInstance('uCMS_View');
			$oldURL = $obj->GetURL($cms_id);
			$this->UpdateFields(array('parent'=>$newParent,'position'=>$pos),$cms_id);
			$newURL = $obj->GetURL($cms_id);
		}
	}
}
class uCMS_Edit extends uSingleDataModule {
	public function GetTitle() { return 'Edit Content'; }
	public function GetOptions() { return IS_ADMIN | ALLOW_ADD | ALLOW_EDIT | ALLOW_DELETE | ALLOW_FILTER; }
	public function GetTabledef() { return 'tabledef_CMS'; }
	public function SetupFields() {
		$this->CreateTable('cms');
		$this->AddField('cms_id','cms_id','cms','Page ID',itTEXT);
		$this->AddField('link','<a target="_blank" href="'.PATH_REL_ROOT.'{cms_id}.php">'.PATH_REL_ROOT.'{cms_id}.php</a>','cms','View Page');
		$this->AddField('title','title','cms','Page Title',itTEXT);
		$this->AddField('nav_text','nav_text','cms','Menu Title',itTEXT);
		$templates = utopia::GetVar('TEMPLATE_LIST');
		$templates['Default Template'] = '';
		$this->AddField('template','template','cms','Template',itCOMBO,$templates);
//		$this->AddField('position','position','cms','Navigation Position',itTEXT);
		$this->AddField('hide','hide','cms','Hide from Menus',itCHECKBOX);
		$this->AddField('noindex','noindex','cms','noindex',itCHECKBOX);
		$this->AddField('nofollow','nofollow','cms','nofollow',itCHECKBOX);
		$this->FieldStyles_Set('title',array('width'=>'100%'));
		$this->AddField('description','description','cms','Meta Description',itTEXT);
		$this->FieldStyles_Set('description',array('width'=>'100%'));
		$this->AddField('blocks',array($this,'getPossibleBlocks'),'cms','Possible Data Blocks');
		$this->AddField('content','content','cms','Page Content',itHTML);
		$this->FieldStyles_Set('content',array('width'=>'100%','height'=>'20em'));
		$this->AddFilter('cms_id',ctEQ);
	}
	public function UpdateField($fieldAlias,$newValue,&$pkVal=NULL) {
		if ($fieldAlias == 'cms_id') $newValue = UrlReadable($newValue);
		return parent::UpdateField($fieldAlias,$newValue,$pkVal);
	}

	public function getPossibleBlocks($val,$pk,$original) {
		$obj = utopia::GetInstance('uDataBlocks_List');
		$rows = $obj->GetRows();
		foreach (uDataBlocks::$staticBlocks as $blockID => $callback) $rows[]['block_id'] = $blockID;
		$ret = '<div>Click on a block to insert it.</div>';
		foreach ($rows as $row) {
			$ret .= "<span onclick=\"tinyMCE.execCommand('mceInsertContent',false,'{block.'+$(this).text()+'}');\" style=\"margin:0 5px\">{$row['block_id']}</span>";
		}   
		return trim($ret);
	}
	public function SetupParents() {
	}
	public function RunModule() {
		$this->ShowData();
	}
	public static function StartNoProcess() {
		echo '<!-- NoProcess -->';
	}
	public static function StopNoProcess() {
		echo '<!-- /NoProcess -->';
	}
}

utopia::AddTemplateParser('cms','uCMS_View::templateParser');
class uCMS_View extends uSingleDataModule {
	public function GetOptions() { return ALLOW_FILTER; }
	public function GetTabledef() { return 'tabledef_CMS'; }
	public function GetUUID() { return 'cms'; }

	static function last_updated() {
		$page = self::findPage();
		return $page['updated'];
	}
	static function templateParser($id) {
		$obj = utopia::GetInstance('uCMS_View');
		$rec = $obj->GetRows($id);
		$rec = $rec[0];
		return '<div class="mceEditable">'.$rec['content'].'</div>';
	}
	public function GetURL($filters = NULL, $encodeAmp = false) {
	  if (is_array($filters) && array_key_exists('uuid',$filters)) unset($filters['uuid']);
    if (!is_array($filters) && is_string($filters)) $filters = array('cms_id'=>$filters);
    
    $cPage = self::findPage();
    
		$rec = NULL;
		if ($filters && (is_string($filters) || is_array($filters))) {
      if ($cPage && !array_key_exists('cms_id',$filters)) $filters['cms_id'] = $cPage['cms_id'];
			$rec = $this->LookupRecord($filters);
    }

		if (!$rec && $cPage) $rec = $cPage;

		if (!$rec) return $_SERVER['REQUEST_URI'];

		// build QS
		$qs = '';
		if (is_array($filters)) {
			if (array_key_exists('cms_id',$filters)) unset($filters['cms_id']);
			if (array_key_exists('uuid',$filters)) unset($filters['uuid']);
			$qs = http_build_query($filters); if ($qs) $qs = "?$qs";
		}
		$cms_id = $rec['cms_id'];
    $ishome = $rec['is_home'];

		$path = array();
		while ($rec['parent']) {
			$path[] = $rec['parent'];
			$rec = $this->LookupRecord($rec['parent']);
			if (!$rec) break;
		}
		$path = array_reverse($path);

		if (!$ishome) $path[] = $cms_id.'.php';

		return '/'.implode('/',$path).$qs;
	}
	public function GetTitle() {
		$rec = NULL;
		$fltr = $this->FindFilter('cms_id');
		if (!$fltr['value'] || $fltr['value'] == '{cms_id}') {
			$rec = self::GetHomepage();
		}
		if (!$rec) $rec = $this->GetRecord($this->GetDataset(),0);
		return $rec['title'];
	}
	public function SetupFields() {
		$this->CreateTable('cms');
		$this->AddField('cms_id','cms_id','cms','cms_id');
		$this->AddField('title','title','cms','title');
		$this->AddField('updated','updated','cms');
		$this->AddField('parent','parent','cms','Parent');
		$this->AddField('position','position','cms','position');
		$this->AddField('template','template','cms','template');
		$this->AddField('nav_text','nav_text','cms');
		$this->AddField('description','description','cms','description');
		$this->AddField('content','content','cms','content');
		$this->AddField('is_home','(({parent} = \'\' OR {parent} IS NULL) AND ({position} IS NULL OR {position} = 0))','cms');
		$this->AddField('noindex','noindex','cms','noindex');
		$this->AddField('nofollow','nofollow','cms','nofollow');
		$this->AddFilter('cms_id',ctEQ);
	}

	public function SetupParents() {
		uDataBlocks::AddStaticBlock('page_updated','uCMS_View::last_updated');
	}

	static function GetHomepage() {
		$obj = utopia::GetInstance('uCMS_View');
		$row = $obj->LookupRecord(array('is_home'=>'1'));
		if (!$row) $row = $obj->LookupRecord();
		if ($row) return $row;
		return FALSE;
	}

	static function findPage() {
		$uri = $_SERVER['REQUEST_URI'];
		$uri = preg_replace('/(\?.*)?/','',$uri);

		if ($uri == '/') return self::GetHomepage();    

		preg_match('/([^\/]+)(\.php)?$/Ui',$uri,$matches);
		if (array_key_exists(1,$matches)) {
			$obj = utopia::GetInstance('uCMS_View');
			$row = $obj->LookupRecord($matches[1]);
			if ($row) return $row;
		}

		return false;
	}

	public function RunModule() {
		// custom home breadcrumb
		//breadcrumb::ShowHome(false);
		$rec = self::findPage();
		if (empty($rec)) {
			utopia::PageNotFound();
//			header("HTTP/1.0 404 Not Found");
//			echo 'Error 404: File not found'; return;
		}
		
		utopia::UseTemplate(self::GetTemplate($rec['cms_id']));
		
		//breadcrumb::AddURL($rec['nav_text'] ? $rec['nav_text'] : $rec['title'],$this->GetURL(array('cms_id'=>$rec['cms_id'])),-1000);
		utopia::SetTitle($rec['title']);
		utopia::SetDescription($rec['description']);
		$robots = array();
		if ($rec['nofollow']) $robots[] = 'NOFOLLOW';
		if ($rec['noindex']) $robots[] = 'NOINDEX';
		if (!empty($robots)) utopia::AppendVar('<head>','<META NAME="ROBOTS" CONTENT="'.implode(', ',$robots).'">');
		echo '{cms.'.$rec['cms_id'].'}';
	}

	static function GetTemplate($id) {
		$template = NULL;
		while ($id != NULL) {
			$obj = utopia::GetInstance('uCMS_View');
			$rec = $obj->LookupRecord($id);
			if ($rec['template']) { $template = $rec['template']; break; }
			$id = $rec['parent'];
		}
		if (!$template) $template = modOpts::GetOption('CMS','default_template');
		return $template;
	}
}
?>

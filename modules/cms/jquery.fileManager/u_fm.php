<?php
define('itFILEMANAGER' ,'fileman');
class fileManager extends uBasicModule {
	function GetTitle() {
		return 'File Manager';
	}
	function SetupParents() {
		//$this->AddParent('internalmodule_Admin');
		$this->AddParent('uCMS_List');
		$this->RegisterAjax('fileManagerAjax',array($this,'ajax'));
		utopia::AddInputType(itFILEMANAGER,array($this,'show_fileman'));
	}
	function show_fileman($fieldName,$inputType,$defaultValue='',$possibleValues=NULL,$attributes = NULL,$noSubmit = FALSE) {
		list($path) = $this->Init();
		//if (!is_array($attributes)) $attributes = array();
		//$attributes['onclick'] = 'alert("moo");return false;';
		utopia::AppendVar('script_include', <<<FIN
	function filesel(id,item) {
		if (item.type != 0) return;
		$('#fileMan').dialog('close');
		alert(item.fullPath);
		uf(id,item.fullPath,'$fieldName');
	}
//	$(document).ready(function() {
//		$('#fileMan').dialog({autoOpen: false});
//	}
FIN
);
		return '<div id="fileMan"></div>'.utopia::DrawInput($fieldName,itTEXT,$defaultValue,$possibleValues,$attributes,$noSubmit).
			'<input id="'.$fieldName.'" type="button" onclick="$(\'#fileMan\').fileManager({ajaxPath:\''.$path.'\',events:{dblclick:function(event) {filesel(\''.$fieldName.'\',$(this).data(\'item\'))} }}).dialog();" value="Choose File">';
		//return $out.$defaultValue.utopia::DrawInput($fieldName,itBUTTON,'Choose File',$possibleValues,$attributes,$noSubmit);
	}
	function GetOptions() {return IS_ADMIN;}
	function ParentLoad($parent) { $this->RunModule(); }
	function ajax() {
		utopia::CancelTemplate();
		if (array_key_exists('upload',$_GET))
			jqFileManager::ProcessUpload(PATH_UPLOADS);
		else
			jqFileManager::ProcessAjax(PATH_UPLOADS,null,'fileManager::OnRename');
	}
	function Init() {
		utopia::AddJSFile(jqFileManager::GetPathJS());
		utopia::AddCSSFile(jqFileManager::GetPathCSS());
		utopia::AppendVar('script_include', <<<FIN
	function dclick(event) {
		var item = $(this).data('item');
		if (item.type != 0) return;
		window.open(item.fullPath);
	}
FIN
);
		return array($this->GetAjaxPath(),$this->GetAjaxUploadPath());
	}
	function GetAjaxPath() {
		return $this->GetURL(array('__ajax'=>'fileManagerAjax'));
	}
	function GetAjaxUploadPath() {
		return $this->GetURL(array('__ajax'=>'fileManagerAjax','upload'=>1));
	}
	function RunModule() {
		$tabGroupName = utopia::Tab_InitGroup();
		ob_start();
		list($path,$pathUpload) = $this->Init();

		echo '<div id="fileMan"></div>';
		$includeOpts = '';
		if (class_exists('uPlupload')) {
			$jsOptionVar = 'filemanagerOptions';
			uPlupload::Init($jsOptionVar,$pathUpload);
			$includeOpts = ','.$jsOptionVar;
		}
		utopia::AppendVar('script_include', "$(document).ready(function() { $('#fileMan').fileManager({ajaxPath:'$path',events:{dblclick:dclick}}$includeOpts);});");

		$out = ob_get_contents();
		ob_end_clean();
		utopia::Tab_Add($this->GetTitle(),$out,$tabGroupName,false);
		utopia::Tab_InitDraw($tabGroupName);
	}
	static function OnRename($from,$to) {
		// has been renamed.. fix in CMS
		$from = jqFileManager::GetRelativePath($from);
		$to = jqFileManager::GetRelativePath($to);
//		$rows = cubeDB::lookupSimple(cubeCMS::GetTable(),'*','content LIKE \'%'.cubeDB::escape($from).'%\'');
//		foreach ($rows as $row) {
//			$newVal = str_replace($from,$to,$row['content']);
//			cubeDB::updateRecord(cubeCMS::GetTable(),array('content'=>$newVal),array(cubeCMS::GetPrimaryKey()=>$row[cubeCMS::GetPrimaryKey()]));
//		}
	}
}
?>

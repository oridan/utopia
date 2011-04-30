<?php
class uBlob extends uBasicModule {
	function SetupParents() {
		$this->SetRewrite(array('{module}','{field}','{pk}','{filename}'));
	}
	function GetUUID() { return 'blob'; }
	function RunModule() {
		$obj = utopia::GetInstance($_GET['module']);
		$rec = $obj->LookupRecord(mysql_real_escape_string($_GET['pk']));

		if (!$rec || !isset($rec[$_GET['field']])) utopia::PageNotFound();
                utopia::CancelTemplate();

		$fieldName = $_GET['field'];
		$data = $rec[$fieldName];
		
		// allow browsers to auto detect content type
		header('Content-Type:');
		die($data);
	}
}

?>

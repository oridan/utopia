<?php

class tabledef_ModOpts extends uTableDef {
	public $tablename = 'internal_modopts';

	public function SetupFields() {
		// add all the fields in here
		// AddField($name, $type, $length, $collation='', $attributes='', $null='not null', $default='', $extra='', $comments='')
		// SetPrimaryKey($name);

		$this->AddField('ident','varchar',100);
		$this->AddField('value',ftLONGTEXT);
		$this->SetPrimaryKey('ident');
	}
}
class modOpts extends uListDataModule implements iAdminModule {
	public function GetTitle() { return 'Options'; }
	public function GetSortOrder() { return -9999.5; }
	public function GetOptions() { return ALLOW_FILTER | ALLOW_ADD | ALLOW_EDIT | ALLOW_DELETE; }
	public function GetTabledef() { return 'tabledef_ModOpts'; }
	public function SetupFields() {
		$this->CreateTable('opts');
		$this->AddField('ident','ident','opts');
		$this->AddField('group','','');
		$this->AddField('name','','','Name');
		$this->AddField('value','value','opts','Value',itTEXT);
	}
	public function SetupParents() {
		$this->AddParent('/');
		self::AddOption('site_name','Site Name',NULL);
	}
	public function GetRows($filter=NULL,$clearFilters=false) {
		$rows = parent::GetRows($filter,$clearFilters);
		foreach ($rows as $k=>$row) {
			foreach (self::$types as $id=>$t) {
				if ($id == $row['ident']) {
					$rows[$k]['name'] = $t[2];
					$rows[$k]['group'] = $t[3];
				}
			}
		}
		return $rows;
	}
	public function RunModule() {
		$rows = $this->GetRows();
		array_sort_subkey($rows,'group');
		$grouped = array();
		
		foreach ($rows as $r) {
			if (!isset(self::$types[$r['ident']])) continue;
			$grouped[self::$types[$r['ident']][3]][] = $r;
		}
		
		foreach ($grouped as $group=>$g) {
			$order = $group == 'Site Options' ? -10000 : null;
			$this->ShowData($g,$group,$order);
		}
	}
	public static $types = array();
	public static function AddOption($ident,$name,$group=NULL,$init='',$fieldType=itTEXT,$values=NULL) {
		if (!$group) $group = 'Site Options';
		self::$types[$ident] = array($fieldType,$values,$name,$group,$init);
		return self::GetOption($ident);
	}
	public static function GetOption($ident) {
		$obj = utopia::GetInstance(__CLASS__);
		$rec = $obj->LookupRecord($ident);
		if ($rec) return $rec['value'];
		$obj->UpdateFields(array('ident'=>$ident,'value'=>self::$types[$ident][4]));
		if (isset(self::$types[$ident])) return self::$types[$ident][4];
		return FALSE;
	}
	public static function SetOption($ident,$value) {
		$obj = utopia::GetInstance(__CLASS__);
		$obj->UpdateField('value',$value,$ident);
	}
	public function GetCellData($fieldName, $row, $url = '', $inputTypeOverride=NULL, $valuesOverride=NULL) {
		$pk = $this->GetPrimaryKey();
		if ($fieldName == 'value' && isset(self::$types[$row[$pk]])) {
			$inputTypeOverride = self::$types[$row[$pk]][0];
			$valuesOverride = self::$types[$row[$pk]][1];
		}
		return parent::GetCellData($fieldName, $row, $url, $inputTypeOverride, $valuesOverride);
	}
}

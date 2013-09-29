<?php

// this file moves metadata for widgets into the linked tables and removes the __metadata field from all tables

function remove_metadata_field() {
	$ds = database::query('SHOW TABLES');
	while (($t = $ds->fetch(PDO::FETCH_NUM))) {
		try {
			database::query('ALTER TABLE '.$t[0].' DROP `__metadata`');
		} catch (Exception $e) {}
	}
}
uEvents::AddCallback('AfterInit','remove_metadata_field','',MAX_ORDER+MAX_ORDER);


function pull_from_meta_custom() {
	try {
		$obj = utopia::GetInstance('uWidgets',false);
		$obj->BypassSecurity(true);

		$ds = database::query('SELECT * FROM tabledef_Widgets WHERE `block_type` = ? AND `__metadata` IS NOT NULL',array('uCustomWidget'));
		while (($row = $ds->fetch())) {
			$pk = $row['widget_id'];
			$meta = utopia::jsonTryDecode($row['__metadata']);
			foreach ($meta as $field => $val) {
				$obj->UpdateField($field,$val,$pk);
			}
		}
		$obj->BypassSecurity(false);
		
		$ds = database::query('UPDATE tabledef_Widgets SET `__metadata` = NULL WHERE `block_type` = ? AND `__metadata` IS NOT NULL',array('uCustomWidget'));
	} catch (Exception $e) {}
}
uEvents::AddCallback('AfterInit','pull_from_meta_custom');

function pull_from_meta_text() {
	try {
		$obj = utopia::GetInstance('uWidgets',false);
		$obj->BypassSecurity(true);

		$ds = database::query('SELECT * FROM tabledef_Widgets WHERE `block_type` = ? AND `__metadata` IS NOT NULL',array('uTextWidget'));
		while (($row = $ds->fetch())) {
			$pk = $row['widget_id'];
			$meta = utopia::jsonTryDecode($row['__metadata']);
			foreach ($meta as $field => $val) {
				$obj->UpdateField($field,$val,$pk);
			}
		}
		$obj->BypassSecurity(false);
		
		$ds = database::query('UPDATE tabledef_Widgets SET `__metadata` = NULL WHERE `block_type` = ? AND `__metadata` IS NOT NULL',array('uTextWidget'));
	} catch (Exception $e) {}
}
uEvents::AddCallback('AfterInit','pull_from_meta_text');

function pull_from_meta_twitter() {
	try {
		$obj = utopia::GetInstance('uWidgets',false);
		$obj->BypassSecurity(true);

		$ds = database::query('SELECT * FROM tabledef_Widgets WHERE `block_type` = ? AND `__metadata` IS NOT NULL',array('uTwitterWidget'));
		while (($row = $ds->fetch())) {
			$pk = $row['widget_id'];
			$meta = utopia::jsonTryDecode($row['__metadata']);
			foreach ($meta as $field => $val) {
				$obj->UpdateField($field,$val,$pk);
			}
		}
		$obj->BypassSecurity(false);
		
		$ds = database::query('UPDATE tabledef_Widgets SET `__metadata` = NULL WHERE `block_type` = ? AND `__metadata` IS NOT NULL',array('uTwitterWidget'));
	} catch (Exception $e) {}
}
uEvents::AddCallback('AfterInit','pull_from_meta_twitter');
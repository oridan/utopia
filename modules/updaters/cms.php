<?php

function uCMS_publish_update() {
	$o = utopia::GetInstance('uCMS_Edit');
	$o->BypassSecurity(true);
	$ds = $o->GetDataset(array('{content_published_time} > {content_time}'));
	while (($row = $ds->fetch())) {
		$o->UpdateFieldRaw('content_published_time','`content_time`',$row['cms_id']);
	}
	$o->BypassSecurity(false);
}

uEvents::AddCallback('AfterInit','uCMS_publish_update');

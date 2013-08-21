<?php

/**
 * iUtopiaModule: Identifying interface for utopia modules
 */
interface iUtopiaModule {}

/**
 * iRestrictedAccess: used to define modules which utilise User Roles
 */
interface iRestrictedAccess {}

/**
 * iAdminModule: Is an iRestrictedAccess class which also forces ADMIN_TEMPLATE to be used
 */
interface iAdminModule extends iRestrictedAccess {}


/**
 * iWidget: defines a class as a widget and requires the developer to specify the structure and output for the widget
 */
interface iWidget {
	/**
	 * Provides quick access to set up additional fields for the widget instance
	 * @param object $sender uWidget instance
	 */
	static function Initialise($sender);
	
	/**
	 * Echo output based on data
	 * @param array $data uWidget record with all populated information
	 */
	static function DrawData($data);
}

/*
 interface iOutput {
 // ShowData is the main function which processes each field in turn and returns the resulting html to be output to the browser.
 function ShowData();

 // per record
 function OutputRecord();

 function OutputField();

 // returns the cell, including <span id="">
 //	function GetFieldSpan();
 //	function GetCellData();
 }
 */

<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
 
/**
* Camtasia repository object plugin
*
* @author Martin Gorgas
* @version $Id$
*
*/
class ilCamtasiaPlugin extends ilRepositoryObjectPlugin
{
	const CTYPE = 'Services';
	const CNAME = 'Repository';
	const SLOT_ID = 'robj';
	const PNAME = 'Camtasia';
	private static $instance = null;

	public static function getInstance()
	{
		if(null === self::$instance)
		{
			require_once 'Services/Component/classes/class.ilPluginAdmin.php';
			return self::$instance = ilPluginAdmin::getPluginObject(
				self::CTYPE,
				self::CNAME,
				self::SLOT_ID,
				self::PNAME
			);
		}
		return self::$instance;
	}

	public function getPluginName()
	{
		return self::PNAME;
	}

	protected function uninstallCustom()
	{
		global $ilDB;

		$ilDB->dropTable('rep_robj_xcam_data');
		$ilDB->dropTable('rep_robj_xcam_config');
	}
}
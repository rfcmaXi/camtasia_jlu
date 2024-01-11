<?php
 
/**
* Camtasia repository object plugin
*
* @author Martin Gorgas
* @version $Id$
*
*/
class ilCamtasiaPlugin extends ilRepositoryObjectPlugin
{
	function getPluginName(): string
	{
		return "Camtasia";
	}

	protected function uninstallCustom(): void
	{
		global $ilDB;

		$ilDB->dropTable('rep_robj_xcam_data');
		$ilDB->dropTable('rep_robj_xcam_config');
	}

	public function allowCopy(): bool
	{
		return false;
	}
}
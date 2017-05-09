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
	function getPluginName()
	{
		return "Camtasia";
	}
}
?>

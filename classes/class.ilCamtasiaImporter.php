<?php

require_once("./Services/Export/classes/class.ilXmlImporter.php");
ilCamtasiaPlugin::getInstance()->includeClass('class.ilObjCamtasia.php');
ilCamtasiaPlugin::getInstance()->includeClass('class.ilCamtasiaXMLParser.php');

/**
 * Class ilCamtasiaImporter
 *
 * @author Martin Gorgas
 */
class ilCamtasiaImporter extends ilXmlImporter {


	public function init()
	{
		$this->xml_file = $this->getImportDirectory().'/Plugins/xcam/set_1/export.xml';
	}

	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
	// todo	
	}

}
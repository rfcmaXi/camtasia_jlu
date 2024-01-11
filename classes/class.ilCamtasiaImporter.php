<?php

require_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Class ilCamtasiaImporter
 *
 * @author Martin Gorgas
 */
class ilCamtasiaImporter extends ilXmlImporter {

	/**
	 * @var ilObjInteractiveVideo | null
	 */
	protected $xcam_object = null;

	/**
	 * @var string
	 */
	protected $xml_file;


	public function init(): void
	{
		$this->xml_file = $this->getImportDirectory().'/Plugins/xcam/set_1/export.xml';
	}

	public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
	{
		global $tree, $ilDB;
		
		$this->init();

		if($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id))
		{
			$ref_ids = ilObject::_getAllReferences($new_id);
			$ref_id  = current($ref_ids);

			$parent_ref_id = $tree->getParentId($ref_id);

			$this->xcam_object = ilObjectFactory::getInstanceByObjId($new_id, false);
			$this->xcam_object->setRefId($ref_id);
		}
		else
		{
			$this->xcam_object = new ilObjCamtasia();
			$parser = new ilCamtasiaXMLParser($this->xcam_object, $this->getXmlFile());
			$parser->setImportDirectory($this->getImportDirectory());
			$parser->startParsing();
			$this->xcam_object = $parser->getObjCamtasia();

			$this->xcam_object->create();
			
			ilFileUtils::rCopy($this->getImportDirectory().'/Plugins/xcam/set_1/expDir_1/objects/',$this->xcam_object->getDataDirectory());
		}
		$a_mapping->addMapping('Plugins/xcam', 'xcam', $a_id, $this->xcam_object->getId());
	}

	/**
	 * @param $xml_file
	 */
	private function setXmlFile($xml_file)
	{
		$this->xml_file = $xml_file;
	}

	public function getXmlFile()
	{
		return $this->xml_file;
	}
}
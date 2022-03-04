<?php

require_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Class ilCamtasiaExporter
 *
 * @author Martin Gorgas
 */
class ilCamtasiaExporter extends ilXmlExporter {

	/**
	 * Get xml representation
	 *
	 * @param    string        entity
	 * @param    string        schema version
	 * @param    string        id
	 * @return    string        xml string
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id) 
	{
		ilCamtasiaPlugin::getInstance()->includeClass('class.ilObjCamtasia.php');

		$ref_id = current(ilObject::_getAllReferences($a_id));
		$this->obj_id		= $a_id;
		$this->object		= new ilObjCamtasia($ref_id);
		$this->xml_writer	= new ilXmlWriter();
		$this->export_dir	= $this->getAbsoluteExportDirectory();
		$date				= time();
		$this->sub_dir		= $date . '_' . IL_INST_ID . '_' . "xcam" . '_' . $a_id;
		$this->filename		= $this->sub_dir . ".xml";

		$this->exportPagesXML();

		return $this->xml_writer->xmlDumpMem();
	}

	public function init() 
	{
		// TODO: Implement init() method.
	}

	public function getValidSchemaVersions($a_entity) 
	{
		return array(
			'5.2.0' => array(
				'namespace'    => 'http://www.ilias.de/',
				#'xsd_file'     => 'xtsf_5_1.xsd',
				'uses_dataset' => false,
				'min'          => '5.2.0',
				'max'          => ''
			)
		);
	}

	public function exportPagesXML()
	{
		$attr         = array();
		$attr["Type"] = "ilCamtasia";
		$this->xml_writer->xmlStartTag("ContentObject", $attr);

		// MetaData
		$this->exportXMLMetaData();

		// Settings
		$this->exportXMLSettings();

		$this->xml_writer->xmlEndTag("ContentObject");
	}

	public function exportXMLMetaData()
	{
		require_once 'Services/MetaData/classes/class.ilMD2XML.php';
		$md2xml = new ilMD2XML($this->object->getId(), 0, $this->object->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$this->xml_writer->appendXML($md2xml->getXML());
	}
	
	private function exportXMLSettings()
	{
		$this->xml_writer->xmlStartTag('Settings');

		$this->xml_writer->xmlElement('Title', null, (string)$this->object->getTitle());
		$this->xml_writer->xmlElement('Description', null, (string)$this->object->getDescription());
		$this->xml_writer->xmlElement('Online', null, (int)$this->object->getOnline());
		
		$this->xml_writer->xmlElement('HTTP-Stream', null, (string)$this->object->gethttp());
		$this->xml_writer->xmlElement('Playerfile', null, (string)$this->object->getPlayerFile());
		
		$this->exportFiles();
		
		$this->xml_writer->xmlEndTag('Settings');
	}

	/**
	 * create camtasia package
	 */
	function exportFiles()
	{
		$this->xml_writer->xmlStartTag('HTMLFiles',);
		$this->xml_writer->xmlElement('HTMLSource', null, $this->object->getType()."_".$this->object->getId());
		$this->doExportCamtasiaSource($this->object->getId(), $this->xml_writer, $this->export_dir);
		$this->xml_writer->xmlEndTag('HTMLFiles');
	}
	
	public function doExportCamtasiaSource($obj_id, $xml_writer, $export_path)
	{
		ilUtil::makeDirParents($export_path . '/objects');
		$source_dir = $this->object->getDataDirectory();
		ilUtil::rCopy($source_dir, $export_path . '/objects');
		
		$xml_writer->xmlElement('Objects', null, $export_path . '/objects'); what is it good for?
	}
}
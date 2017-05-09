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
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id) {
        // TODO: Implement XML?.
	}

	public function init() {
		// TODO: Implement init() method.
	}

	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top. Example:
	 *
	 *        return array (
	 *        "4.1.0" => array(
	 *            "namespace" => "http://www.ilias.de/Services/MetaData/md/4_1",
	 *            "xsd_file" => "ilias_md_4_1.xsd",
	 *            "min" => "4.1.0",
	 *            "max" => "")
	 *        );
	 *
	 *
	 * @return        array
	 */
	public function getValidSchemaVersions($a_entity) {
		// no need?
	}
}
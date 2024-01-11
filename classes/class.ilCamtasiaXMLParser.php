<?php

require_once 'Services/Xml/classes/class.ilSaxParser.php';

/**
 * Class ilCamtasiaXMLParser
 *
 * @author Martin Gorgas
 */
class ilCamtasiaXMLParser extends ilSaxParser {

	/**
	 * @var ilObjCamtasia
	 */
	protected $xcam_obj;
	protected $cdata = '';

	/**
	 * @param ilObjCamtasia $xcam_obj
	 * @param                      $xmlFile
	 */
	public function __construct($xcam_obj, $xmlFile)
	{
		$this->xcam_obj			= $xcam_obj;
		$this->inSettingsTag	= false;
		$this->inMetaDataTag	= false;
		$this->inMdGeneralTag	= false;
		parent::__construct($xmlFile);
	}
	
	/**
	 * @param $xmlParser
	 * @param $tagName
	 * @param $tagAttributes
	 */
	public function handlerBeginTag($xmlParser, $tagName, $tagAttributes)
	{
		$a = 0;
		switch($tagName)
		{
			case 'MetaData':
				#$this->inMetaDataTag = true;
				break;

			case 'General':
				if($this->inMetaDataTag)
				{
					$this->inMdGeneralTag = true;
				}
				break;

			case 'Description':
				if($this->inMetaDataTag && $this->inMdGeneralTag)
				{
					$this->cdata = '';
				}
				break;

			case 'Settings':
				$this->inSettingsTag = true;
				break;
			case 'Online':
			case 'HTTP-Stream':
			case 'Playerfile':
				if($this->inSettingsTag)
				{
				$this->cdata = '';
				}
				break;
			case 'SetTitle':
			case 'SetDescription':
				$this->cdata = '';
				break;
		}
	}

	/**
	 * @param $xmlParser
	 * @param $tagName
	 */
	public function handlerEndTag($xmlParser, $tagName)
	{
		switch($tagName)
		{
			case 'MetaData':
				$this->inMetaDataTag = false;
				break;

			case 'General':
				if($this->inMetaDataTag)
				{
					$this->inMdGeneralTag = false;
				}
				break;

			case 'Title':
				#if($this->inMetaDataTag && $this->inMdGeneralTag)
				{
					$this->xcam_obj->setTitle(trim($this->cdata));
					$this->cdata = '';
				}
				break;

			case 'Description':
				#if($this->inMetaDataTag && $this->inMdGeneralTag)
				{
					$this->xcam_obj->setDescription(trim($this->cdata));
					$this->cdata = '';
				}
				break;

			case 'Settings':
				if($this->inSettingsTag)
				{
					$this->inSettingsTag = false;
				}
			break;

			case 'Online':
				$this->xcam_obj->setOnline(trim($this->cdata));
				$this->cdata = '';
				break;

			case 'HTTP-Stream':			
				$this->xcam_obj->sethttp(trim($this->cdata));
				$this->cdata = '';
				break;
				
			case 'Playerfile':
				$this->xcam_obj->setPlayerFileImported(trim($this->cdata));
				$this->cdata = '';
				break;
		}
	}

	/**
	 * @param $xmlParser
	 */
	public function setHandlers($xmlParser): void
	{
		xml_set_object($xmlParser, $this);
		xml_set_element_handler($xmlParser, 'handlerBeginTag', 'handlerEndTag');
		xml_set_character_data_handler($xmlParser, 'handlerCharacterData');
	}

	/**
	 * @return ilObjCamtasia
	 */
	public function getObjCamtasia()
	{
		return $this->xcam_obj;
	}

	/**
	 * Set import directory
	 *
	 * @param	string	import directory
	 */
	public function setImportDirectory($a_val)
	{
		$this->importDirectory = $a_val;
	}

	/**
	 * Get import directory
	 *
	 * @return	string	import directory
	 */
	public function getImportDirectory()
	{
		return $this->importDirectory;
	}

	public function handlerCharacterData($xmlParser, $charData)
	{
		if($charData != "\n")
		{
			// Replace multiple tabs with one space
			$charData = preg_replace("/\t+/", " ", $charData);

			$this->cdata .= $charData;
		}
	}

}
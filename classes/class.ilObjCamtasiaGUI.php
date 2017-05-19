<?php
require_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once("./Modules/File/classes/class.ilObjFileGUI.php");
require_once("./Services/Form/classes/class.ilFileInputGUI.php");
require_once("./Services/Utilities/classes/class.ilFileUtils.php");

/**
* User Interface class for the camtasia repository object.
*
* User interface classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author Martin Gorgas
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjCamtasiaGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjCamtasiaGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI
*
*
*/
class ilObjCamtasiaGUI extends ilObjectPluginGUI
{
	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd)
	{
		$this->setTitleAndDescription();
        switch ($cmd)
		{
			case "importCamtasiaAction": // list all commands that need write permission here
			case "uploadCamtasiaForm":
            case "initImportForm":
            case "ilexportgui":
            case "exportHTML":
				$this->checkPermission("write");
				$this->$cmd();
				break;
           
			case "showContent":	// list all commands that need read permission here
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}

	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
	}

	/**
	 * After object has been created -> jump to this command
	 */
	function getAfterCreationCmd()
	{
		return "uploadCamtasiaForm";
	}

	/**
	 * Get standard command
	 */
	function getStandardCmd()
	{
		return "showContent";
        
	}

	/**
	* Get type.
	*/
	final function getType()
	{
		return "xcam";
	}

     /**
	 * @param string $type
	 * @return array
	 */
    protected function initCreationForms($type)
	{
		return array(
			self::CFORM_NEW => $this->initCreateForm($type)
		);
	}

		/**
	 * @param string $type
	 * @return ilPropertyFormGUI
	 */
    	public function  initCreateForm($type)
	{
		$form = parent::initCreateForm($type);
        $this->plugin->includeClass('class.ilObjCamtasia.php');
        
        // Send additional information
        $form->setDescription($this->txt('limitations'));
        
        //Instant Online
        $online = new ilCheckboxInputGUI($this->lng->txt('online'), 'online');
		$form->addItem($online);
        
        // Http-Stream
		$ht = new ilTextInputGUI($this->txt("stream"), "stream");
        $ht->setMaxLength(128);
        $ht->setSize(40);
        $ht->setRequired(true);
        //Example URL
        $ht->setInfo($this->txt("stream_info") . " " . ilObjCamtasia::getEXURL());
		$form->addItem($ht);  
        
        // Template or new Zip?
        $si = new ilRadioGroupInputGUI($this->txt("filesw"), "filesw");
        $si->setRequired(true);
        
        $si2 = new ilRadioOption($this->txt("new_file"), "new_file");
        $in_file = new ilFileInputGUI($this->txt("upload_file"), "upload_file");
        $in_file->setRequired(true);
        $in_file->setSuffixes(array("zip", "ZIP"));
        $si2->addSubItem($in_file);
        $si->addOption($si2);
        
        $tt = new ilRadioOption($this->txt("tafel_template"), "tafel_template");
        $tt->setInfo(ilObjCamtasia::getTempfile() . " " . $this->txt("template_info"));
        $si->addOption($tt);
        $si->setValue("new_file");
        $form->addItem($si);
        
        return $form;
    }
    

	/**
	* Upload CamtasiaZipFile. This commands uses the form class to display an input form.
	*/
	function uploadCamtasiaForm()
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab("upload");
        $this->initImportForm($this->getType());
        $this->getPropertiesValues();
		$tpl->setContent($this->form->getHTML());
	}

	protected function initImportForm($a_new_type)
	{
		global $lng, $tpl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form_gui = new ilPropertyFormGUI();
        $form_gui->setTitle($this->txt("new_Camtasia"));
		//$form_gui->setMultipart(TRUE);
		
        // title
        $tt = new ilTextInputGUI($this->txt("title"), "title");
        $tt->setRequired(true);
        $tt->setValue($this->object->getTitle());
        $form_gui->addItem($tt);
        
        // description
		$ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
		$form_gui->addItem($ta);
        
        // online
		$cb = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$form_gui->addItem($cb);
        
        // Http-Stream
		$tht = new ilNonEditableValueGUI($this->txt("http"), "http");
		$form_gui->addItem($tht);
        
        // player file
		$ti = new ilTextInputGUI($this->txt("player_file"), "playerfile");
        $ti->setInfo($this->txt("player_file_info"));
		$ti->setMaxLength(200);
        //$ti->setRequired(true);
		$ti->setSize(80);
		$form_gui->addItem($ti);

        // add form for new file
        $this->addnewFile($form_gui);
		
		// Buttons
        $form_gui->addCommandButton("importCamtasiaAction", $this->txt("importCamtasiaAction"));
		//$form_gui->addCommandButton("cancel", $this->txt("cancel")); //no need
		
		$form_gui->setFormAction($this->ctrl->getFormAction($this, "importCamtasiaAction"));

		$this->form=$form_gui;
		return $form_gui;
	}

	/*
	 * Import Action
	 * set by initImportForm
	 */
    
    private function addnewFile(ilPropertyFormGUI $form_gui)
     {   
        $header_tr = new ilFormSectionHeaderGUI();
		$header_tr->setTitle($this->txt('new_file_title'));
        $header_tr->setInfo($this->txt("limitations2"));
		$form_gui->addItem($header_tr);
         
        // new upload
		$upl = new ilCheckboxInputGUI($this->txt("newfileform"), "newfile");
         
        // Http-Stream
		$ht = new ilTextInputGUI($this->txt("stream"), "stream");
        $ht->setMaxLength(128);
        $ht->setSize(40);
        $ht->setRequired(true);
        //Example URL
        $ht->setInfo($this->txt("stream_info") . " " . $this->object->getEXURL());
		$upl->addSubItem($ht);
        
		// Template or new Zip?
        $si = new ilRadioGroupInputGUI($this->txt("filesw"), "filesw");
        $si->setRequired(true);
        
        $tt = new ilRadioOption($this->txt("tafel_template"), "tafel_template"); 
        $tt->setInfo($this->object->getTempfile() . " " . $this->txt("template_info"));
        $si2 = new ilRadioOption($this->txt("new_file"), "new_file");
        $si->addOption($tt);
        $si->addOption($si2);
        $upl->addSubItem($si);
         
        $in_file = new ilFileInputGUI($this->txt("upload_file"), "upload_file");
        //$in_file->setRequired(true); // conflict with file_switch
        $in_file->setSuffixes(array("zip", "ZIP"));
        $in_file->setAllowDeletion(true); 
        $upl->addSubItem($in_file);

		$form_gui->addItem($upl);
    }
    
    
	public function importCamtasiaAction()
	{
		global $ilErr, $tpl;
        
        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$this->checkPermissionBool("write", "", $this->getType()))
		{
			$ilErr->raiseError($this->txt("no_create_permission"));
		}
        
        $this->initImportForm($a_new_import);

        if ($this->form->checkInput())
                    
		{
            $stream = $this->form->getInput("stream");
            $filesw = $this->form->getInput("filesw");
            $online = $this->form->getInput("online");
            $new_update = $this->form->getInput("newfile");
            
            if ($new_update == 0) { 
                $this->updateProperties();}
            
            $server = $this->object->getVideoserver();
            if (($new_update == 1) && (preg_match("#$server#", $stream))) {
                 $this->importAsXCAMModule($stream, $filesw, $online);}    
        }  
            ilUtil::sendFailure($this->txt("no_link"), true);
            $this->form->setValuesByPost();
		    $tpl->setContent($this->form->getHtml());  
    }

	private function importAsXCAMModule($stream, $filesw, $online)
	{   
        // cleanup
        $data = $this->object->getDataDirectory('local');
        ilUtil::delDir($data);
         
        // template or new zip?   
            if ($filesw == "new_file") { 
                $tempdir = $this->object->extractToTemporaryDir(); }
            if ($filesw == "tafel_template"){
                $tempdir = $this->object->extractToTemporaryDirTemplate(); }

        //$this->plugin->includeClass('class.ilObjCamtasia.php');
		$newObj = new ilObjCamtasia($this->object->getRefId());
        
        $newObj->populateByDirectory($tempdir);
		$newObj->sethttp($stream);
        $newObj->setOnline($online);
        ilUtil::delDir($tempdir);
        
        // Auto-detect startfile
        $startSuffix = "_player.html";
	    $files = array();
        ilFileUtils::recursive_dirscan($newObj->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->object->endsWith($file, $startSuffix)){
					$newObj->setPlayerFile(str_replace($newObj->getDataDirectory()."/", "", $files["path"][$idx]).substr($file, 0, strlen($file) - 12).".html");
					break;}}}        
        // no content-menu
        $nomenuSuffix = "_player.html";
        $click= 'id="tscVideoContent"';
        $noclick= 'id="tscVideoContent" oncontextmenu="return false"';
	    $files = array();
        ilFileUtils::recursive_dirscan($newObj->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->object->endsWith($file, $nomenuSuffix)){
					$this->object->patchFile($files["path"][$idx].$file, $click, $noclick);
					break;}}}
        // one title for all
        $titleSuffix = "_player.html";
        $start = '<title>';
        $end  = '</title>';
	    $files = array();
        ilFileUtils::recursive_dirscan($newObj->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->object->endsWith($file, $titleSuffix)){
					$this->object->patchFileBetween($files["path"][$idx].substr($file, 0, strlen($file) - 12).".html", $start, $end, $this->object->getTitle());
                    break;}}}  
        // replace http-stream for mp4
        $streamSuffix = "_config.xml";
        $start = '<rdf:li xmpDM:name="0" xmpDM:value="';
        $end  = '"/>';
        $files = array();
        ilFileUtils::recursive_dirscan($newObj->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->object->endsWith($file, $streamSuffix)){
					$this->object->patchFileBetween($files["path"][$idx].$file, $start, $end, $stream);
					break;}}}            
        $streamSuffix2 = "_player.html";
        $start = 'TSC.playerConfiguration.addMediaSrc("';
        $end  = '");';
        $files = array();
        ilFileUtils::recursive_dirscan($newObj->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->object->endsWith($file, $streamSuffix2)){
					$this->object->patchFileBetween($files["path"][$idx].$file, $start, $end, $stream);
					break;}}}
        
        $newObj->doUpdate();
		
        // are this camtasia files?
            if ($newObj->getPlayerFile() != "")
                { ilUtil::sendSuccess($this->txt("file_patched"), true); }
            else
                { ilUtil::sendFailure($this->txt("file_not_patched"), true); }  
	
        $this->ctrl->redirect($this, "uploadCamtasiaForm");
        }

	// --
	/**
	 * Set tabs
	 */
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;

		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addNonTabbedLink("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"), '_blank');
		}

		// settings tab with write permission
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("upload", $this->txt('upload'), $ilCtrl->getLinkTarget($this, "uploadCamtasiaForm"));
        }
            
        // standard info screen tab
		$this->addInfoTab();

		//  export tab with write permission
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
            $ilTabs->addTab("export", $this->txt("export"), $ilCtrl->getLinkTargetByClass("ilexportgui", ""));
		}

		// standard epermission tab
		$this->addPermissionTab();
	}

	/**
	 * Get values for edit properties form
	 */
	function getPropertiesValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
        $values["http"] = $this->object->gethttp();
		$values["online"] = $this->object->getOnline();
		$values["playerfile"] = $this->object->getPlayerFile();
        $values["filesw"] = "new_file";
		$this->form->setValuesByArray($values);
	}
    
	/**
	 * Update properties
	 */
	public function updateProperties()
	{
		global $tpl, $lng, $ilCtrl;

		$this->initImportForm($a_new_import2);
		if ($this->form->checkInput())
		{
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$this->object->setPlayerFile($this->form->getInput("playerfile"));
            $this->object->setOnline($this->form->getInput("online"));
            
            // one title for all
            $titleSuffix = "_player.html";
            $start = '<title>';
            $end  = '</title>';
	     	$files = array();
		    ilFileUtils::recursive_dirscan($this->object->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->object->endsWith($file, $titleSuffix)){
					$this->object->patchFileBetween($files["path"][$idx].substr($file, 0, strlen($file) - 12).".html", $start, $end, $this->object->getTitle());
                    break;}}}
            
            $this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "uploadCamtasiaForm");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	/**
	 * Show content
	 */
	function showContent()
	{
		global $tpl, $ilTabs;
		global $ilUser;

		$playerFile = $this->object->getPlayerFile();

		if ($playerFile != "")
		{
			$playerFileFullPath = $this->object->getDataDirectory().'/'.$playerFile;
            ilUtil::redirect($playerFileFullPath); 
		} else {
			$ilTabs->activateTab("Content");
            ilUtil::sendFailure($this->txt("no_record"));
		}
	}
    
    /**
	 * Export content
	 */ 
    public function executeCommand() 
    {
		global $ilTabs, $tpl;
		
        $next_class = $this->ctrl->getNextClass($this);
		switch ($next_class) {
			case 'ilexportgui':
				$tpl->setTitle($this->object->getTitle());;
				$tpl->setTitleIcon(ilObject::_getIcon($this->object->getId()));
				$this->setLocator();
				$tpl->getStandardTemplate();
				$this->setTabs();
				include_once './Services/Export/classes/class.ilExportGUI.php';
				$ilTabs->activateTab("export");
				$exp = new ilExportGUI($this);
				//$exp->addFormat('xml'); // no need for xml export
                $exp->addFormat("html", "", $this, "exportHTML");
				$this->ctrl->forwardCommand($exp);
				$tpl->show();
				return;
				break;
		}
		$return_value = parent::executeCommand();
		return $return_value;
	}
    
    /**
	 * create html package
	 */
	function exportHTML()
	{
		$inst_id = IL_INST_ID;
		include_once("./Services/Export/classes/class.ilExport.php");
		
		ilExport::_createExportDirectory($this->object->getId(), "html",
			$this->object->getType());
		$export_dir = ilExport::_getExportDirectory($this->object->getId(), "html",
			$this->object->getType());
		
		$subdir = $this->object->getType()."_".$this->object->getId();
		$filename = $this->subdir.".zip";
		$target_dir = $export_dir."/".$subdir;
		ilUtil::delDir($target_dir);
		ilUtil::makeDir($target_dir);
		$source_dir = $this->object->getDataDirectory();
		ilUtil::rCopy($source_dir, $target_dir);
		// zip it all
		$date = time();
		$zip_file = $export_dir."/".$date."__".IL_INST_ID."__".
			$this->object->getType()."_".$this->object->getId().".zip";
		ilUtil::zip($target_dir, $zip_file);
		ilUtil::delDir($target_dir);
	}
}
?>

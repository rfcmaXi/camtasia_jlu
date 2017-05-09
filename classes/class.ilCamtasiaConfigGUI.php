<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
/**
 * eLecture configuration user interface class
 *
 * @author Martin Gorgas
 * @version $Id$
 *
 */
class ilCamtasiaConfigGUI extends ilPluginConfigGUI
{
	/* handles all commmands, default is "configure" */
	function performCommand($cmd)
	{

		switch ($cmd)
		{
			case "configure":
			case "save":
				$this->$cmd();
				break;
		}
	}

	/* configure scree */
	function configure()
	{
		global $tpl;
		$form = $this->initConfigurationForm();
		$tpl->setContent($form->getHTML());
	}
	
	/* configuration form */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl;
		
		$pl = $this->getPluginObject();
	    $pl->includeClass("class.ilObjCamtasia.php");    
        
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
	
		//Videoserver
		$ti = new ilTextInputGUI($pl->txt("videoserver_url"), "videoserver_url");
        $ti->setValue(ilObjCamtasia::getVideoserver());
		$ti->setRequired(true);
        $ti->setInfo($pl->txt("videoserver_info"));
  		$form->addItem($ti);
        
        //Example URL
		$ex = new ilTextInputGUI($pl->txt("ex_url"), "ex_url");
		$ex->setValue(ilObjCamtasia::getEXURL());
		$ex->setRequired(true);
        $ex->setInfo($pl->txt("ex_info"));
  		$form->addItem($ex);
        
        //Template File
		$ex = new ilTextInputGUI($pl->txt("template_file"), "template_file");
		$ex->setValue(ilObjCamtasia::getTempfile());
		$ex->setRequired(true);
        $ex->setInfo($pl->txt("tempfile_info"));
  		$form->addItem($ex);
        
		$form->addCommandButton("save", $lng->txt("save"));
		$form->setTitle($pl->txt("configuration"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		return $form;
	}
	
	/* save to db */
	public function save()
	{
		global $tpl, $lng, $ilCtrl;
	
		$pl = $this->getPluginObject();
        
		$form = $this->initConfigurationForm();
        
		if ($form->checkInput())
		{
			$server = $form->getInput("videoserver_url");
			$exurl = $form->getInput("ex_url");
            $tempfile = $form->getInput("template_file");
            
            //Check tempfile
            if (file_exists(substr($_SERVER['SCRIPT_FILENAME'], 0, -10). "/Customizing/global/plugins/Services/Repository/RepositoryObject/Camtasia/templates/".$tempfile)) {
            
            $this->setConfig($server, $exurl, $tempfile);            
			ilUtil::sendSuccess($pl->txt("config_saved"), true);
			$ilCtrl->redirect($this, "configure");}
            
            else {
            ilUtil::sendFailure($pl->txt("no_ttfile"), true);
            $form->setValuesByPost();
			$tpl->setContent($form->getHtml());   }
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}

        private function setConfig($a_val, $b_val, $c_val, $d_val)
	{
		global $ilDB;
	
		$ilDB->manipulate("DELETE FROM rep_robj_xcam_config");
		$ilDB->manipulate("INSERT INTO rep_robj_xcam_config (videoserver,exurl,tempfile) VALUES (" . $ilDB->quote($a_val, "text") . "," . $ilDB->quote($b_val, "text"). "," . $ilDB->quote($c_val, "text")  . ")");
    }
    
}
?>
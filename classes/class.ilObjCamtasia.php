<?php
include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
* Application class for the camtasia repository object.
*
* @author Martin Gorgas
*
* $Id$
*/
class ilObjCamtasia extends ilObjectPlugin
{

	protected $online; // [bool]

	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);

	}

	/**
	 * Get type.
	 */
	final function initType()
	{
		$this->setType("xcam");
	}

	/**
	 * Create object
	 */
	function doCreate()
	{
		global $ilDB;
		
		// Import-Mode - should only be set if an xcam module was imported
		if ($this->gethttp() !== NULL) {
			
			$ilDB->manipulate("INSERT INTO rep_robj_xcam_data ".
			"(id, http, is_online, player_file) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote($this->gethttp(), "text").",".
			$ilDB->quote($this->getOnline(), "integer").",".
			$ilDB->quote($this->getPlayerFile(), "text").
			")");
			
			return;
		}	

		// Clone-Mode - should only be set if an xcam module was copied
		if (!isset($_POST['stream'])) {
			
			$ilDB->manipulate("INSERT INTO rep_robj_xcam_data ".
			"(id, http, is_online, player_file) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote("x", "text").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote("x", "text").
			")");
			
			return;
		}

		// Create-Mode if stream is correct
		$server = $this->getVideoserver();
		if (preg_match("#$server#", $_POST['stream'])) {
		
		$ilDB->manipulate("INSERT INTO rep_robj_xcam_data ".
			"(id, http, is_online, player_file) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote("x", "text").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote("x", "text").
			")");

        // Import content
        // template or new zip?
        if ($_POST['filesw'] == "new_file") { 
            $tempdir = $this->extractToTemporaryDir(); }
        if ($_POST['filesw'] == "tafel_template"){
            $tempdir = $this->extractToTemporaryDirTemplate(); }
        $this->populateByDirectory($tempdir);
		ilUtil::delDir($tempdir);
        
        $this->sethttp($_POST['stream']);
        $this->setOnline($_POST['online']);
        
        // Auto-detect startfile
        $startSuffix = "_player.html";
	    $files = array();
        ilFileUtils::recursive_dirscan($this->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->endsWith($file, $startSuffix)){
					$this->setPlayerFile(str_replace($this->getDataDirectory()."/", "", $files["path"][$idx]).substr($file, 0, strlen($file) - 12).".html");
					break;}}}        
        // no right-click
        $nomenuSuffix = "_player.html";
        $click= 'id="tscVideoContent"';
        $noclick= 'id="tscVideoContent" oncontextmenu="return false"';
	    $files = array();
        ilFileUtils::recursive_dirscan($this->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->endsWith($file, $nomenuSuffix)){
					$this->patchFile($files["path"][$idx].$file, $click, $noclick);
					break;}}}
        // one title for all
        $titleSuffix = "_player.html";
        $start = '<title>';
        $end  = '</title>';
	    $files = array();
        ilFileUtils::recursive_dirscan($this->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->endsWith($file, $titleSuffix)){
					$this->patchFileBetween($files["path"][$idx].substr($file, 0, strlen($file) - 12).".html", $start, $end, $this->getTitle());
                    break;}}}  
        // replace http-stream for mp4
        $streamSuffix = "_config.xml";
        $start = '<rdf:li xmpDM:name="0" xmpDM:value="';
        $end  = '"/>';
        $files = array();
        ilFileUtils::recursive_dirscan($this->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->endsWith($file, $streamSuffix)){
					$this->patchFileBetween($files["path"][$idx].$file, $start, $end, $this->gethttp());
					break;}}}            
        $streamSuffix2 = "_player.html";
        $start = 'TSC.playerConfiguration.addMediaSrc("';
        $end  = '");';
        $files = array();
        ilFileUtils::recursive_dirscan($this->getDataDirectory(), $files);
		    if (is_array($files["file"])) {
			  foreach($files["file"] as $idx => $file){
				$chk_file = null;
				if ($this->endsWith($file, $streamSuffix2)){
					$this->patchFileBetween($files["path"][$idx].$file, $start, $end, $this->gethttp());
					break;}}}
        
        $this->doUpdate();
        // are this camtasia files?
        if ($this->getPlayerFile() != "")
            { ilUtil::sendSuccess($this->txt("file_patched"), true); }
        else
            { ilUtil::sendFailure($this->txt("file_not_patched"), true); }
        return;
        }
        
        // Error if stream is not correct
		else
		{
		ilUtil::sendFailure($this->txt("no_link"), true);
		ilUtil::redirect($_SERVER['HTTP_REFERER']);
		} 
	}

	/**
	 * Read data from db
	 */
	function doRead()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM rep_robj_xcam_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
		);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->sethttp($rec["http"]);
            $this->setOnline($rec["is_online"]);
			$this->setPlayerFile($rec["player_file"]);
		}
	}
    
    /**
	 * Update data
	 */
	function doUpdate()
	{
		global $ilDB;

		$ilDB->manipulate($up = "UPDATE rep_robj_xcam_data SET ".
            " http = ".$ilDB->quote($this->gethttp(), "text").",".
            " is_online = ".$ilDB->quote($this->getOnline(), "integer").",".
			" player_file = ".$ilDB->quote($this->getPlayerFile(), "text").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
		);
	}

	/**
	 * Delete data from db
	 */
	public function doDelete() {
		global $ilDB;

		// Delete object
		$ilDB->manipulate("DELETE FROM rep_robj_xcam_data WHERE id = ".
                $ilDB->quote($this->getId(), "integer")
        );
		// Delete content of data-directory
		ilUtil::delDir($this->getDataDirectory());
	}


	/**
	 * Check if database entry exists already.
	 * @return bool
	 */
	function doExist()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM rep_robj_xcam_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
		);
		if ($ilDB->fetchAssoc($set) == NULL) {
			return false;
		}

		return true;
	}

    /**
	 * Do Cloning
	 */    
    
    function doCloneObject($new_obj, $a_target_id, $a_copy_id = NULL) {
        $new_obj->cloneStructure($this->getRefId());
        //return $new_obj;
	}
    
    function cloneStructure($original_id) {
		$original = new ilObjCamtasia($original_id);
        ilUtil::rCopy($original->getDataDirectory(), $this->getDataDirectory());
        $this->sethttp($original->gethttp());
		$this->setPlayerFile($original->getPlayerFile());
        $this->setOnline(false); // Copy must be offline
        $this->doUpdate();
        
        // After Cloning forward to $new_obj settings tab        
        ilUtil::sendSuccess($this->txt("copy_successful"), true);
        ilUtil::redirect("ilias.php?baseClass=ilObjPluginDispatchGUI&cmd=forward&ref_id=".$this->getRefId()."&forwardCmd=uploadCamtasiaForm");
        
        
    }
   
    /**
	 * Setter and Getter
	 */ 
	    
    function gethttp()
	{
        return $this->http;
	}
    
    function sethttp($a_http)
	{
		$this->http = $a_http;
	}    
    
    function setOnline($a_online)
	{
		$this->online = $a_online;
	}
    
	function getOnline()
	{
		return $this->online;
	}

	function getPlayerFile()
	{
		return $this->player_file;
	}
	
	function setPlayerFileImported($a_file)
	{
			$this->player_file = $a_file;
	}

	function setPlayerFile($a_file, $a_omit_file_check = false)
	{
		if($a_file &&
		(file_exists($this->getDataDirectory()."/".$a_file) || $a_omit_file_check))
		{
			$this->player_file = $a_file;
		}
	}

	function getDataDirectory($mode = "filesystem")
	{
		global $ilLog;
		$cam_data_dir = ilUtil::getWebspaceDir($mode)."/xcam_data";
		ilUtil::makeDir($cam_data_dir);
		$ilLog->write("Creating data dir: ".$cam_data_dir);

		$cam_dir = $cam_data_dir."/xcam_".$this->getId();
		ilUtil::makeDir($cam_dir);

		return $cam_dir;
	}

    public static function getTempfile()
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT * FROM rep_robj_xcam_config");
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["tempfile"];
		}
		return null;
	}
    
    public static function getEXURL()
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT * FROM rep_robj_xcam_config");
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["exurl"];
		}
		return null;
	}
    
    public static function getVideoserver()
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT * FROM rep_robj_xcam_config");
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["videoserver"];
		}
		return null;
	}
    
    function populateByDirectory($a_dir)
	{
		ilUtil::rCopy($a_dir, $this->getDataDirectory());
		ilUtil::renameExecutables($this->getDataDirectory());
	}
    
    public function extractToTemporaryDir()
	{
		$tmpdir = ilUtil::ilTempnam();
		ilUtil::makeDir($tmpdir);
		$temp_name = $_FILES["upload_file"]["tmp_name"];
		$filename= $_FILES["upload_file"]["name"];
		ilUtil::moveUploadedFile($temp_name, $filename, $tmpdir . "/" . $filename);
		ilUtil::unzip($tmpdir."/".$filename);
		unlink($tmpdir."/".$filename);
        return $tmpdir;
	}
    
    public function extractToTemporaryDirTemplate()
	{	
		$tmpdir = ilUtil::ilTempnam();
		ilUtil::makeDir($tmpdir);
        $filename= $this->getTempfile();
        $temp_name = substr($_SERVER['SCRIPT_FILENAME'], 0, -10). "/Customizing/global/plugins/Services/Repository/RepositoryObject/Camtasia/templates/".$filename;;
		copy($temp_name, $tmpdir."/".$filename);
		ilUtil::unzip($tmpdir."/".$filename);
        unlink($tmpdir."/".$filename);
        return $tmpdir;
    }
   
	public function endsWith($haystack, $needle) {
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}
    
    public function patchFile($file, $old, $new) {
		if (is_file($file)) {
			$content = file_get_contents($file);
			$content = str_replace($old, $new, $content);
			file_put_contents($file, $content); }
	}
    
    public function patchFileBetween($file, $start, $end, $new) {
		if (is_file($file)) {
			$content = file_get_contents($file);
			$content = preg_replace('#('.preg_quote($start).')(.*?)('.preg_quote($end).')#si', '$1'.$new.'$3', $content);
			file_put_contents($file, $content);}
	} 

    /**
	 * Returns files for embedding the player via object/embed/iframe tag
	 */
	public function getFullscreenPlayer() {
		$player = $this->getPlayerFile();
		$pos    = strrpos($player, '.html');
		if ($pos) {
			$player    = substr_replace($player, '_player.html', $pos, strlen('.html'));
			$directory = $this->getDataDirectory();
			return "{$directory}/{$player}";
		}

		return false;
	}
	public function getEmbedCSS() {
		$player = $this->getPlayerFile();
		$pos    = strrpos($player, '.html');
		if ($pos) {
			$player    = substr_replace($player, '_embed.css', $pos, strlen('.html'));
			$directory = $this->getDataDirectory();
			return "{$directory}/{$player}";
		}

		return false;
	}
}
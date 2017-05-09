<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

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

		$ilDB->manipulate("INSERT INTO rep_robj_xcam_data ".
			"(id, http, is_online, player_file) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote("x", "text").",".
            $ilDB->quote(0, "integer").",".
			$ilDB->quote("x", "text").
			")");
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
		$id = $ilDB->quote($this->getId(), 'integer');
		$ilDB->manipulate("DELETE FROM rep_robj_xcam_data WHERE id = ${id}");

		// Delete content of data-directory
		$directory = $this->getDataDirectory();
		ilUtil::delDir($directory);
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
	function doClone($a_target_id,$a_copy_id,$new_obj)
	{
		global $ilDB;

		$new_obj->setOnline($this->getOnline());
		$new_obj->setPlayerFile($this->getPlayerFile());
		$new_obj->update();
	}

	    
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

	/**
	 * Populate by directory. Copy file from the temp ($a_dir) folder to the public web folder.
	 *
	 * @param
	 * @return
	 */
	function populateByDirectory($a_dir)
	{
		ilUtil::rCopy($a_dir, $this->getDataDirectory());
		ilUtil::renameExecutables($this->getDataDirectory());
	}
    
    function getTempfile()
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT * FROM rep_robj_xcam_config");
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["tempfile"];
		}
		return null;
	}
    
    function getEXURL()
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT * FROM rep_robj_xcam_config");
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["exurl"];
		}
		return null;
	}
    
    function getVideoserver()
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT * FROM rep_robj_xcam_config");
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["videoserver"];
		}
		return null;
	}
    
    function getBackup()
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT * FROM rep_robj_xcam_config");
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["backup"];
		}
		return null;
	}
    
}
?>

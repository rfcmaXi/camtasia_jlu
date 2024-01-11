<?php
/**
* Access/Condition checking for the camtasia repository object
* 
* This class is just an empty stub - ilias expects to find a class of this name
* it will never be uses
* 
*/
class ilObjCamtasiaAccess extends ilObjectPluginAccess
{
    /**
     * Checks wether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     *
     * Please do not check any preconditions handled by
     * ilConditionHandler here. Also don't do usual RBAC checks.
     *
     * @param	string		$a_cmd			command (not permission!)
     * @param	string		$a_permission	permission
     * @param	int			$a_ref_id		reference id
     * @param	int			$a_obj_id		object id
     * @param	int			$a_user_id		user id (if not provided, current user is taken)
     *
     * @return	boolean		true, if everything is ok
     */
     
    public function _checkAccess(string $a_cmd, string $a_permission, int $a_ref_id, int $a_obj_id, ?int $a_user_id = null): bool
    {
        global $ilUser, $ilAccess;
        
        // TODO: very ugly workaround to support copy operations since ILIAS 5.2
		// check with 'isset' to prevent crashes when property is changed to 'private'
		global $objDefinition;
		if (isset($objDefinition->obj_data))
		{
			$objDefinition->obj_data['xcam']['allow_copy'] = 1;
		}
                
        if ($a_user_id == "")
        {
            $a_user_id = $ilUser->getId();
        }

        switch ($a_permission)
        {
            case "read":
            case "visible":
				if (!ilObjCamtasiaAccess::checkOnline($a_obj_id) &&
					!$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))
				{
					return false;
				}
				break;
        }

        return true;
    }

    /**
     * Check online status
     */
    static function checkOnline($a_id)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT is_online FROM rep_robj_xcam_data ".
            " WHERE id = ".$ilDB->quote($a_id, "integer")
        );
        $rec  = $ilDB->fetchAssoc($set);
        return (boolean) $rec["is_online"];
    }
    /**
     * Check Playerfile status
     */
    static function checkPlayerfile($a_id)
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT player_file FROM rep_robj_xcam_data".
            " WHERE id = ".$ilDB->quote($a_id, "integer")
        );                    
		$rec  = $ilDB->fetchAssoc($set);
		return (boolean) $rec["player_file"];
	}
}
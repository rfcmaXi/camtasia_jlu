<?php
/**
 * ListGUI implementation for Example object plugin. This one
 * handles the presentation in container items (categories, courses, ...)
 * together with the corresponfing ...Access class.
 *
 * PLEASE do not create instances of larger classes here. Use the
 * ...Access class to get DB data and keep it small.
 */
class ilObjCamtasiaListGUI extends ilObjectPluginListGUI
{

    /**
     * Init type
     */
    function initType()
    {
        $this->setType("xcam");
        $this->copy_enabled = true;
    }

    /**
     * Get name of gui class handling the commands
     */
    public function getGuiClass(): string
    {
        return "ilObjCamtasiaGUI";
    }

    /**
     * Get commands
     */
    public function initCommands(): array
    {
        return array
        (
            array(
                "permission" => "read",
                "cmd" => "showContent",
                "default" => true),
            array(
                "permission" => "write",
                "cmd" => "uploadCamtasiaForm",
                "txt" => $this->txt("edit"),
                "default" => false),
        );
    }

    /**
     * Get item properties
     *
     * @return        array                array of property arrays:
     *                                "alert" (boolean) => display as an alert property (usually in red)
     *                                "property" (string) => property name
     *                                "value" (string) => property value
     */
    public function getProperties(): array
    {
        global $lng;

        $props = array();

        if (!ilObjCamtasiaAccess::checkOnline($this->obj_id))
        {
            $props[] = array("alert" => true, "property" => $this->txt("status"),
                "value" => $this->txt("offline"));
        }
        else if (!ilObjCamtasiaAccess::checkPlayerfile($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("no_start_file"));
		}

        return $props;
    }

  /**
   * Get link targets/window
   */
    public function getCommandFrame(string $cmd): string
    {
        switch ($cmd) {
            case 'showContent':
                return ilFrameTargetInfo::_getFrame('ExternalContent');
            default:
                return '';
        }
    }
}
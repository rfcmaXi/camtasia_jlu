<#1>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'http' => array(
        'type' => 'text',
        'length' => 200,
        'fixed' => false,
        'notnull' => false
    ),
    'is_online' => array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false
    ),
    'player_file' => array(
        'type' => 'text',
        'length' => 200,
        'fixed' => false,
        'notnull' => false
    )
);

$ilDB->createTable("rep_robj_xcam_data", $fields);
$ilDB->addPrimaryKey("rep_robj_xcam_data", array("id"));    
?>
    
<#2>
<?php
$fields = array(
		'videoserver' => array(
				'type' => 'text',
				'length' => 255,
				'notnull' => true
        ),
        'exurl' => array(
				'type' => 'text',
				'length' => 255,
				'notnull' => true
        ),
        'tempfile' => array(
				'type' => 'text',
				'length' => 255,
				'notnull' => true
        ),
        'backup' => array(
				'type' => 'integer',
                'length' => 1,
                'notnull' => false
        )
);   
$ilDB->createTable("rep_robj_xcam_config", $fields);
?>

<#3>
<?php
$ilDB->dropTableColumn("rep_robj_xcam_config", "backup");
?>

<#4>
<?php
	/**
	* Check whether type exists in object_data, if not, create the type
	* The type is normally created at plugin activation, see ilRepositoryObjectPlugin::beforeActivation()
	*/
	$set = $ilDB->query("SELECT obj_id FROM object_data WHERE type='typ' AND title = 'xcam'");
	if ($rec = $ilDB->fetchAssoc($set))
	{
		$typ_id = $rec["obj_id"];
	}
	else
	{
		$typ_id = $ilDB->nextId("object_data");
		$ilDB->manipulate("INSERT INTO object_data ".
		"(obj_id, type, title, description, owner, create_date, last_update) VALUES (".
		$ilDB->quote($typ_id, "integer").",".
		$ilDB->quote("typ", "text").",".
		$ilDB->quote("xcam", "text").",".
		$ilDB->quote("Plugin Camtasia", "text").",".
		$ilDB->quote(-1, "integer").",".
		$ilDB->quote(ilUtil::now(), "timestamp").",".
		$ilDB->quote(ilUtil::now(), "timestamp").
		")");
	}
	/**
	* Add new RBAC copy operations
	*/
	$operations = array('copy');
	foreach ($operations as $operation)
	{
		$query = "SELECT ops_id FROM rbac_operations WHERE operation = ".$ilDB->quote($operation, 'text');
		$res = $ilDB->query($query);
		$row = $ilDB->fetchObject($res);
		$ops_id = $row->ops_id;
		$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ("
		.$ilDB->quote($typ_id, 'integer').","
		.$ilDB->quote($ops_id, 'integer').")";
		$ilDB->manipulate($query);
	}
?>
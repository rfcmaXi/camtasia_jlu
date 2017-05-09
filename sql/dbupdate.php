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
				'type' => 'boolean'
        )
);   
$ilDB->createTable("rep_robj_xcam_config", $fields);
?>
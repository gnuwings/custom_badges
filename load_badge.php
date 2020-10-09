<?php

require_once(dirname(__FILE__).'/../../config.php');

$courseid = optional_param('value', '', PARAM_RAW);
global $DB;
	
	$badgeobj = $DB->get_records('badge',array('courseid'=>$courseid));
		 	$badges= array();
		 	$badges= array('0'=>"Options");

    if ($badgeobj) {

        foreach($badgeobj as $badge){
		$badges[$badge->id] = $badge->name; 
		}
    }
   

			echo json_encode($badges);
	
	
?>

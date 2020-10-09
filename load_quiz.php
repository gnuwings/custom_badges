<?php

require_once(dirname(__FILE__).'/../../config.php');

$courseid = optional_param('value', '', PARAM_RAW);
global $DB;
	
	$quizobj = $DB->get_records('quiz',array('course'=>$courseid));
		 	$quizzes= array();
		 	$quizzes= array('0'=>"Options");

    if ($quizobj) {

        foreach($quizobj as $quiz){
		$quizzes[$quiz->id] = $quiz->name; 
		}
    }
   

			echo json_encode($quizzes);
	
	
?>

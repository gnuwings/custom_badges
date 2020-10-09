<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class create_badge_form extends moodleform {

    function definition() {
        global $CFG,$DB;
    
        $mform = $this->_form;

		$courseobj = $DB->get_records_sql("SELECT * FROM {course} where id > 1");
		$courses= array('0'=>"Select a course");
		foreach($courseobj as $course){
				$courses[$course->id] = $course->fullname; 
		}
		
		if(isset($this->_customdata['id'])){
			$id = $this->_customdata['id'];
			$custom_badge = $DB->get_record('block_custom_badge',array('id'=>$id));
			$courseid=$custom_badge->courseid;
			$quizobj = $DB->get_records('quiz',array('course'=>$courseid));
		 	$quizes= array();
		 	$quizes= array('0'=>"Options");

			if ($quizobj) {

				foreach($quizobj as $quiz){
				$quizes[$quiz->id] = $quiz->name; 
				}
			}
			$badgeobj = $DB->get_records('badge',array('courseid'=>$courseid));
		 	$badges= array();
		 	$badges= array('0'=>"Options");

			if ($badgeobj) {

				foreach($badgeobj as $badge){
				$badges[$badge->id] = $badge->name; 
				}
			}
		}
		else{
			$quizes= "";
			$badges= "";
		}
		
		$startmark =10;
		$endmark =100;
		$increment =5;
		$marks = array();
		for($startmark; $startmark <=$endmark;$startmark=$startmark+$increment ){
			$marks[$startmark] = $startmark;
		}

		$status = array('0'=>'Active','1'=>'Inactive');

		$mform->addElement('select', 'courseid', get_string('selectcourse','block_custom_badge'),$courses);
		$mform->setType('courseid', PARAM_TEXT);
        $mform->addRule('courseid', null, 'required', null, 'client');	
       
		$mform->addElement('select', 'quizid', get_string('selectquiz','block_custom_badge'),$quizes);
		$mform->setType('quizid', PARAM_TEXT);
        $mform->addRule('quizid', null, 'required', null, 'client');	

		$mform->addElement('select', 'mark', get_string('selectmark','block_custom_badge'),$marks);
		$mform->setType('mark', PARAM_TEXT);
        $mform->addRule('mark', null, 'required', null, 'client');	

		$mform->addElement('select', 'badgeid', get_string('selectbadge','block_custom_badge'),$badges);
		$mform->setType('badgeid', PARAM_TEXT);
        $mform->addRule('badgeid', null, 'required', null, 'client');	

		$mform->addElement('select', 'status', get_string('status', 'block_custom_badge'),$status);
		$mform->setType('status', PARAM_TEXT);
        $mform->addRule('status', null, 'required', null, 'client');
        
        if(isset($this->_customdata['id'])){
			$mform->setDefault('courseid', $custom_badge->courseid);
			$mform->setDefault('quizid', $custom_badge->quizid);
			$mform->setDefault('mark', $custom_badge->mark);
			$mform->setDefault('status', $custom_badge->status);
			$mform->setDefault('badgeid', $custom_badge->badgeid);
			$mform->addElement('hidden', 'id', $id);
			$mform->setType('id', PARAM_INT);

		}
        $this->add_action_buttons();


      
    }

  
}


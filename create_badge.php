<?php

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/create_badge_form.php');

require_login(null, false);

$id = optional_param('id', '', PARAM_INT);   
$message = optional_param('message', '', PARAM_RAW);  

$title = get_string('pluginname', 'block_custom_badge');
$heading = $SITE->fullname;
$url = '/blocks/custom_badge/create_badge.php';

$baseurl = new moodle_url($url);

$PAGE->set_url($url);
$PAGE->set_pagelayout('course');
$PAGE->set_context(context_system::instance());
$PAGE->set_title($title);
$PAGE->set_heading($heading);
$PAGE->set_cacheable(true);
$PAGE->requires->jquery();
$PAGE->requires->js('/blocks/custom_badge/js/custom.js', true);

if($id)
    $mform = new create_badge_form(null,array('id'=>$id));
else
    $mform = new create_badge_form();

// If data submitted, then process and store.
if ($mform->is_cancelled()) {
			redirect("view_badge.php"); 
   
} else if ($data = $mform->get_data()) {
	//print_r($data);
	//print_r($_POST);
	$data->issuer = $USER->id;
	$data->quizid = $_POST['quizid'];
	$data->badgeid = $_POST['badgeid'];
	if($data->quizid > 0 && $data->badgeid >0){
		$custom_badge = $DB->get_record('block_custom_badge',array('courseid'=>$data->courseid,
					'quizid'=>$data->quizid,'mark'=>$data->mark,'badgeid'=>$data->badgeid));
		if($custom_badge)
		  			redirect("create_badge.php?message=cr"); 

		if(isset($data->id)){
			$data->timemodified  = time();
			$DB->update_record('block_custom_badge', $data);
			redirect("view_badge.php"); 
		}
		else{
			$data->timecreated   = time();
			$data->timemodified  = time();
			$data->id = $DB->insert_record('block_custom_badge', $data);
			redirect("view_badge.php"); 
		}
	}
	else
  			redirect("create_badge.php?message=error"); 

    
}

echo $OUTPUT->header();
if($message=="error")
	echo "<span class='errors'> Please select all values </span>";
if($message=="cr")
	echo "<span class='errors'> The selected criteria is already exist </span>";
$mform->display();


    
echo $OUTPUT->footer();


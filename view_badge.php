<?php

require(dirname(__FILE__).'/../../config.php');
require_login(null, false);

// PERMISSION.
$id       = optional_param('id', 0,PARAM_INT);  
$action = optional_param('status', '', PARAM_RAW);    

$title = get_string('pluginname', 'block_custom_badge');
$heading = $SITE->fullname;
$url = '/blocks/custom_badge/view_badge.php';

$baseurl = new moodle_url($url);

$PAGE->set_url($url);
$PAGE->set_pagelayout('course');
$PAGE->set_context(context_system::instance());
$PAGE->set_title($title);
$PAGE->set_heading($heading);
$PAGE->set_cacheable(true);


if($action){

		if($action == "delete"){
			$custom_badge=$DB->delete_records('block_custom_badge',array('id'=>$id));
            redirect("view_badge.php");

		}
        $custom_badge=$DB->get_record('block_custom_badge',array('id'=>$id));
        $record = new stdClass();
        $record->id   = $custom_badge->id ;

        if($action == "inactive")
            $record->status   = 1;
        if($action == "active")
			$record->status   = 0;

        $record->timemodified  = time();
       $DB->update_record('block_custom_badge', $record);
         
        redirect("view_badge.php");
}

$sql = "SELECT * from {block_custom_badge} ";
$custom_badges = $DB->get_records_sql($sql);

echo $OUTPUT->header();
	echo '<p align="right"><a href=create_badge.php>'.get_string('create_badge', 'block_custom_badge').'</a></p>';

 if(!empty($custom_badges)){
        $table = new html_table();
        $table->tablealign="left";
        $table->head  = array(get_string('course', 'block_custom_badge'),
								get_string('quiz', 'block_custom_badge'),
								get_string('badge', 'block_custom_badge'),
								get_string('criteria', 'block_custom_badge'),
								get_string('status', 'block_custom_badge'),
								get_string('timecreated', 'block_custom_badge'),
								get_string('timemodified', 'block_custom_badge'),
								get_string('actions', 'block_custom_badge'));
        $table->align = array('centre');
        $table->width = '50%';
        $table->attributes['class'] = 'generaltable';
        $table->data = array();

    foreach ($custom_badges as $custom_badge) {
		 if($custom_badge->status == 0 ){
			$status = 'Active'; 
			$action = html_writer::link(new moodle_url('view_badge.php',array('id' =>$custom_badge->id,'status'=>'inactive')), html_writer::empty_tag('img', array('src'=>$OUTPUT->image_url('i/hide'), 'alt'=>'', 'class'=>'iconsmall')), array('title'=>'Inactive'));  
			//$action = $OUTPUT->single_button(new moodle_url('view_badge.php', array('id' =>$custom_badge->id,'status'=>'inactive')), 'Inactive');
		}
		else{
			$status = 'Inactive';
			$action = html_writer::link(new moodle_url('view_badge.php', array('id' =>$custom_badge->id,'status'=>'active')), html_writer::empty_tag('img', array('src'=>$OUTPUT->image_url('i/show'), 'alt'=>'', 'class'=>'iconsmall')), array('title'=>'Active'));  
			//$action = $OUTPUT->single_button(new moodle_url('view_badge.php', array('id' =>$custom_badge->id,'status'=>'active')), 'Active');
		}
			$edit = html_writer::link(new moodle_url('create_badge.php',array('id'=>$custom_badge->id)), html_writer::empty_tag('img', array('src'=>$OUTPUT->image_url('i/edit'), 'alt'=>'', 'class'=>'iconsmall')), array('title'=>'Edit'));
			$delete = html_writer::link(new moodle_url('view_badge.php',array('id'=>$custom_badge->id,'status'=>'delete')), html_writer::empty_tag('img', array('src'=>$OUTPUT->image_url('i/invalid'), 'alt'=>'', 'class'=>'iconsmall')), array('title'=>'Delete'));

			//$edit = $OUTPUT->single_button(new moodle_url('create_badge.php', array('id' =>$custom_badge->id)), 'Edit');

		$course = $DB->get_record('course',array('id'=>$custom_badge->courseid));
		$quiz = $DB->get_record('quiz',array('id'=>$custom_badge->quizid));
		$badge = $DB->get_record('badge',array('id'=>$custom_badge->badgeid));
        $table->data[] = array($course->fullname,
								$quiz->name,
								$badge->name,
								$custom_badge->mark.'%',
								$status,
								date('d /m /Y',$custom_badge->timecreated),
								date('d /m /Y',$custom_badge->timemodified),
								$edit." ".$action." ".$delete);
    }
    echo html_writer::table($table);        
    }

    
echo $OUTPUT->footer();


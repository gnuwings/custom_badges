<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . "/lib/filelib.php");      // File handling on description and friends.

class block_custom_badge extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_custom_badge');
    }

    public function specialization() {
        global $USER;
        if ($title = get_config('block_custom_badge', 'blockname')) {
            $this->title = $title;
        } else {
            $this->title = get_string('blocktitle', 'block_custom_badge');
        }
       
    }

    public function get_content() {
        global $CFG, $OUTPUT, $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        $this->content->text = '';

		

        if(is_siteadmin($USER)){
               $this->content->text .= '<a href="'.$CFG->wwwroot.'/blocks/custom_badge/create_badge.php">'.get_string('create_badge', 'block_custom_badge').'</a>';
               $this->content->text .= '<br><a href="'.$CFG->wwwroot.'/blocks/custom_badge/view_badge.php">'.get_string('view_badge', 'block_custom_badge').'</a>';
          
                return $this->content;
            
        }

        return $this->content;
    }

    public function applicable_formats() {
        return array(
            'all' => false,
            'site' => true,
            'course-*' => false,
            'my' => true
        );
    }

    public function instance_allow_multiple() {
        return false;
    }

  
}

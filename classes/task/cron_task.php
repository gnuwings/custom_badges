<?php

namespace block_custom_badge\task;
require_once($CFG->libdir . '/badgeslib.php');
require_once($CFG->dirroot . '/badges/lib/awardlib.php');

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
 public function get_name() {
        return get_string('crontask', 'block_custom_badge');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG,$DB;
        echo "custom_badge cron";
        $sql = "SELECT * from {block_custom_badge} ";
		$custom_badges = $DB->get_records_sql($sql);
		$attemptssql = "SELECT * FROM {quiz_attempts}
							WHERE state = 'finished' AND sumgrades IS NOT NULL AND quiz = ?";
							
		$noofquestionsql = "SELECT COUNT(qs.questionid) as qnum
						  FROM {quiz_slots} qs, {question} q WHERE q.id = qs.questionid
						  AND qs.quizid = ? AND q.qtype != ?";
						  	
				  			
		foreach($custom_badges as $custom_badge){
			$quizid = $custom_badge->quizid;
			$mark = $custom_badge->mark;
			$badge = $DB->get_record('badge',array('id'=>$custom_badge->badgeid));
			$badgeclass = new \badge($custom_badge->badgeid);
			$userattempts = $DB->get_records_sql($attemptssql, array($quizid));
			$noofquestion = $DB->get_record_sql($noofquestionsql, array($quizid, 'description'));
			if ($badge->expireperiod || $badge->expiredate) {
				
				$expiry = null;
				$timestamp = time();

				if (isset($badge->expiredate)) {
					$expiry = $badge->expiredate;
				} else if (isset($badge->expireperiod)) {
					$expiry = $timestamp + $badge->expireperiod;
				}
				$dateexpire = $expiry;	
			}
			else {
				$dateexpire = null;
			}
			
			if ($badge->type == BADGE_TYPE_SITE) {
				$context= \context_system::instance();
			} else if ($badge->type == BADGE_TYPE_COURSE) {
				$context = \context_course::instance($badge->courseid);
			}	

       
			foreach($userattempts as $attempt){
				$score = ($attempt->sumgrades/$noofquestion->qnum )*100;
				$score= round($score);
				if($score >= $mark){
					$custom_badge_issued = $DB->get_record('block_custom_badge_issued', array('customid'=>$custom_badge->id,'userid'=>$attempt->userid));
					if(!$custom_badge_issued){
						$now = time();
						$record = new \stdClass();
						$record->customid   = $custom_badge->id ;
						$record->badgeid   = $custom_badge->badgeid ;
						$record->userid   = $attempt->userid ;
						$record->uniquehash = sha1(rand() . $attempt->userid . $badge->id . $now);
						$record->dateissued   = time() ;
						$record->dateexpire   = $dateexpire ;
						$record->visible   = 1 ;
						$record->visible = get_user_preferences('badgeprivacysetting', 1, $attempt->userid);
						$record->issuernotified   = time() ;
						$customresult = $DB->insert_record('block_custom_badge_issued', $record);
						$result = $DB->insert_record('badge_issued', $record);
						
						
						if ($result) {
							// Trigger badge awarded event.
							$eventdata = array (
								'context' => $context,
								'objectid' => $badge->id,
								'relateduserid' => $attempt->userid,
								'other' => array('dateexpire' => $dateexpire, 'badgeissuedid' => $result)
							);
							\core\event\badge_awarded::create($eventdata)->trigger();
							
							// Lock the badge, so that its criteria could not be changed any more.
							if ($badge->status == BADGE_STATUS_ACTIVE) {
								$badgeclass->set_status(BADGE_STATUS_ACTIVE_LOCKED);
							}
							
							// Update details in criteria_met table.
							$compl = $badgeclass->get_criteria_completions($attempt->userid);
							foreach ($compl as $c) {
								$obj = new \stdClass();
								$obj->id = $c->id;
								$obj->issuedid = $result;
								$DB->update_record('badge_criteria_met', $obj, true);
							}

							/*if (!$nobake) {
								// Bake a badge image.
								$pathhash = $badgeclass->badges_bake($record->uniquehash, $badge->id, $attempt->userid, true);

								// Notify recipients and badge creators.
								$badgeclass->badges_notify_badge_award($badge, $attempt->userid, $record->uniquehash, $pathhash);
							}*/
						}
						
						
						// Get badge creator.
						//$creator = $DB->get_record('user', array('id' => $custom_badge->issuer), '*', MUST_EXIST);
						$creator = $DB->get_record('user', array('id' => $attempt->userid), '*', MUST_EXIST);//for testing
						$creatorsubject = get_string('creatorsubject', 'badges', $badge->name);
						$creatormessage = '';
						$issuedlink = \html_writer::link(new \moodle_url('/badges/badge.php', array('hash' => $record->uniquehash)), $badge->name);
						$recipient = $DB->get_record('user', array('id' => $attempt->userid), '*', MUST_EXIST);
echo $creator->email;
						$a = new \stdClass();
						$a->user = fullname($recipient);
						$a->link = $issuedlink;
						$creatormessage .= get_string('creatorbody', 'badges', $a);
						
						$admin = get_admin();
						$userfrom = new \stdClass();
						$userfrom->id = $admin->id;
						$userfrom->email = !empty($CFG->badges_defaultissuercontact) ? $CFG->badges_defaultissuercontact : $admin->email;
						foreach (get_all_user_name_fields() as $addname) {
							$userfrom->$addname = !empty($CFG->badges_defaultissuername) ? '' : $admin->$addname;
						}
						$userfrom->firstname = !empty($CFG->badges_defaultissuername) ? $CFG->badges_defaultissuername : $admin->firstname;
						$userfrom->maildisplay = true;

						// Create a message object.
						$eventdata = new \core\message\message();
						$eventdata->courseid          = SITEID;
						$eventdata->component         = 'moodle';
						$eventdata->name              = 'badgecreatornotice';
						$eventdata->userfrom          = $userfrom;
						$eventdata->userto            = $creator;
						$eventdata->notification      = 1;
						$eventdata->subject           = $creatorsubject;
						$eventdata->fullmessage       = format_text_email($creatormessage, FORMAT_HTML);
						$eventdata->fullmessageformat = FORMAT_PLAIN;
						$eventdata->fullmessagehtml   = $creatormessage;
						$eventdata->smallmessage      = $creatorsubject;

						message_send($eventdata);

					}
				}
			}
		}


    }

}

<?php


defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'block_custom_badge\task\cron_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )
);

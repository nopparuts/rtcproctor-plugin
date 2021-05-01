<?php
// This file is part of the customcert module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Code fragment to define the version of the customcert module
 *
 * @package    mod_rtcproctor
 * @copyright  2020 Nopparut Saelim
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function mod_rtcproctor_get_coursemodule_info($cm) {
    $info = new cached_cm_info();
    $info->content = '<p>This will display below the module.</p>';
    return $info;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $conf
 * @return bool|int
 */
function rtcproctor_add_instance($conf) {
    global $DB;

    $conf_dbi = new stdClass();
    $conf_dbi->name = get_conf_name($conf);
    $conf_dbi->course = intval(get_conf_courseid($conf));
    $conf_dbi->section = intval(get_conf_section($conf));
    $conf_dbi->timecreated = time();
    $conf_dbi->timemodified = time();

    $id = $DB->insert_record("rtcproctor", $conf_dbi);

    return $id;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function rtcproctor_delete_instance($id) {
    global $DB;

    if (! $conf = $DB->get_record("rtcproctor", array("id"=>$id))) {
        return false;
    }

    $result = true;

    $cm = get_coursemodule_from_instance('rtcproctor', $id);

    if (! $DB->delete_records("rtcproctor", array("id"=>$conf->id))) {
        $result = false;
    }

    if (! $DB->delete_records("rtcproctor_users", array("rtcproctorid"=>$conf->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $conf
 * @return bool
 */
function rtcproctor_update_instance($conf) {
    global $DB;
    $updb = new stdClass();
    $updb->id = $conf->instance;
    $updb->name = get_conf_name($conf);
    $updb->timemodified = time();

    $DB->update_record("rtcproctor", $updb);

    $completiontimeexpected = !empty($conf->completionexpected) ? $conf->completionexpected : null;
    \core_completion\api::update_completion_date_event($conf->coursemodule, 'rtcproctor', $conf->id, $completiontimeexpected);

    return true;
}

function rtcproctor_view($conf, $course, $cm, $context)
{

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $conf->id
    );

    $event = \mod_rtcproctor\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('rtcproctor', $conf);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

function rtcproctor_get_conf_courseid($conf) {
    return $conf->course;
}

function rtcproctor_get_conf_userid($conf) {
    global $USER;
    return $USER->id;
}

function rtcproctor_get_conf_section($conf) {
    return $conf->section;
}

function rtcproctor_get_conf_name($conf) {
    return $conf->name;
}
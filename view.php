<?php

// This file is part of Moodle - http://moodle.org/
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
 * rtcproctor module
 *
 * @package mod_rtcproctor
 * @copyright  2020 Nopparut Saelim
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require("../../config.php");
require_once("$CFG->dirroot/mod/rtcproctor/lib.php");
global $USER, $OUTPUT;

define("TEACHER_URL", get_config("rtcproctor", "teacher_url"));
define("STUDENT_URL", get_config("rtcproctor", "student_url"));

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // URL instance id

if ($u) {  // Two ways to specify the module
    $conf = $DB->get_record('rtcproctor', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('rtcproctor', $conf->id, $conf->courseid, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('rtcproctor', $id, 0, false, MUST_EXIST);
    $conf = $DB->get_record('rtcproctor', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/rtcproctor:view', $context);

// Completion and trigger events.
rtcproctor_view($conf, $course, $cm, $context);

$PAGE->set_url('/mod/rtcproctor/view.php', array('id' => $cm->id));

rtcproctor_print_header($conf, $cm, $course);
rtcproctor_print_heading($conf, $cm, $course);
rtcproctor_print_intro($conf, $cm, $course);

$allowedit  = has_capability('mod/rtcproctor:edit', $context);
$allowview  = has_capability('mod/rtcproctor:view', $context);

$roleassignments = $DB->get_records('role_assignments', array('userid' => $USER->id));

$roleid = 100;
foreach ($roleassignments as &$value) {
    if ( $roleid > $value->roleid ) $roleid = $value->roleid;
}

$out = "";
//modifed the following line on 21 Feb 2021 to use only course enrollment role
//if ($roleid == "3" || $allowedit) {
if ($allowedit) {
//    echo("<br><br><br>Teacher<br><br><br>");
    echo("<script src='./copy-cilpboard.js'></script>");
//    $out .= html_writer::link(TEACHER_URL , TEACHER_URL);
    $out .= html_writer::link(TEACHER_URL , "Link to RTCProctor - Monitor Page", array('target' => '_blank'));
    $out .= "<br><br>";
    $out .= '<input class="btn btn-default" type="button" value="Copy to clipboard" onclick="selectElementContents(document.querySelector(\'#student-list > tbody\'))">';
    $out .= "<br><br>";
    $course_context = context_course::instance($course->id);
    $students = get_role_users(5 , $course_context);
    $conf_room = $students;
    $table = new html_table();
    $table->id = "student-list";
    $table->head = array('Student ID','Passcode');
    foreach ($conf_room as &$value) {
        $studentId = explode("@", $value->email)[0];
        $table->data[] = array($studentId, hash('fnv1a32', $studentId . "-in-" . $cm->id));
    }
    $out .= html_writer::table($table);
    echo $out;
} else if ($roleid == "5" || $allowview) {
//    echo("<br><br><br>Student<br><br><br>");
    echo("<script src='./qrcode.min.js'></script>");

    $username = explode("@", $USER->email)[0];
    $conf_st = new StdClass();
    $conf_st->uuid = hash('fnv1a32', $username . "-in-" . $cm->id);

    $url_st = STUDENT_URL . "#" . $conf_st->uuid;
    $out .= html_writer::label("Your passcode is : {$conf_st->uuid}", "");
    $out .= "<br>";
//    $out .= html_writer::link($url_st, $url_st);
    $out .= html_writer::link($url_st, "Link to RTCProctor - Student Page", array('target' => '_blank'));
    $out .= "<br><br>";
    $out .= html_writer::div("", "", array('id'=>'qrcode'));
    $out .= "<br>";
    $out .= html_writer::script('new QRCode(document.getElementById("qrcode"), "' . $url_st . '");');
    echo $out;
}
echo $OUTPUT->footer();

function rtcproctor_print_header($conf, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$conf->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($conf);
    echo $OUTPUT->header();
}

/**
 * Print rtcproctor heading.
 * @param object $conf
 * @param object $cm
 * @param object $course
 * @param bool $notused This variable is no longer used.
 * @return void
 */
function rtcproctor_print_heading($conf, $cm, $course, $notused = false) {
    global $OUTPUT;
    echo $OUTPUT->heading(format_string($conf->name), 2);
}

/**
 * Print rtcproctor introduction.
 * @param object $conf
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function rtcproctor_print_intro($conf, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($conf->displayoptions) ? array() : unserialize($conf->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($conf->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'rtcproctorintro');
            echo format_module_intro('rtcproctor', $conf, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

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
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 *
 * Display stafftraining enrolment requests and courses where this enrolment method is available.
 *
 * @package    block_stafftraining
 * @copyright  2017 Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * File : create.php
 * Staff training creation.
 */

require_once('../../config.php');
require_once('create_form.php');
require_once('lib.php');
require_once($CFG->dirroot.'/course/lib.php');

// Check access.
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_capability('moodle/course:create', $systemcontext);

// Header code.
$PAGE->set_url('/blocks/stafftraining/create.php', array());
$PAGE->set_pagelayout('standard');
$title = get_string('createtraining', 'block_stafftraining');
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Navigation
$PAGE->navbar->add($title);
$subcategories = $DB->get_records('course_categories', array('parent' => INTRAUCP_CATEGORY), 'name');
$categnames = array();
foreach ($subcategories as $subcategory) {
	$categnames[$subcategory->id] = $subcategory->name;
}
$formdata = array('categnames' => $categnames);
$mform = new create_stafftraining_form(null, $formdata);
$myurl = new moodle_url('/my/index.php', array());

// Three possible states.
if ($mform->is_cancelled()) {
    redirect($myurl);
} else if ($submitteddata = $mform->get_data()) {
	$coursedata = block_stafftraining_getdata($submitteddata);
	$newcourse = create_course($coursedata, $submitteddata->description);
	$attendance = block_stafftraining_prepare($newcourse, $submitteddata);
    header("Location: $CFG->wwwroot/mod/attendance/manage.php?id=$attendance->coursemodule");
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}

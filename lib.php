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
 * @copyright  2017 onwards Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : browse.php
 * Lists the course categories where at least one course can be entered using enrol_stafftraining.
 */

defined('MOODLE_INTERNAL') || die;
define('INTRAUCP_CATEGORY', 10);             //UCP
require_once($CFG->dirroot.'/config.php');
require_once($CFG->dirroot.'/group/lib.php');

?>
<style>
th,td {
	padding-right:25px;
	text-align:center;
}
</style>
<?php

/**
 * Lists all browsable file areas
 *
 * @package  block_stafftraining
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @return array
 */
function block_stafftraining_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['image'] = get_string('image', 'inventory');
    return $areas;
}

function block_stafftraining_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");
    require_login();
    if ($filearea !== 'image') {
        return false;
    }
    $itemid = array_shift($args);
    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_stafftraining', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    send_stored_file($file, null, 0, $forcedownload, $options);
}

//~ function block_createtraining_rendermenu() {
	//~ $html = '<ul>';
	//~ $coursekinds = block_createtraining_coursekinds();
	//~ foreach ($coursekinds as $coursekind) {
		//~ $html .= "<li><a href='$coursekind->url'>".$coursekind->label."</a></li>";
	//~ }
	//~ $html .= '</ul>';
	//~ return $html;
//~ }

//~ function block_createtraining_coursekinds() {
	//~ $ordinarycourse = new stdClass();
	//~ $ordinarycourse->label = get_string('ordinarycourse', 'block_createtraining');
	//~ $ordinaryargs = array('category' => 1, 'returnto' => 'topcat');
	//~ $ordinarycourse->url = new moodle_url('/course/edit.php', $ordinaryargs);

	//~ $intraucpcourse = new stdClass();
	//~ $intraucpcourse->label = get_string('intraucpcourse', 'block_createtraining');
	//~ $intraucpargs = array();
	//~ $intraucpcourse->url = new moodle_url('/blocks/createtraining/intraucp.php', $intraucpargs);

	//~ $kinds = array($ordinarycourse, $intraucpcourse);
	//~ return $kinds;
//~ }

function block_stafftraining_getdata($submitteddata) {
	global $DB;
	$sql = "SELECT MAX(id) AS last FROM {course}";
	$existingcourses = $DB->get_record_sql($sql);
	$potentialid = $existingcourses->last + 1;
	$intraucpprefix = 'INTRAUCP';
    $coursedata = new stdClass;
    $coursedata->fullname = $submitteddata->coursetitle;
    $coursedata->summary_editor = $submitteddata->description;
    $coursedata->category = $submitteddata->categid;
    $coursedata->shortname = $coursedata->fullname;
    $coursedata->idnumber = $intraucpprefix.'-'.$potentialid;
    $coursedata->format = 'topics';
    return $coursedata;
}

function block_stafftraining_createmodule($modulename, $courseid, $sectionnum) {
	global $CFG;
    $moduleinfo = new stdClass();
    $moduleinfo->modulename = $modulename;
    $moduleinfo->name = get_string('pluginname', "mod_$modulename");
    $moduleinfo->course = $courseid;
    $moduleinfo->groupmode = 1;
    $moduleinfo->grade = 100;
    $moduleinfo->cmidnumber = '';
    $moduleinfo->section = $sectionnum;
    $moduleinfo->visible = 1;
    $moduleinfo->introeditor = array('text' => '', 'format' => 0, 'itemid' => 0);
    $moduleinfo->page_after_submit_editor = array('text' => '', 'format' => 0, 'itemid' => 0);
    $moduleinfo->visibleoncoursepage = 0;
    $moduleinfo->page_after_submit = '';
    $createdmoduleinfo = create_module($moduleinfo);
    return $createdmoduleinfo;
}

function block_stafftraining_addblock($blockname, $courseid, $region) {
	global $DB;
	$params = array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $courseid);
    $coursecontext = $DB->get_record('context', $params);
    $blockinstance = new stdClass();
    $blockinstance->blockname = $blockname;
    $blockinstance->parentcontextid = $coursecontext->id;
    $blockinstance->showinsubcontexts = 0;
    $blockinstance->pagetypepattern = '*';
    $blockinstance->defaultregion = $region;
    $blockinstance->defaultweight = 0;
    $blockinstance->id = $DB->insert_record('block_instances', $blockinstance);
}

function block_stafftraining_prepare($course, $moredata) {
	global $DB;

    // Attribution du rôle Organisateur, dans cette formation, à l'utilisateur actuel (qui est en train de la créer).
    block_stafftraining_enrolcreator($course);

	$DB->set_field('course', 'groupmode', 1, array('id' => $course->id));
	$DB->set_field('course', 'groupmodeforce', 1, array('id' => $course->id));

	$forummodule = $DB->get_record('modules', array('name' => 'forum'));
    $DB->set_field('course_modules', 'groupmode', 1, array('course' => $course->id, 'module' => $forummodule->id));

    // Enable and configure intratraining enrolment method
    $stafftrainingenrol = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'stafftraining'));
    $stafftrainingenrol->status = 0;
    $stafftrainingenrol->customint1 = $moredata->capacity;
        // Ici, peut-être utiliser customint2 pour lier la méthode à cette instance du bloc "Formations du personnel".
    $DB->update_record('enrol', $stafftrainingenrol);

	// Create training groups.
	$groupnum = 1;
	while ($groupnum <= $moredata->nbgroups) {
		$newgroup = new stdClass();
		$newgroup->courseid = $course->id;
        $newgroup->name = ucwords(get_string('group')).' '.$groupnum;
        $newgroupid = groups_create_group($newgroup);
        $groupnum++;
	}

	// Fill description if the user gave one.
	if (is_object($moredata->description)) {
		$DB->set_field('course', 'summary', $moredata->description->text, array('id' => $course->id));
	    $DB->set_field('course', 'summaryformat', $moredata->description->format, array('id' => $course->id));
	}

    // Add required blocks.
    block_stafftraining_addblock('signinsheet', $course->id, 'left');

    // Follow modules completion.
	$DB->set_field('course', 'enablecompletion', 1, array('id' => $course->id));

    // Create required modules : attendance, feedback, certificate.
    $attendance  = block_stafftraining_createmodule('attendance', $course->id, 0);
    $feedback    = block_stafftraining_createmodule('feedback', $course->id, 0);
    $certificate = block_stafftraining_createmodule('certificate', $course->id, 0);

    $feedbackavailability = '{"op":"&","c":[{"type":"completion","cm":'.$attendance->coursemodule.',"e":1}],"showc":[true]}';
    $DB->set_field('course_modules', 'availability', $feedbackavailability, array('id' => $feedback->coursemodule));
    $DB->set_field('course_modules', 'completion', 2, array('id' => $feedback->coursemodule));
    $DB->set_field('feedback', 'completionsubmit', 1, array('id' => $feedback->instance));

    //~ $attendanceinstance = $DB->get_record('attendance', array('id' => $attendance->instance));
    //~ $feedbackinstance = $DB->get_record('feedback', array('id' => $feedback->instance));

    $certificateavailability = '{"op":"&","c":[{"type":"completion","cm":'.$feedback->coursemodule.',"e":1}],"showc":[true]}';
    $DB->set_field('course_modules', 'availability', $certificateavailability, array('id' => $certificate->coursemodule));
    $certificateinstance = $DB->get_record('certificate', array('id' => $certificate->instance));
    $certificateinstance->certificatetype = 'A4_non_embedded';
    $certificateinstance->orientation = 'L';
    $certificateinstance->borderstyle = 'Fancy1-green.jpg';
    $certificateinstance->datefmt = 5;
    $certificateinstance->gradefmt = 1;
    $certificateinstance->printteacher = 1;
    $certificateinstance->printdate = 2;
    $certificateinstance->printhours = $moredata->nbhours.' '.get_string('hours');
    $certificateinstance->printseal = 'Fancy.png';
    $DB->update_record('certificate', $certificateinstance);

    // Create content section.
    block_stafftraining_contentsection($course, $moredata->description);
    return $attendance;
}

function block_stafftraining_contentsection($course, $description) {
	global $DB;
	$courseformatoptions = course_get_format($course)->get_format_options();
	$courseformatoptions['numsections']++;
    course_create_sections_if_missing($course, $courseformatoptions['numsections']);
    $sections = $DB->get_records('course_sections', array('course' => $course->id));
    foreach ($sections as $section) {
		if ($section->sequence == '') {
			$DB->set_field('course_sections', 'name', get_string('trainingcontent', 'block_stafftraining'),
                array('id' => $section->id));
		}
	}
}

function block_stafftraining_enrolcreator($course) {
	global $DB, $USER;
	$now = time();
	$manualenrol = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $course->id));
    $coursecontext = context_course::instance($course->id);
    $organizerrole = $DB->get_record('role', array('shortname' => 'organizer'));

    $enrolment = new stdClass();
    $enrolment->enrolid = $manualenrol->id;
    $enrolment->userid = $USER->id;
    $enrolment->timestart = $now;
    $enrolment->timecreated = $now;
    $enrolment->timemodified = $now;
    $enrolment->modifierid = $USER->id;
    $DB->insert_record('user_enrolments', $enrolment);

    $roleassignment = new stdClass();
    $roleassignment->roleid = $organizerrole->id;
    $roleassignment->contextid = $coursecontext->id;
    $roleassignment->userid = $USER->id;
    $roleassignment->timemodified = $now;
    $roleassignment->modifierid = $USER->id;
    $DB->insert_record('role_assignments', $roleassignment);
}

function block_stafftraining_organizergroups() {
    global $DB, $USER;
    $organizerrole = $DB->get_record('role', array('shortname' => 'organizer'));
    if (!$organizerrole) {
		return array();
	}
    $organizerassignments = $DB->get_records('role_assignments', array('roleid' => $organizerrole->id, 'userid' => $USER->id));
    $orgagroupids = array();
    foreach ($organizerassignments as $organizerassignment) {
        $orgacontext = $DB->get_record('context', array('id' => $organizerassignment->contextid, 'contextlevel' => CONTEXT_COURSE), '*', MUST_EXIST);
        $orgagroups = $DB->get_records('groups', array('courseid' => $orgacontext->instanceid));
        foreach ($orgagroups as $orgagroup) {
			$orgagroupids[] = $orgagroup->id;
		}
    }
    return $orgagroupids;
}

// TODO
function block_stafftraining_organizerrequests($criterium) {
    global $DB;
    $orgagroupids = block_stafftraining_organizergroups();
	$organizerrequests = array();
	foreach ($orgagroupids as $orgagroupid) {
		$params = array('groupid' => $orgagroupid);
		switch ($criterium) {
			case 'recorded':
				$params['recorded'] = 1;
				break;

			case 'unrecorded':
				$params['recorded'] = 0;
				break;

			case 'waiting':
                $params['recorded'] = 1;
				$params['timeorganiser'] = 0;
				break;
		}
		$grouprequests = $DB->get_records('enrol_stafftraining_enroldata', $params);
		foreach ($grouprequests as $grouprequest) {
			$organizerrequests[] = $grouprequest;
		}
	}

    return $organizerrequests;
}

function block_stafftraining_chiefrequests($criterium) {
    global $DB, $USER;
    $params = array('chiefid' => $USER->id);
    switch ($criterium) {
        case 'recorded':
            $params['recorded'] = 1;
            break;

        case 'unrecorded':
            $params['recorded'] = 0;
            break;

        case 'waiting':
            $params['recorded'] = 1;
            $params['timechief'] = 0;
            break;
    }
    $requests = $DB->get_records('enrol_stafftraining_enroldata', $params);
    return $requests;
}

function block_stafftraining_yourrequests($criterium) {
    global $DB, $USER;
    $params = array('userid' => $USER->id);
    switch ($criterium) {
        case 'recorded':
            $params['recorded'] = 1;
            break;

        case 'unrecorded':
            $params['recorded'] = 0;
            break;

        case 'waiting':
            $params['recorded'] = 1;
			$params['timeorganiser'] = 0;
            break;
    }
    $requests = $DB->get_records('enrol_stafftraining_enroldata', $params);
    return $requests;
}

function block_stafftraining_formline($label, $value) {
	echo "<span style='font-weight:bold'>$label :</span> $value<br>";
}



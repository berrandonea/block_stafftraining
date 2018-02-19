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
 * File : your.php
 * Lists the current user's stafftraining enrolment requests.
 */

require_once('../../config.php');
require_once('lib.php');

$moodlefilename = '/blocks/stafftraining/your.php';
$PAGE->set_url($moodlefilename);
$title = get_string('yourrequests', 'block_stafftraining');
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$browseurl = new moodle_url('/blocks/stafftraining/browse.php');
$PAGE->navbar->add(get_string('pluginname', 'block_stafftraining'), $browseurl);
$PAGE->navbar->add($title);

require_login();

$unrecordedrequests = $DB->get_records('enrol_stafftraining_enroldata', array('userid' => $USER->id, 'recorded' => 0));
$waitingrequests = $DB->get_records('enrol_stafftraining_enroldata', array('userid' => $USER->id, 'recorded' => 1, 'timechief' => 0));
$recordedrequests = $DB->get_records('enrol_stafftraining_enroldata', array('userid' => $USER->id, 'recorded' => 1, 'timechief' => 0));
$handledrequests = array();
foreach ($recordedrequests as $recordedrequest) {
	if (in_array($recordedrequest, $waitingrequests)) {
		continue;
	} else {
		$handledrequests[] = $recordedrequests;
	}
}

echo $OUTPUT->header();
if ($unrecordedrequests) {
    echo html_writer::tag('h3', get_string('pleaserecord', 'block_stafftraining'));
    echo '<table>';
    echo '<tr>';
    echo '<th>'.get_string('course').'</th>';
    echo '<th>'.get_string('group').'</th>';
    echo '<th>'.get_string('confirm').'</th>';
    echo '<th>'.get_string('cancel').'</th>';
    echo '</tr>';
    foreach ($unrecordedrequests as $unrecordedrequest) {
	    $group = $DB->get_record('groups', array('id' => $unrecordedrequest->groupid));
	    $training = $DB->get_record('course', array('id' => $group->courseid));
	    echo '<tr>';
	    echo "<td>$training->fullname</td>";
	    echo "<td>$group->name</td>";
	    echo "<td><a href='$CFG->wwwroot/enrol/stafftraining/checkformdata.php?id=$unrecordedrequest->id'>"
	        .get_string('confirm')."</a></td>";
	    echo "<td><a href='$CFG->wwwroot/enrol/stafftraining/checkformdata.php?id=$unrecordedrequest->id'>"
	        .get_string('cancel')."</a></td>";
	    echo '</tr>';
    }
    echo '</table>';
}

if ($waitingrequests) {
	echo html_writer::tag('h3', get_string('yourwaiting', 'block_stafftraining'));
	echo '<table>';
	echo '<tr>';
    echo '<th>'.get_string('course').'</th>';
    echo '<th>'.get_string('group').'</th>';
    echo '<th>'.get_string('timeasked', 'block_stafftraining').'</th>';
    echo '<th>'.get_string('chiefadvice', 'block_stafftraining').'</th>';
    echo '</tr>';
    foreach ($waitingrequests as $waitingrequest) {
		$group = $DB->get_record('groups', array('id' => $waitingrequest->groupid));
	    $training = $DB->get_record('course', array('id' => $group->courseid));
	    echo '<tr>';
	    echo '<td>'."<a href='checkyours.php?id=$waitingrequest->id'>".$training->fullname."</a>".'</td>';
	    echo "<td>$group->name</td>";
	    $datetime = date('d/m/Y', $waitingrequest->timeasked);
	    echo "<td>$datetime</td>";
	    if ($waitingrequest->timechief) {
			if ($waitingrequest->chiefadvice) {
			    echo '<td style="color:green" title="'.$waitingrequest->chieftext.'">'.get_string('accepted', 'block_stafftraining').'</td>';
		    } else {
			    echo '<td style="color:red" title="'.$waitingrequest->chieftext.'">'.get_string('rejected', 'block_stafftraining').'</td>';
		    }
		} else {
			echo '<td></td>';
		}	    
	    echo '</tr>';
	}
	echo '</table>';
}

if ($handledrequests) {
	echo html_writer::tag('h3', get_string('yourhandled', 'block_stafftraining'));
	echo '<table>';
	echo '<tr>';
    echo '<th>'.get_string('course').'</th>';
    echo '<th>'.get_string('group').'</th>';
    echo '<th>'.get_string('timeasked', 'block_stafftraining').'</th>';
    echo '<th>'.get_string('chiefadvice', 'block_stafftraining').'</th>';
    echo '<th>'.get_string('organizeranswer', 'block_stafftraining').'</th>';
    echo '</tr>';
    foreach ($handledrequests as $handledrequest) {
		$group = $DB->get_record('groups', array('id' => $handledrequest->groupid));
	    $training = $DB->get_record('course', array('id' => $group->courseid));
	    echo '<tr>';
	    echo '<td>'."<a href='checkyours.php?id=$waitingrequest->id'>".$training->fullname."</a>".'</td>';
	    echo "<td>$group->name</td>";
	    $datetime = date('d/m/Y', $handledrequest->timeasked);
	    echo "<td>$datetime</td>";
	    if ($handledrequest->timechief) {
			if ($handledrequest->chiefadvice) {
			    echo '<td style="color:green" title="'.$handledrequest->chieftext.'">'.get_string('accepted', 'block_stafftraining').'</td>';
		    } else {
			    echo '<td style="color:red" title="'.$handledrequest->chieftext.'">'.get_string('rejected', 'block_stafftraining').'</td>';
		    }
		} else {
			echo '<td></td>';
		}
	    echo '</tr>';
	}
	echo '</table>';
}

echo $OUTPUT->footer();

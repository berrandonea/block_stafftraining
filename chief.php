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
 * File : chief.php
 * Lists the stafftraining enrolment requests issued by users whose chief is the current user.
 */

require_once('../../config.php');
require_once('lib.php');

$moodlefilename = '/blocks/stafftraining/chief.php';
$PAGE->set_url($moodlefilename);
$title = get_string('yourstaffrequests', 'block_stafftraining');
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$browseurl = new moodle_url('/blocks/stafftraining/browse.php');
$PAGE->navbar->add(get_string('pluginname', 'block_stafftraining'), $browseurl);
$PAGE->navbar->add($title);

require_login();

//~ $unrecordedrequests = $DB->get_records('enrol_stafftraining_enroldata', array('userid' => $USER->id, 'recorded' => 0));

$string['pleasehandlechief'] = 'Des membres de votre personnel ont émis les demandes de formation ci-dessous. Merci de nous donner votre avis concernant ces requêtes.';
$string['asker'] = 'Demandé par';
$string['timeasked'] = 'Demandé le';
$string['handle'] = 'Répondre';
$string['youradvice'] = 'Votre avis';
$string['oranizeranswer'] = 'Réponse des organisateurs';
$chiefrecordedrequests = block_stafftraining_chiefrequests('recorded');

echo $OUTPUT->header();
echo html_writer::tag('h3', get_string('pleasehandlechief', 'block_stafftraining'));
echo '<table>';
echo '<tr>';
echo '<th>'.get_string('asker', 'block_stafftraining').'</th>';
echo '<th>'.get_string('course').'</th>';
echo '<th>'.get_string('timeasked', 'block_stafftraining').'</th>';
echo '<th>'.get_string('youradvice', 'block_stafftraining').'</th>';
echo '<th>'.get_string('organizeranswer', 'block_stafftraining').'</th>';
echo '</tr>';

foreach ($chiefrecordedrequests as $chiefrecordedrequest) {
	$asker = $DB->get_record('user', array('id' => $chiefrecordedrequest->userid));
	$group = $DB->get_record('groups', array('id' => $chiefrecordedrequest->groupid));	
	$course = $DB->get_record('course', array('id' => $group->courseid));
	$datetime = date('d/m/Y', $chiefrecordedrequest->timeasked); 
    echo '<tr>';
    echo '<td>'."$asker->firstname $asker->lastname".'</td>';
    echo '<td>'.$course->fullname.'</td>';
    echo '<td>'.$datetime.'</td>';
    if ($chiefrecordedrequest->timechief) {
		if ($chiefrecordedrequest->chiefadvice) {
			echo '<td style="color:green">'.get_string('accepted', 'block_stafftraining').'</td>';
		} else {
			echo '<td style="color:red">'.get_string('rejected', 'block_stafftraining').'</td>';
		}
	} else {
		echo '<td>'."<a href='chiefhandle.php?id=$chiefrecordedrequest->id'>".get_string('handle', 'block_stafftraining')."</a>".'</td>';
	}
    echo '</tr>';
}


//~ foreach ($unrecordedrequests as $unrecordedrequest) {
	//~ $group = $DB->get_record('groups', array('id' => $unrecordedrequest->groupid));
	//~ $training = $DB->get_record('course', array('id' => $group->courseid));
	//~ echo '<tr>';
	//~ echo "<td>$training->fullname</td>";
	//~ echo "<td>$group->name</td>";
	//~ echo "<td><a href='$CFG->wwwroot/enrol/stafftraining/checkformdata.php?id=$unrecordedrequest->id'>"
	    //~ .get_string('confirm')."</a></td>";
	//~ echo "<td><a href='$CFG->wwwroot/enrol/stafftraining/checkformdata.php?id=$unrecordedrequest->id'>"
	    //~ .get_string('cancel')."</a></td>";
	//~ echo '</tr>';
//~ }
echo '</table>';
echo $OUTPUT->footer();

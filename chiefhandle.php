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
 * File : chiefhandle.php
 * Shows an enrolment requests issued by someone whose chief is the current user.
 */

require_once('../../config.php');
require_once('lib.php');

$requestid = required_param('id', PARAM_INT);
$request = $DB->get_record('enrol_stafftraining_enroldata', array('id' => $requestid, 'recorded' => 1, 'chiefid' => $USER->id), '*', MUST_EXIST);
$asker = $DB->get_record('user', array('id' => $request->userid));

$moodlefilename = '/blocks/stafftraining/chiefhandle.php';
$PAGE->set_url($moodlefilename, array('id' => $requestid));
$title = "$asker->firstname $asker->lastname";
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$browseurl = new moodle_url('/blocks/stafftraining/browse.php');
$chiefurl = new moodle_url('/blocks/stafftraining/chief.php');
$PAGE->navbar->add(get_string('pluginname', 'block_stafftraining'), $browseurl);
$PAGE->navbar->add(get_string('yourstaffrequests', 'block_stafftraining'), $chiefurl);
$PAGE->navbar->add($title);

require_login();

echo $OUTPUT->header();

$askerdata = $DB->get_record('enrol_stafftraining_userdata', array('userid' => $asker->id));
echo html_writer::tag('h3', $title);

block_stafftraining_formline(get_string('phone'), $asker->phone1);
block_stafftraining_formline(get_string('birthday', 'enrol_stafftraining'), date('d/m/Y', $askerdata->birthday));
$statusoptions = array(get_string('permanent', 'enrol_stafftraining'), get_string('contractual', 'enrol_stafftraining'));
block_stafftraining_formline(get_string('status'), $statusoptions[$askerdata->status]);
$corpsoptions = array('AENES', 'ITRF', get_string('defaultcourseteacher'), 'BU');
block_stafftraining_formline(get_string('corps', 'enrol_stafftraining'), $corpsoptions[$askerdata->corps]);
block_stafftraining_formline(get_string('rank', 'enrol_stafftraining'), $askerdata->rank);

$affectation = $DB->get_record('enrol_stafftraining_affectation', array('id' => $askerdata->affectation));
block_stafftraining_formline(get_string('arrivaldate', 'enrol_stafftraining'), date('d/m/Y', $askerdata->arrival));
echo '<br>';

$group = $DB->get_record('groups', array('id' => $request->groupid));
$course = $DB->get_record('course', array('id' => $group->courseid));
block_stafftraining_formline(get_string('wantedtraining', 'enrol_stafftraining'), $course->fullname);
block_stafftraining_formline(get_string('wantedgroup', 'enrol_stafftraining'), $group->name);

$grouphtml = '';
$groupsessions = $DB->get_records('attendance_sessions', array('groupid' => $group->id));
if ($groupsessions) {					
    $grouphtml .= ucwords(get_string('sessions', 'attendance'));
	$grouphtml .= '<ul>';
	$days = array(0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat');
	foreach ($groupsessions as $groupsession) {
	    if ($groupsession->sessdate < $now) {
		    $begun = 1;						    
		} else {
		    $timestart = $groupsession->sessdate;
            $timestop  = $groupsession->sessdate + $groupsession->duration;
            $daynum = date('w', $timestart);
            $dayidentifier = 'day'.$days[$daynum];
            $grouphtml .= "<li>".get_string('on', 'enrol_stafftraining').' '.get_string($dayidentifier, 'enrol_stafftraining')
						                  .' '.date('d/m/Y', $timestart).', '
						                  .get_string('from', 'enrol_stafftraining').' '.date('H:i', $timestart).' '
						                  .get_string('to', 'enrol_stafftraining').' '.date('H:i', $timestop).".</li>";
        }
    }
    $grouphtml .= '</ul>';
} else {
    $grouphtml .= get_string('nodateyet', 'enrol_stafftraining');
    $grouphtml .= '<br><br>';
}
echo $grouphtml;

if ($request->planned) {
	$plannedstring = get_string('yes');
} else {
	$plannedstring = get_string('no');
}
block_stafftraining_formline(get_string('planned', 'enrol_stafftraining'), $plannedstring);
echo '<br>';
block_stafftraining_formline(get_string('interest', 'enrol_stafftraining'), format_text($request->interest));
echo '<br>';
block_stafftraining_formline(get_string('schedule', 'enrol_stafftraining'), format_text($request->schedule));
echo '<br>';
block_stafftraining_formline(get_string('accessibility', 'enrol_stafftraining'), format_text($request->accessibility));
echo '<br>';
block_stafftraining_formline(get_string('infos', 'enrol_stafftraining'), format_text($request->infos));



  //~ echo format_text($summarytext, $section->summaryformat, $summaryformatoptions);


//~ Intérêt professionnel pour la formation
//~ Avez-vous des contraintes d'emploi du temps ?

//~ Même si vous avez choisi, ci-dessus, un groupe pour lequel les dates de formation sont déjà fixées, merci de remplir ceci au cas où nous devions vous proposer d'autres dates.


//~ Merci d'indiquer vos éventuels besoins d'adaptation pour la formation (accessibilité, mobilité)
//~ Informations complémentaires


echo $OUTPUT->footer();


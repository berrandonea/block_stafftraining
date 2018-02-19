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

require_once('../../config.php');
$moodlefilename = '/blocks/stafftraining/browse.php';
$PAGE->set_url($moodlefilename);
$systemcontext = context_system::instance();
$title = get_string('pluginname', 'block_stafftraining');
$PAGE->set_context($systemcontext);
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->navbar->add($title);

require_login();

$sql = "SELECT DISTINCT category FROM {course} WHERE id IN (SELECT DISTINCT courseid FROM {enrol} WHERE enrol = 'stafftraining')";
if (! has_capability('moodle/course:viewhiddencourses', $systemcontext)) {
	$sql .= " AND visible = 1";
}

$fs = get_file_storage();
$trainings = $DB->get_records_sql($sql);

echo $OUTPUT->header();
$categorypage = "$CFG->wwwroot/course/index.php";
foreach ($trainings as $training) {
	$category = $DB->get_record('course_categories', array('id' => $training->category));
	if ($category) {

		$imageurl = '';

                $listcoursesincategory = $DB->get_records('course', array('category' => $category->id));

                foreach ($listcoursesincategory as $courseincategory) {

                    $coursecontextid = $DB->get_record('context',
                            array('instanceid' => $courseincategory->id, 'contextlevel' => CONTEXT_COURSE))->id;

                    $sql = "SELECT * FROM {files} WHERE contextid = ? AND component LIKE 'course' "
                            . "AND filearea LIKE 'overviewfiles' AND filename NOT LIKE '.'";

                    $imagefile = $DB->get_record_sql($sql, array($coursecontextid));

                    if ($imagefile) {

                        break;
                    }
                }

                if ($imagefile) {

                    $fileinfo = array('component' => 'course',
                        'filearea' => 'overviewfiles',
                        'itemid' => $imagefile->itemid,
                        'contextid' => $coursecontextid,
                        'filepath' => $imagefile->filepath,
                        'filename' => $imagefile->filename);

                    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                                        $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

                    if ($file) {

                        if ($imagefile->itemid != 0) {

                            $imageurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                                $file->get_filepath(), $file->get_filename());
                        } else {

                            $imageurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                $file->get_component(), $file->get_filearea(), null,
                                $file->get_filepath(), $file->get_filename());
                        }
                    }
                } else {

                    $imageurl = 'pix/college-graduation.png';
                }

		$countsql = "SELECT COUNT(DISTINCT c.shortname) FROM {course} c, {enrol} e WHERE c.id = e.courseid AND c.category = $category->id AND e.enrol = 'stafftraining'";
		$nbtrainings = $DB->count_records_sql($countsql);

		echo '<div style="float:left;padding-right:40px">';
		echo '<table>';
		echo '<tr>';
		echo '<td>';

		echo "<img src=$imageurl style='width:100px;height:100px'>";
		echo '</td>';
		//~ echo '<td>';
		//~ if (has_capability('block/stafftraining:editcategorypictures', $systemcontext)) {
			//~ echo editicon($category->id);
		//~ }
		//~ echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td style="text-align:center">';
		echo "<a href='$categorypage?categoryid=$category->id' style='font-weight:bold'>$category->name</a>";
		echo '</td>';
		echo '<td>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td style="text-align:center">';
		echo "($nbtrainings ".get_string('trainings', 'block_stafftraining').")";
		echo '</td>';
		echo '<td>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '</div>';
	}
}
echo $OUTPUT->footer();

function editicon($categoryid) {
	echo '<table>';
	echo '<tr>';
	echo '<td>';
	echo "<a href='editcategory.php?edit=$categoryid'>";
	echo "<img src='../../pix/i/edit.png' alt='".get_string('edit')." style='width:20px;height:20px' />";
	echo "</a>";
	echo '</td>';
	echo '</tr>';
	echo '</table>';
}

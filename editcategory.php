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
require_once('editcategory_form.php');

$moodlefilename = '/blocks/stafftraining/editcategory.php';
$categoryid = required_param('edit', PARAM_INT);
$category = $DB->get_record('course_categories', array('id' => $categoryid));
$categoryurl = new moodle_url('/course/index.php', array('categoryid' => $categoryid));

require_login();
$systemcontext = context_system::instance();
require_capability('block/stafftraining:editcategorypictures', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url($moodlefilename, array('edit' => $categoryid));
$title = get_string('editcategorypicture', 'block_stafftraining');
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$browseurl = new moodle_url('/blocks/stafftraining/browse.php', array());
$PAGE->navbar->add(get_string('pluginname', 'block_stafftraining'), $browseurl);
$PAGE->navbar->add($category->name." ($title)");

$categorypicture = $DB->get_record('block_stafftraining_category', array('categoryid' => $category->id));

$draftitemid = file_get_submitted_draft_itemid('image');
file_prepare_draft_area($draftitemid, $systemcontext->id, 'block_stafftraining',
        $categoryid, array('maxbytes' => 0, 'maxfiles' => 1));
$formdata['image'] = $draftitemid;


$formdata['edit'] = $categoryid;
$formdata['categoryname'] = $category->name;
$mform = new block_stafftraining_editcategory_form();
$mform->set_data($formdata);

// Three possible states.
if ($mform->is_cancelled()) {
    redirect($browseurl);
} else if ($submitteddata = $mform->get_data()) {

    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    $listimages = $fs->get_area_files($usercontext->id, 'user', 'draft', $submitteddata->image, 'id');
    foreach ($listimages as $image) {
		echo $image->get_filename();
        if ($image->get_filename() != ".") {
            $imagename = $image->get_filename();
        }
    }

	if ($categorypicture) {

		$oldimagename = $categorypicture->imagename;

                // Prepare file record object.
                $fileinfo = array(
                    'component' => 'block_stafftraining',
                    'filearea' => 'image',     // Usually = table name.
                    'itemid' => $categoryid,   // Usually = ID of row in table.
                    'contextid' => $systemcontext->id, // ID of context.
                    'filepath' => '/',           // Any path beginning and ending in /.
                    'filename' => $oldimagename); // Any filename.

                // Get file.
                $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                        $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

                // Delete it if it exists.
                if ($file) {
                    $file->delete();
                }


		$categorypicture->imagename = $imagename;
		$DB->update_record('block_stafftraining_category', $categorypicture);
	} else {
		$categorypicture = new stdClass();
		$categorypicture->categoryid = $categoryid;
		$categorypicture->imagename = $imagename;
		$categorypicture->id = $DB->insert_record('block_stafftraining_category', $categorypicture);
	}

	$savedimage = file_save_draft_area_files($submitteddata->image,
	                           $systemcontext->id,
	                           'block_stafftraining',
	                           'image',
	                           $categoryid,
	                           array('maxbytes' => 0, 'maxfile' => 1));

    redirect($browseurl);
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}

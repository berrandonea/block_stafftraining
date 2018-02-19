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
 * File : create_form.php
 * Form for staff trainings creation.
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once("{$CFG->libdir}/formslib.php");

class create_stafftraining_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;
        $mform->addElement('header', 'generalheader', get_string('general'));

        $categnames = $this->_customdata['categnames'];
		$mform->addElement('select', 'categid', get_string('category'), $categnames);
		$mform->setType('categid', PARAM_INT);

        $mform->addElement('text', 'coursetitle', get_string('trainingtitle', 'block_stafftraining'), array('size'=>'60'));
        $mform->setType('coursetitle', PARAM_TEXT);
        $mform->addRule('coursetitle', get_string('required'), 'required');
        
        $mform->addElement('editor', 'description', get_string('description'));
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('text', 'nbgroups', get_string('nbgroups', 'block_stafftraining'), array('size'=>'4'));
        $mform->setType('nbgroups', PARAM_INT);
        $mform->setDefault('nbgroups', 0);
        $mform->addRule('nbgroups', get_string('required'), 'required');

        $mform->addElement('text', 'nbhours', get_string('nbhours', 'block_stafftraining'), array('size'=>'4'));
        $mform->setType('nbhours', PARAM_INT);
        $mform->setDefault('nbhours', 0);
        $mform->addRule('nbhours', get_string('required'), 'required');

        $mform->addElement('text', 'capacity', get_string('capacity', 'block_stafftraining'), array('size'=>'4'));
        $mform->setType('capacity', PARAM_INT);
        $mform->setDefault('capacity', 0);
        $mform->addRule('capacity', get_string('required'), 'required');

        $this->add_action_buttons();
    }
}

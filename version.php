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
 * File : version.php
 * Version number.
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2018012405;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2013110500;        // Requires this Moodle version
$plugin->component = 'block_stafftraining'; // Full name of the plugin (used for diagnostics)

$plugin->dependencies = array('enrol_stafftraining' => 2017110700);

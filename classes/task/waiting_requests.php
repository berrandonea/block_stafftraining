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
 * File : classes/task/waiting_requests.php
 * Cron task.
 */

namespace block_stafftraining\task;

//Envoyer un mail indiquant les demandes d'inscription en attente
class waiting_requests extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('pluginname', 'block_stafftraining');
    }

    public function execute() {
		global $DB;
		// Préparation du mail
		$headers = 'From: noreply@cours.u-cergy.fr'."\r\n".'MIME-Version: 1.0'."\r\n".
			 'Reply-To: noreply@cours.u-cergy.fr'. "\r\n".'Content-type: text/html; charset=utf-8'."\r\n".
			 'X-Mailer: PHP/'.phpversion();

		// A qui envoyer le mail ?

		// Envoi du mail
        $subject = "";			
        $message = "
                <html>
                <head>
                    <title></title>
                </head>
                <body>
				</body>";
        $to = $teacher->email;
        mail($to, $subject, $message, $headers);
    }
}

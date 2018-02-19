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
 * File : block_stafftraining.php
 * Block content.
 */
 
require_once("$CFG->dirroot/blocks/stafftraining/lib.php");
require_once("$CFG->dirroot/enrol/stafftraining/lib.php");

class block_stafftraining extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_stafftraining');
    }
 
    //~ function applicable_formats() {
        //~ return array('site' => true);
    //~ }

    function get_content() {
        global $CFG;
        
        if ($this->content !== NULL) {
            return $this->content;
        }
        $this->content = new stdClass;
        if (empty($this->instance)) {
            return $this->content;
        }
        $blockdirurl = $CFG->wwwroot.'/blocks/stafftraining';
        $this->content->text = "<a href='$blockdirurl/browse.php' style='margin:5px;float:left'><input class='btn btn-primary' value='"
            .get_string('browsetrainings', 'block_stafftraining')."'></a>";
        $systemcontext = context_system::instance();
        $coursecreator = has_capability('moodle/course:create', $systemcontext);
        if ($coursecreator) {
			$this->content->text .= '&nbsp;&nbsp;';
			$this->content->text .= "<a href='$blockdirurl/create.php' style='margin:5px;float:left'><input class='btn btn-primary' value='"
            .get_string('createnew', 'block_stafftraining')."'></a>";
		}

        $this->content->text .= '<br><br>';

        $pixwidth = '20px';
        $padding = '30px';

        $yourunrecordedrequests = block_stafftraining_yourrequests('unrecorded');
        $yourwaitingrequests = block_stafftraining_yourrequests('waiting');
        $yourrecordedrequests = block_stafftraining_yourrequests('recorded');
        //~ if ($yourunrecordedrequests || $yourrecordedrequests) {
			$this->content->text .= "<div style='margin-left:10px;float:left;font-weight:bold;padding-right:$padding'>";
			$this->content->text .= "<a href='$blockdirurl/your.php'>";
			$this->content->text .= get_string('yourrequests', 'block_stafftraining');
			$this->content->text .= "</a>";
			$this->content->text .= "</div>";
			$this->content->text .= "<div style='margin-left:10px;float:left;padding-right:$padding'>";
			$this->content->text .= "<img src='$blockdirurl/pix/waiting.png' width='$pixwidth'>";
			$this->content->text .= get_string('unrecorded', 'block_stafftraining').' : ';
			$this->content->text .= '<span style="font-weight:bold">'.count($yourunrecordedrequests).'</span>';
			$this->content->text .= "</div>";
			$this->content->text .= "<div style='margin-left:10px;float:left;padding-right:$padding'>";
			$this->content->text .= "<img src='$blockdirurl/pix/waiting.png' width='$pixwidth'>";
			$this->content->text .= get_string('waiting', 'block_stafftraining').' : ';
			$this->content->text .= '<span style="font-weight:bold">'.count($yourwaitingrequests).'</span>';
			$this->content->text .= "</div>";
            $this->content->text .= "<div style='margin-left:10px;float:left;padding-right:$padding'>";
			$this->content->text .= "<img src='$blockdirurl/pix/handled.png' width='$pixwidth'>";
            $this->content->text .= get_string('handled', 'block_stafftraining').' : ';
            $this->content->text .= '<span style="font-weight:bold">'.(count($yourrecordedrequests) - count($yourwaitingrequests)).'</span>';
            $this->content->text .= "</div>";
            $this->content->text .= "<br><br>";
		//~ }

		$chiefwaitingrequests = block_stafftraining_chiefrequests('waiting');
		$chiefrecordedrequests = block_stafftraining_chiefrequests('recorded');
		if ($chiefrecordedrequests) {
			$this->content->text .= "<div style='float:left;font-weight:bold;padding-right:$padding'>";
			$this->content->text .= "<a href='$blockdirurl/chief.php'>";
			$this->content->text .= get_string('yourstaffrequests', 'block_stafftraining');
			$this->content->text .= "</a>";
			$this->content->text .= "</div>";
			$this->content->text .= "<div style='float:left;padding-right:$padding'>";
			$this->content->text .= "<img src='$blockdirurl/pix/waiting.png' width='$pixwidth'>";
			$this->content->text .= get_string('waiting', 'block_stafftraining').' : ';			
			$this->content->text .= '<span style="font-weight:bold">'.count($chiefwaitingrequests).'</span>';
			$this->content->text .= "</div>";
            $this->content->text .= "<div style='float:left;padding-right:$padding'>";
			$this->content->text .= "<img src='$blockdirurl/pix/handled.png' width='$pixwidth'>";
            $this->content->text .= get_string('handled', 'block_stafftraining').' : ';
            $this->content->text .= '<span style="font-weight:bold">'.(count($chiefrecordedrequests) - count($chiefwaitingrequests)).'</span>';
            $this->content->text .= "</div>";
            $this->content->text .= "<br><br>";
		}

		$organizerwaitingrequests = block_stafftraining_organizerrequests('waiting');
		$organizerrecordedrequests = block_stafftraining_organizerrequests('recorded');
		if ($organizerwaitingrequests || $organizerhandledrequests) {
			$this->content->text .= "<div style='float:left;font-weight:bold;padding-right:$padding'>";
			$this->content->text .= "<a href='$blockdirurl/organiser.php'>";
			$this->content->text .= get_string('othersrequests', 'block_stafftraining');
			$this->content->text .= "</a>";
			$this->content->text .= "</div>";
			$this->content->text .= "<div style='float:left;padding-right:$padding'>";
			$this->content->text .= "<img src='$blockdirurl/pix/waiting.png' width='$pixwidth'>";
			$this->content->text .= get_string('waiting', 'block_stafftraining').' : ';			
			$this->content->text .= '<span style="font-weight:bold">'.count($organizerwaitingrequests).'</span>';
			$this->content->text .= "</div>";
            $this->content->text .= "<div style='float:left;padding-right:$padding'>";
			$this->content->text .= "<img src='$blockdirurl/pix/handled.png' width='$pixwidth'>";
            $this->content->text .= get_string('handled', 'block_stafftraining').' : ';
            $this->content->text .= '<span style="font-weight:bold">'.(count($organizerrecordedrequests) - count($organizerwaitingrequests)).'</span>';
            $this->content->text .= "</div>";
		}

        return $this->content;
    }
    


    //~ function countrequests($plugininstance) {
		//~ global $CFG;
		
		//~ $html .= "<p style='font-weight:bold'>Ajouter une demande +</p>";
		//~ return $html;
	//~ }

    //~ $yourrequests = $plugininstance->yourrequests();
						//~ if ($yourequests) {
							//~ $this->content->text .= $this->shownbrequests('your', $plugininstance, $yourrequests);
						//~ }
						//~ $othersrequests = $plugininstance->othersrequests();
						//~ if ($yourequests) {
							//~ $this->content->text .= $this->shownbrequests('other', $plugininstance, $othersrequests);
						//~ }

    //~ function get_plural($nb) {
        //~ if ($nb > 1) {
            //~ $plural = "s";            
        //~ } else {
            //~ $plural = "";
        //~ }
        //~ return $plural;
    //~ }

}


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
 * Newblock block caps.
 *
 * @package    block_showgrade
 * @copyright  Ruben Cancho <canchete@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/gradelib.php');

class block_showgrade extends block_base {

    function init() {
        $this->title = null;
        $this->grade = null;
        $this->category = null;
    }

    public function specialization() {
        if (isset($this->config)) {
            // TODO apply null pattern
            if (isset($this->config->category)) {
                if (empty($this->config->title)) {
                    $this->title = $this->get_category()->fullname;
                    if ($this->title == "?") {
                        $this->title = get_string('coursetotal', 'block_showgrade');
                    }
                } else {
                    $this->title = $this->config->title;
                }
            }
        }
        else {
            $this->title = get_string('defaulttitle', 'block_showgrade');
        }
    }


    function get_category() {
        if ($this->category == null && $this->config->category !== null) {
            $this->category = grade_category::fetch(array('id'=> $this->config->category));
        }
        return $this->category;
    }

    function get_grade() {
        global $DB, $USER;
        if ($this->grade == null && $this->config->category !== null) {
            $this->grade = $DB->get_record('grade_grades',
                    array('itemid'=> $this->get_category()->get_grade_item()->id,
                          'userid'=> $USER->id));
        }

        return $this->grade;
    }

    function get_formatted_grade() {
        if ($this->get_grade() == null) {
            return "-";
        }
        if (is_numeric($this->get_grade())) {
            return number_format($this->get_grade(), 0) . ' points';
        }
        else {
            // This should never happen!
            return "*";
        }
    }

    function get_content() {
        global $CFG, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $this->content->text = '<h2>' . $this->get_formatted_grade() . '</h2>';

        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => false,
                     'site-index' => false,
                     'course-view' => true,
                     'course-view-social' => false,
                     'mod' => true,
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
        return true;
    }

    function has_config() {return true;}

    public function cron() {
        mtrace( "Hey, my cron script is running" );
        // do something
        return true;
    }
}

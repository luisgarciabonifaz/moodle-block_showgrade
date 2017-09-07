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
        $this->title = '';
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
            $category = $this->get_category();
            if ($category) { 
                $this->grade = $DB->get_record('grade_grades',
                        array('itemid'=> $this->get_category()->get_grade_item()->id,
                              'userid'=> $USER->id));
            } else {
                $this->grade = null;
            }
        }

        return $this->grade;
    }

    function get_finalgrade() {
        return $this->get_grade()->finalgrade;
    }

    function get_level() {
        return number_format($this->get_finalgrade() / $this->config->pointslevel, 0);
    }

    function get_formatted_level() {
        return get_string('level', 'block_showgrade') . ' ' . $this->get_level();
    }

    function get_formatted_grade() {
        if ($this->get_grade() == null) {
            return "-";
        }

        return number_format($this->get_finalgrade(), 0) . ' points';
    }

    function get_points_nextlevel() {
        return number_format($this->config->pointslevel * ($this->get_level() + 1) - $this->get_finalgrade(), 0);
    }

    function get_formatted_nextlevel() {
        return $this->get_points_nextlevel() . ' ' . get_string('pointslevelup', 'block_showgrade');
    }


    function percent($number){
        return number_format($number, 2) * 100 . '%';
    }

    function get_completed_percent() {
        return $this->percent($this->get_finalgrade() / $this->get_maxpoints()) . ' ' . get_string('completed', 'block_showgrade');
    }

    function get_maxpoints() {
        return $this->get_grade()->rawgrademax;
    }

    function get_formatted_maxpoints() {
        return get_string('of','block_showgrade')
                . ' ' . number_format($this->get_maxpoints(),0)
                . ' ' . get_string('possiblepoints', 'block_showgrade');
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
        $this->content->text = '';

        if ($this->config == null) {
            return $this->content;
        }

        if (property_exists($this->config, 'enablelevels')) {
            if ($this->config->enablelevels == true) {
                $this->content->text .= '<h2>' . $this->get_formatted_level() . '</h2>';
                $this->content->text .= '<p>' . $this->get_formatted_nextlevel() .'</p>';
            }
        }

        $this->content->text .= '<h4>' . $this->get_formatted_grade() . '</h4>';

        if (property_exists($this->config, 'enablemaxpoints')) {
            if ($this->config->enablemaxpoints == true) {
                $this->content->text.= '<p>' . $this->get_formatted_maxpoints() . '</p>';
            }
        }

        if (property_exists($this->config, 'enablecompletion')) {
            if ($this->config->enablecompletion == true) {
                $this->content->text.= '<p>' . $this->get_completed_percent() . '</p>';
            }
        }

        return $this->content;
    }

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

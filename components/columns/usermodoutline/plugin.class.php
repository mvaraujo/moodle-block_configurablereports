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
 * Configurable Reports
 * A Moodle block for creating customizable reports
 *
 * @package blocks
 * @author  : Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date    : 2009
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/blocks/configurable_reports/plugin.class.php');

class plugin_usermodoutline extends plugin_base {

    public function init() {
        $this->fullname = get_string('usermodoutline', 'block_configurable_reports');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = ['users'];
    }

    public function summary($data) {
        global $DB;
        // Should be a better way to do this.
        if ($cm = $DB->get_record('course_modules', ['id' => $data->cmid])) {
            $modname = $DB->get_field('modules', 'name', ['id' => $cm->module]);
            if ($name = $DB->get_field("$modname", 'name', ['id' => $cm->instance])) {
                return $data->columname . ' (' . $name . ')';
            }
        }

        return $data->columname;
    }

    public function colformat($data) {
        $align = (isset($data->align)) ? $data->align : '';
        $size = (isset($data->size)) ? $data->size : '';
        $wrap = (isset($data->wrap)) ? $data->wrap : '';

        return [$align, $size, $wrap];
    }

    // Data -> Plugin configuration data.
    // Row -> Complet user row c->id, c->fullname, etc...
    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB, $CFG;
        if ($cm = $DB->get_record('course_modules', ['id' => $data->cmid])) {
            $mod = $DB->get_record('modules', ['id' => $cm->module]);
            if ($instance = $DB->get_record("$mod->name", ['id' => $cm->instance])) {
                $libfile = "$CFG->dirroot/mod/$mod->name/lib.php";
                if (file_exists($libfile)) {
                    require_once($libfile);
                    $useroutline = $mod->name . "_user_outline";
                    if (function_exists($useroutline)) {
                        if ($course = $DB->get_record('course', ['id' => $this->report->courseid])) {
                            $result = $useroutline($course, $row, $mod, $instance);
                            if ($result) {
                                $returndata = '';
                                if (isset($result->info)) {
                                    $returndata .= $result->info . ' ';
                                }

                                if ((!isset($data->donotshowtime) || !$data->donotshowtime) && !empty($result->time)) {
                                    $returndata .= userdate($result->time);
                                }

                                return $returndata;
                            }
                        }
                    }
                }
            }
        }

        return '';
    }

}

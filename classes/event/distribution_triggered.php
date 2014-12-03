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
 * The mod_ratingallocate distribution_triggered event.
 *
 * @package    mod_ratingallocate
 * @copyright  2014 Tobias Reischmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_ratingallocate\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The mod_ratingallocate distribution_triggered event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - array allocations: created allocations
 *      - double time_needed: time needed to finish the distribution
 * }
 *
 * @since     Moodle 2.7
 * @copyright 2014 Tobias Reischmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class distribution_triggered extends \core\event\base {
    
    public static function create_simple($context, $objectid, $allocations, $time_needed){
        // the values of other need to be encoded since the base checks for equality of a decoded encoded other instance with the original.
        // this is not given for doubles or nested arrays
        $allocations_json_valid = json_decode(json_encode($allocations),true);
        $time_needed_json_valid = json_decode(json_encode($time_needed),true);
        return self::create(array('context' => $context, 'objectid' => $objectid, 
                        'other' => array('allocations'=> $allocations_json_valid,'time_needed'=>$time_needed_json_valid)));        
    }
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'ratingallocate_ratings';
    }
 
    public static function get_name() {
        return get_string('distribution_triggered', 'mod_ratingallocate');
    }
 
    public function get_description() {
        return get_string('distribution_triggered_description', 'mod_ratingallocate', 
                array('userid' => $this->userid, 
                      'ratingallocateid' => $this->objectid, 
                      'time_needed' => $this->other['time_needed']));
    }
 
    public function get_url() {
        return new \moodle_url('/mod/ratingallocate/view.php', array('ratingallocate' => $this->objectid));
    }
}
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
 * Prints a particular instance of ratingallocate
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_ratingallocate
 * @copyright  2014 T Reischmann, C Usener
 * @copyright  based on code by M Schulze copyright (C) 2014 M Schulze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/course/moodleform_mod.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Provides a form for manual allocations
 */
class manual_alloc_form extends moodleform {

    /** @var $ratingallocate ratingallocate */
    private $ratingallocate;

    const FORM_ACTION = 'action';
    const ASSIGN = 'assign';

    /**
     * Constructor
     * @param mixed $url
     * @param ratingallocate $ratingallocate
     */
    public function __construct($url, ratingallocate $ratingallocate) {
        $this->ratingallocate = $ratingallocate;
        parent::__construct($url);
        $this->definition_after_data();
    }

    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE;

        $mform = $this->_form;

        $mform->addElement('hidden', self::FORM_ACTION, ACTION_MANUAL_ALLOCATION);
        $mform->setType(self::FORM_ACTION, PARAM_TEXT);


        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'data', 0);
        $mform->setType('data', PARAM_INT);
    }
    
    public function definition_after_data(){
        parent::definition_after_data();
        $mform = & $this->_form;

        $ratingdata = $this->ratingallocate->get_ratings_for_rateable_choices();
        $different_ratings = array();
        $empty_preferences = array();
        foreach ($this->ratingallocate->get_rateable_choices() as $choiceid => $choice){
            $empty_preferences[$choiceid] = get_string('no_rating_given' , ratingallocate_MOD_NAME);
        }
        $userdata = array();
        // Create one entry for each user choice combination
            foreach ($this->ratingallocate->get_raters_in_course() as $userid => $users) {
                $userdata[$userid] = $empty_preferences;
            }

        // Add actual rating data to userdata
        foreach ($ratingdata as $rating) {
            if (!array_key_exists($rating->userid, $userdata)) {
                $userdata[$rating->userid] = $empty_preferences;
            }
            if ($rating->rating) {
                $userdata[$rating->userid][$rating->choiceid] = $rating->rating;
                $different_ratings[$rating->rating] = $rating->rating;
            }
        }
        // Create and set up the flextable for ratings and allocations.
        $table = new mod_ratingallocate\ratings_and_allocations_table($this->ratingallocate->get_renderer(),
            $this->ratingallocate->get_options_titles($different_ratings), $this->ratingallocate,
            'manual_allocation');
        $table->setup_choices($this->ratingallocate->get_rateable_choices());

        // The rest must be done through output buffering due to the way flextable works.
        ob_start();
        $table->build_table($ratingdata, $this->ratingallocate->get_allocations(), true);
        $tableoutput = ob_get_contents();
        ob_end_clean();
        $mform->addElement('html', $tableoutput);

        $this->add_special_action_buttons();
    }

    /**
     * Overriding formslib's add_action_buttons() method, to add an extra submit "save changes and continue" button.
     *
     * @param bool $cancel show cancel button
     * @param string $submitlabel null means default, false means none, string is label text
     * @param string $submit2label  null means default, false means none, string is label text
     * @return void
     */
    public function add_special_action_buttons() {
        $submitlabel = get_string('savechanges');
        $submit2label = get_string('saveandcontinue', ratingallocate_MOD_NAME);

        $mform = $this->_form;

        // elements in a row need a group
        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', $submit2label);
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Returns the forms HTML code.
     * So we don't have to call display().
     */
    public function to_html() {
        $o = '';
        $o .= $this->_form->getValidationScript();
        $o .= $this->_form->toHtml();
        return $o;
    }

}

<?php

/**
 * Event observers.
 *
 * @package mod_booking
 * @copyright 2015 Andraž Prinčič <atletek@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_forum.
 */
class mod_booking_observer {

    /**
     * Observer for course_module_updated.
     *
     * @param  \core\event\course_module_updated $event
     * @return void
     */
    public static function course_module_updated(\core\event\course_module_updated $event) {
        global $DB;

        $visible = $DB->get_record('course_modules', array('id' => $event->contextinstanceid), 'visible');

        $showHide = new stdClass();
        $showHide->id = $event->other['instanceid'];
        $showHide->showinapi = $visible->visible;

        $DB->update_record("booking", $showHide);

        return;
    }

    /**
     * Observer for the user_deleted event
     *
     * @param \core\event\user_deleted $event        	
     */
    public static function user_deleted(\core\event\user_deleted $event) {
        global $DB;

        $params = array(
            'userid' => $event->relateduserid
        );
        
        $DB->delete_records_select('booking_answers', 'userid = :userid', $params);
        $DB->delete_records_select('booking_teachers', 'userid = :userid', $params);
    }

    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param \core\event\user_enrolment_deleted $event
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        GLOBAL $DB;

        $cp = (object) $event->other['userenrolment'];
        if ($cp->lastenrol) {
            $DB->execute('DELETE ba FROM {booking_answers} AS ba LEFT JOIN {booking} AS b ON b.id = ba.bookingid WHERE ba.userid = :userid AND b.course = :course', array('userid' => $cp->userid, 'course' => $cp->courseid));
            $DB->execute('DELETE ba FROM {booking_teachers} AS ba LEFT JOIN {booking} AS b ON b.id = ba.bookingid WHERE ba.userid = :userid AND b.course = :course', array('userid' => $cp->userid, 'course' => $cp->courseid));
        }
    }

}

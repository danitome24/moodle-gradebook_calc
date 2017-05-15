<?php
/**
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
/**
 * @author Daniel Tome <danieltomefer@gmail.com>
 */

require_once '../../config.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->libdir . '/pagelib.php';
//Id of course
$courseId = required_param('id', PARAM_TEXT);
$gradeId = required_param('gradeid', PARAM_TEXT);

$gtree = new grade_tree($courseId, false, false);

/// Make sure they can even access this course
if (!$course = $DB->get_record('course', array('id' => $courseId))) {
    print_error('nocourseid');
}
if (!$grade_item = grade_item::fetch(array('id' => $gradeId, 'courseid' => $courseId))) {
    print_error('invaliditemid');
}

$grades = grade_item::fetch_all(['courseid' => $courseId, 'itemtype' => 'mod']);
require_login($course);
$context = context_course::instance($course->id);

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/gradebook/advanced_operation.php', ['id' => $courseId, 'gradeid' => $gradeId]));
$PAGE->set_title(get_string('pluginname', 'local_gradebook'));
$PAGE->set_cacheable(false);
$output = $PAGE->get_renderer('local_gradebook');

$form = new local_gradebook\form\AdvancedOperationForm(
    null,
    [
        'gradeid' => $gradeId,
        'id' => $courseId,
        'gtree' => $gtree,
        'element' => $gtree->top_element,
    ],
    'post'
);

if ($formData = $form->get_data()) {
    //Make sure they can even access this course
    if (!$course = $DB->get_record('course', array('id' => $formData->id))) {
        print_error('nocourseid');
    }
    if (!$grade_item = grade_item::fetch(array('id' => $formData->gradeid, 'courseid' => $course->id))) {
        print_error('invaliditemid');
    }
    if (!$grade_item_condition_1 = grade_item::fetch(array('idnumber' => $formData->grade_condition_1, 'courseid' => $course->id))) {
        print_error('invaliditemid');
    }
    if (!$grade_item_condition_2 = grade_item::fetch(array('idnumber' => $formData->grade_condition_2, 'courseid' => $course->id))) {
        print_error('invaliditemid');
    }
    $calculation = '[[' . $formData->positive_result . ']]*min(1,round([[' . $grade_item_condition_1->idnumber . ']]/(2*[[' . $grade_item_condition_2->idnumber . ']])))+[[' . $formData->negative_result . ']]*(1-min(1,round([[' . $grade_item_condition_1->idnumber . ']]/(2*[[' . $grade_item_condition_2->idnumber . ']]))))';
    $calculation = '=' . $calculation;
    if (!$grade_item->validate_formula($calculation)) {
        print_error('error');
    }
    $grade_item->set_calculation($calculation);

    $message = get_string('add_operation_success', 'local_gradebook');
    $urlToRedirect = new \moodle_url('/local/gradebook/index.php', ['id' => $courseId]);
    redirect($urlToRedirect, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

echo html_writer::start_div('container-fluid advanced-operation');
echo html_writer::start_div('row-fluid');
$form->display();
echo html_writer::end_div();
echo html_writer::end_div();
echo $OUTPUT->footer();

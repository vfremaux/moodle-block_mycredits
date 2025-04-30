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
 * @package     block_shop_access
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

class block_mycredits extends block_base {

    public function init() {
        $this->title = get_string('blocktitle', 'block_mycredits');
    }

    public function has_config() {
        return false;
    }

    public function specialization() {
        $this->title = !empty($this->config->title) ? format_string($this->config->title) : $this->title;
    }

    public function instance_allow_config() {
        return true;
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function applicable_formats() {
        // Default case: the block can be used in all course types.
        return array('all' => true);
    }

    public function get_content() {
        global $OUTPUT, $USER, $DB, $COURSE;

        if (!empty($this->content)) {
            return $this->content;
        }

        $this->content = new StdClass();
        $this->content->text = '';

        $userid = $USER->id;

        $context = context_block::instance($this->instance->id);

        $template = new StdClass;
        if (!empty($this->config->acquireurl)) {
            $template->acquirecreditsurl = $this->config->acquireurl;
        }
        $template->credits = 0 + $DB->get_field('enrol_trainingcredits', 'coursecredits', ['userid' => $userid]);
        $template->iszero = false;
        if ($template->credits == 0) {
            $template->iszero = true;
        }

        $fs = get_file_storage();

        $systemcontext = context_system::instance();
        $template->haspicture = false;
        $files = $fs->get_area_files($systemcontext->id, 'block_mycredits', 'acquirepicture', 0, "itemid, filepath, filename", false);
        if (!empty($files)) {
            $file = array_shift($files);
            $template->haspicture = true;
            $template->pictureurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                    $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
        }

        $this->content->text = $OUTPUT->render_from_template('block_mycredits/mycredits', $template);

        $this->content->footer = '';
        if (has_capability('enrol/trainingcredits:managecredits', $this->context)) {
            $manageurl = new moodle_url('/enrol/trainingcredits/usercredits.php', ['returnid' => $COURSE->id]);
            $this->content->footer = '<a href="'.$manageurl.'">'.get_string('managecredits', 'block_mycredits').'</a>';
        }

        return $this->content;
    }
}
<?php

global $CFG;
require_once($CFG->libdir . '/gradelib.php');

class block_showgrade_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $DB, $COURSE;

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_showgrade'));
        $mform->setDefault('config_title', get_string('defaulttext', 'block_showgrade'));
        $mform->setType('config_title', PARAM_TEXT);

        $categoriesRS = grade_category::fetch_all(array('courseid'=>$COURSE->id));

        foreach($categoriesRS as $record) {
            $categories[$record->id] = $record->fullname;
        }

        $mform->addElement('select', 'config_category', get_string('category', 'block_showgrade'), $categories);


    }
}

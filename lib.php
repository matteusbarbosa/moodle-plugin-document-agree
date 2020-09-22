<?php 

function mod_contractdocument_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
/*
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        send_file_not_found();
    } */

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_contractdocument', 'attachment', $args[0], '/', $args[1]);

    send_stored_file($file);
}

function contractdocument_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Add contract instance.
 * @param object $data
 * @param object $mform
 * @return int new folder instance id
 */
function contractdocument_add_instance($data, $mform) {
    global $DB;

    $cmid        = $data->coursemodule;
    $context = context_module::instance($cmid);

    $data->id = $mform->add_instance($data, $context);


    $data->timemodified = time();

    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));



  //  $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
   // \core_completion\api::update_completion_date_event($data->coursemodule, 'contractdocument', $data->id, $completiontimeexpected);

    return $data->id;
}

/**
 * Update contract instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function contractdocument_update_instance($data, $mform) {
    global $DB;

    $cmid        = $data->coursemodule;
    $context = context_module::instance($cmid);

    $data->id = $mform->add_instance($data, $context);



    $data->timemodified = time();

    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));

  //  $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
   // \core_completion\api::update_completion_date_event($data->coursemodule, 'contractdocument', $data->id, $completiontimeexpected);

    return $data->id;
}

/**
 * Delete folder instance.
 * @param int $id
 * @return bool true
 */
function contractdocument_delete_instance($id) {
    global $DB;

    if (!$document = $DB->get_record('contractdocument', array('id'=>$id))) {
        return false;
    }
    // note: all context files are deleted automatically

    $DB->delete_records('contractdocument', array('id'=>$document->id));

    return true;
}

function contractdocument_user_outline(){}
function contractdocument_user_complete($course, $user, $mod, $contractdocument){

}
function contractdocument_print_recent_activity($course, $isteacher, $timestart){}
function contractdocument_cron(){}
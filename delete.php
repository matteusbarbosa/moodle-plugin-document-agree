<?php

require_once('../../config.php');
require('./contractdocument.php');
require('./lib.php');
require('./classes/form_delete.php');
$context = get_context_instance(CONTEXT_MODULE);

require_login();

$PAGE->set_blocks_editing_capability('mod/contractdocument:manage');

$token = required_param('token', PARAM_TEXT);
$user_id = $USER->id;
$document_id = optional_param('document_id', null, PARAM_INT);
$token_valid = check_token($token, $user_id, $document_id);
$token_confirm = optional_param('token_confirm', null, PARAM_INT);

$user_signs = $DB->get_records('contractsign', array('user_id' => $user_id), '', '*');
$document = $DB->get_record('contractdocument', array('id' => $document_id));
$document_signatures = $DB->get_records('contractsign', array('contractdocument_id' => $document_id), '', '*');

$PAGE->set_blocks_editing_capability('mod/contractdocument:view');
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'contractdocument'));
$PAGE->set_url($CFG->wwwroot . '/mod/contractdocument/form.php');
$PAGE->set_heading(get_string('pluginname', 'contractdocument'));

$is_manager = has_capability('mod/contractdocument:manage', $PAGE->context) ? true : false;

if(!$is_manager)
redirect($CFG->wwwroot . '/mod/contractdocument/list.php');

if($document != null)
$mform = new form_delete();

$previewnode = $PAGE->navigation->add(get_string('managedocuments', 'contractdocument'), new moodle_url('/mod/contractdocument/admin.php'), navigation_node::TYPE_CONTAINER);
$thingnode = $previewnode->add(get_string('deleteconfirm', 'contractdocument'), null);
$thingnode->make_active();

$token = make_token($user_id, $document_id);

echo $OUTPUT->header();

?>

<h2><?php echo get_string('deleteconfirm', 'contractdocument'); ?></h2>
<hr>

<?php if (isset($mform) && $data = $mform->get_data()) {

    if($data->token_confirm == make_token($USER->id, $data->document_id)){
        $document = $DB->get_record('contractdocument', array('id' => $data->document_id));
        $DB->delete_records('contractdocument', array('id' => $document->id));
        $DB->delete_records('contractsign', array('contractdocument_id' => $document->id));
        $DB->delete_records('files', array('component'=> 'mod_contractdocument', 'filearea' => 'attachment', 'itemid' => $document->file_id));
        ?>
<div class="alert alert-success alert-block fade in " role="alert">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?php echo get_string('deletesuccess', 'contractdocument'); ?>
    </div>
        <?php
    }
 } else {

    if($document != null){
  // FAIL / DEFAULT
  $mform->display();
    } else {
?> 

<div class="alert alert-danger alert-block fade in " role="alert">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?php echo get_string('documentnotfound', 'contractdocument'); ?>
    </div>

<?php
    }
}


echo $OUTPUT->footer();

// The rest of your code goes below this.
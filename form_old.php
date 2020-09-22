<?php
// File: /mod/contractdocument/view.php

require_once('../../config.php');
require_once('./contractdocument.php');
require_once('./lib.php');
require_once('./classes/form_document.php');

require_login();

$document_id = optional_param('document_id', null, PARAM_INT);

if(!isset($document_id)){
  echo get_string('documentnotfound', 'contractdocument');
  die;
}



$user_id = $USER->id;

$user_signs = $DB->get_records('contractsign', array('user_id' => $user_id), '', '*');
$documents_list = $DB->get_records('contractdocument', array(), '', '*');

$PAGE->set_blocks_editing_capability('mod/contractdocument:view');
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'contractdocument'));
$PAGE->set_url($CFG->wwwroot . '/mod/contractdocument/form.php');
$PAGE->set_heading(get_string('pluginname', 'contractdocument'));


$is_manager = has_capability('mod/contractdocument:manage', $PAGE->context) ? true : false;

if(!$is_manager)
redirect($CFG->wwwroot . '/mod/contractdocument/list.php');

$previewnode = $PAGE->navigation->add(get_string('listdocuments', 'contractdocument'), new moodle_url('/mod/contractdocument/admin.php'), navigation_node::TYPE_CONTAINER);
$thingnode = $previewnode->add(get_string('formdocument', 'contractdocument'), null);
$thingnode->make_active();

echo $OUTPUT->header();

?>

<?php if($document_id != null): ?>
<h2><?php echo get_string('editdocument', 'contractdocument'); ?></h2>
<?php else: ?>
<h2><?php echo get_string('createdocument', 'contractdocument'); ?></h2>
<?php endif; ?>


<?php
$context = context_system::instance();
$filemanageropts = array('subdirs' => 10, 'maxbytes' => '99999999999999999', 'maxfiles' => 50, 'context' => $context);

$mform = new form_document('document_save.php');

$options = array(
  'maxfiles' => 1,
  'maxbytes' => $CFG->maxbytes,
  'subdirs' => 0,
  'accepted_types' =>  '*'
);

$entry = new stdClass;
$entry->id = null;

if($document_id != null){

  $document = $DB->get_record('contractdocument', array('id'=>$document_id));

  if (!empty($document->id)) {
    $entry->id = $document_id;
  }

$draftitemid = $document->file_id;

file_prepare_draft_area($draftitemid, $context->id, 'mod_contractdocument', 'attachment', $entry->id,
                      $options);

$entry->attachment = $document->file_id;

$mform->set_data($entry);

} else {

  //PRELOAD USER DRAFT 

  //$draftitemid = file_get_submitted_draft_itemid('attachment');
  /*
  $draftitem_data = $DB->get_records('files', ['component'=> 'user', 'filearea' => 'draft'], $sort='id DESC', $fields='itemid', $limitfrom=1, $limitnum=1);
  $draftitemid = key($draftitem_data);
  file_prepare_draft_area($draftitem, $context->id, 'user', 'draft', $draftitemid,$options);

$entry->attachment = $draftitemid;
 */

$mform->set_data($entry);
}


$mform->display();
?>



<?php
echo $OUTPUT->footer();

// The rest of your code goes below this.
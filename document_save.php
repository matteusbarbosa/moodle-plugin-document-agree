<?php
require_once('../../config.php');
require_once('./classes/form_document.php');
$context = context_system::instance();

$PAGE->set_blocks_editing_capability('mod/contractdocument:view');
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'contractdocument'));
$PAGE->set_url($CFG->wwwroot . '/mod/contractdocument/document_save.php');
$PAGE->set_heading(get_string('pluginname', 'contractdocument'));

$previewnode = $PAGE->navigation->add(get_string('createdocument', 'contractdocument'), new moodle_url('/mod/contractdocument/form.php'), navigation_node::TYPE_CONTAINER);
$previewnode->make_active();

$is_manager = has_capability('mod/contractdocument:manage', $PAGE->context) ? true : false;

if(!$is_manager)
redirect($CFG->wwwroot . '/mod/contractdocument/list.php');

echo $OUTPUT->header();

$mform = new form_document();

if ($mform->is_cancelled()) {
  // CANCELLED
  echo '<h1>Cancelled</h1>';
  echo '<p><p>';
  echo $OUTPUT->notification(format_string('Handle form cancel operation, if cancel button is present on form'));
  echo "<a href='./upload.php?id={$id}'><input type='button' value='Try Again' /><a>";
} else if ($data = $mform->get_data()) {
  
  $document_id = isset($data->document_id) ? $data->document_id : null;
  
  
  $entry = new stdClass;
  $entry->id = null;
  

    
    $document = $DB->get_record('contractdocument', array('id'=>$document_id));

    $draftitemid = file_get_submitted_draft_itemid('attachment') != null ? file_get_submitted_draft_itemid('attachment'): $document->file_id;

    if($draftitemid == null)
    $draftitemid = $document->file_id;
    
  
  $entry->attachments = $draftitemid;
  
  $mform->set_data($entry);
  
  $filemanageropts = array('subdirs' => 10, 'maxbytes' => '99999999999999999', 'maxfiles' => 50, 'context' => $context);
  
  // Save the files submitted - Salva os arquivos submetidos do filemanager no banco de dados na tabela files.
  file_save_draft_area_files($entry->attachments, $context->id, 'mod_contractdocument', 'attachment', $entry->attachments, $filemanageropts);
  
  $document = $DB->get_record('contractdocument', array('id'=>$document_id));
  
  
  $fs = get_file_storage();
  
  $file_itemid = null;
  $download_url = null;
  
  if ($files = $fs->get_area_files($context->id, 'mod_contractdocument', 'attachment', $draftitemid)) {
    
    
    
    if(count($files) == 0){
      echo $OUTPUT->notification(get_string('nouploadnotice', 'contractdocument'));
    }
    
    
    // Look through each file being managed - pt_br verificar todos os arquivos que estao sendo gerenciados pelo filemanager
    foreach ($files as $file) {
      
      //not a directory
      if($file->get_filename() == '.')
      continue;
      
      // Build the File URL. Long process! But extremely accurate. - pt_br cria uma url para o arquivo
      $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
      
      $download_url = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path();
      
      
      $download_url = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path();
      
      // Display for file - pt_bt exibe o arquivo em caso de imagem.
      if (file_extension_in_typegroup($file->get_filename(), 'web_image')) {
        echo html_writer::empty_tag('img', array('src' => $download_url));
      }
      
      
      $file_itemid = $file->get_itemid();
      
    }
    
    
  } else {
    //No files
    //echo $OUTPUT->notification(get_string('minimumfiles', 'contractdocument'));
  }
  
  $document_id = $mform->document_save($document_id, $data->title, $data->intro['text'], $data->publish, $download_url, $file_itemid);
  $success = 1;
  
}

if(isset($success)):
  ?>
  
  <div class="alert alert-success alert-block fade in " role="alert">
  <button type="button" class="close" data-dismiss="alert">×</button>
  <?php echo get_string('savesuccess', 'contractdocument'); ?>
  </div>
  <hr>
  <a href="admin.php" class="btn btn-link pull-right"> <?php echo get_string('managedocuments', 'contractdocument'); ?></a>
  
  <?php else: ?>
  <div class="alert alert-danger alert-block fade in " role="alert">
  <button type="button" class="close" data-dismiss="alert">×</button>
  <?php echo get_string('documentnotfound', 'contractdocument'); ?>
  </div>
  <hr>
  <?php endif;?>
  
  <?php
  echo $OUTPUT->footer();
  
<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/contractdocument/lib.php');

class mod_contractdocument_mod_form extends moodleform_mod {
    
    function definition() {
        global $CFG, $DB, $OUTPUT;

        $this->create();
    }
    
    public function create(){
        global $DB, $CFG;
 
        $update_id = optional_param('update', null, PARAM_INT);

        $document = null;

        if($update_id != null){
            $cm = $DB->get_record('course_modules', array('id'=>$update_id));
            $document = $DB->get_record('contractdocument', array('id'=>$cm->instance));
        }
 
        
        $mform    =& $this->_form; // Don't forget the underscore!
        $filemanageropts = array(
            'maxfiles' => 1,
            'maxbytes' => $CFG->maxbytes,
            'subdirs' => 0,
            'accepted_types' =>  '*'
        );
        
        $mform->addElement('text', 'name', get_string('name', 'contractdocument'), ['required' => 'required']); // Add elements to your form
        $mform->setType('name', PARAM_NOTAGS);                   //Set type of element
        $mform->addRule('name', get_string('missingtitle', 'contractdocument'), 'required', null, 'client');
        
        $mform->addElement('editor', 'warningtext', get_string('warningtext', 'contractdocument'), array(
            'subdirs'=>0,
            'maxbytes'=>0,
            'maxfiles'=>0,
            'changeformat'=>0,
            'context'=>null,
            'noclean'=>0,
            'trusttext'=>0,
            'enable_filemanagement' => false));
            $mform->setType('warningtext', PARAM_RAW);
            $mform->setDefault('warningtext', ['text' => $document != null ? $document->warningtext : get_string('warningdefault', 'contractdocument')]);


                $this->standard_intro_elements();
                
                $published_timestamp = $document != null && $document->published_at != null ? ' ('.get_string('published_at', 'contractdocument').': '.date('d/m/Y H:i', $document->published_at).')' : null;
                $mform->addElement('advcheckbox', 'publish', get_string('publish', 'contractdocument'), get_string('publish', 'contractdocument'). $published_timestamp, array('group' => 1), array(0, 1));
                $mform->setDefault('publish', ($document != null && $document->published_at == null ? 0 : 1));        //Default value
               
                // FILE MANAGER
                $mform->addElement('html', '<p><span class="text-warning">'.get_string('nouploadwarning', 'contractdocument').'</span></p>');
                
                $mform->addElement('html', '<p><span class="text-danger">'.get_string('fileremovewarning', 'contractdocument').'</span></p>');
                
                $mform->addElement('filemanager', 'attachment', get_string('uploaddocument', 'contractdocument'), null, $filemanageropts);
                
                if($document != null)
                $this->filemanager_load($mform, $cm);

                $this->standard_grading_coursemodule_elements();
                
                $features = new stdClass();
                $features->groups           = false;
                $features->groupings        = false;
                $features->groupmembersonly = true;

                $this->standard_coursemodule_elements($features);
                // Buttons
                $this->add_action_buttons();

                $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);


                
                return $mform;
            }
            
            
            function data_preprocessing(&$default_values) {
                parent::data_preprocessing($default_values);
            
                
            }

            /* 
                RETURN $entry
            */

            function filemanager_load($mform, $cm){
                global $CFG, $DB;

                $context = context_module::instance($cm->id);

                $document = $DB->get_record('contractdocument', ['id' => $cm->instance]);
                
                $filemanageropts = array('subdirs' => 10, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => 50);

                $options = array(
                  'maxfiles' => 1,
                  'maxbytes' => $CFG->maxbytes,
                  'subdirs' => 0,
                  'accepted_types' =>  '*'
                );
                
                $entry = new stdClass;
                $entry->id = null;
                
                if($document != null){                
                  if (!empty($document->id)) {
                    $entry->id = $cm->instance;
                  }
                
                $draftitemid = $document->file_id;

                
                file_prepare_draft_area($draftitemid, $context->id, 'mod_contractdocument', 'attachment', $entry->id,
                                      $options);
                
                $entry->attachment = $document->file_id;

                parent::set_data($entry);

                return $entry;

                
                }
            }
            
            
            /**
            * Allows module to modify the data returned by form get_data().
            * This method is also called in the bulk activity completion form.
            *
            * Only available on moodleform_mod.
            *
            * @param stdClass $data the form data to be modified.
            */
            public function data_postprocessing($data) {
                parent::data_postprocessing($data);
                
            }
            
            function add_instance($data, $context){
                
                global $DB;
                
                $update_id = optional_param('update', null, PARAM_INT);

                $document = null;
        
                if($update_id != null){
                    $cm = $DB->get_record('course_modules', array('id'=>$update_id));
                    $document = $DB->get_record('contractdocument', array('id'=>$cm->instance));
                    $draftitemid = $document->file_id;
                }      else {
                    $draftitemid = file_get_submitted_draft_itemid('attachment');
                }   

                $entry = new stdClass;
                $entry->id = null;
                
                $entry->attachments = $draftitemid;
                
                $filemanageropts = array('subdirs' => 10, 'maxbytes' => '99999999999999999', 'maxfiles' => 50, 'context' => $context);
                
                // Save the files submitted - Salva os arquivos submetidos do filemanager no banco de dados na tabela files.
                file_save_draft_area_files($entry->attachments, $context->id, 'mod_contractdocument', 'attachment', $entry->attachments, $filemanageropts);
                
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
                
                return $this->document_save($update_id, $data->name, $data->intro, $data->warningtext['text'], $data->publish, $download_url, $file_itemid);
                
            }
            
            function document_save($document_id = null, $name, $intro, $warningtext, $publish, $download_url, $file_id){ 
                global $DB, $USER;
                
                $record = new stdClass();
                
                if($document_id != null){
                    $document = $DB->get_record('contractdocument', array('id'=>$document_id));
                    if($document != null)
                    $record->id = $document_id;
                }
                
                $record->name         = $name;
                $record->intro         = $intro;
                $record->warningtext         = $warningtext;
                
                
                if(isset($document->published_at)){
                    if($publish == 1)
                    $record->published_at  = $document->published_at;
                    else
                    $record->published_at = null;
                } else {
                    if($publish == 1)
                    $record->published_at = strtotime('now');
                    else
                    $record->published_at = null;
                }
                
                
                $record->user_id         = $USER->id;
                $record->url         = $download_url;
                $record->file_id         = $file_id;
                $record->created_at         = strtotime('now');

           
                
                if($document_id != null){
                    $record->id = $document_id;                 
                    $record->updated_at         = strtotime('now');
                    $DB->update_record('contractdocument', $record, false);                    
                }
                else{
                    $record->id = $DB->insert_record('contractdocument', $record, true, false);
                
                }
                
                return $record->id;
                
            }
            
            
        }
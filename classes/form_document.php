<?php

require($CFG->dirroot . '/course/moodleform_mod.php');

class form_document extends moodleform {
    function definition() {
        global $DB;
        
        $document_id = optional_param('document_id', null, PARAM_INT);
        
        if($document_id != null){
            $mform = $this->edit($document_id);
        } else {
            $mform = $this->create();
        }
        
    }
    public function create(){
        global $DB;
        
        $mform = $this->_form; // Don't forget the underscore!
        $filemanageropts = $this->_customdata['filemanageropts'];
        
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
            
            $mform->addElement('advcheckbox', 'publish', get_string('publish', 'contractdocument'), get_string('publish', 'contractdocument'), array('group' => 1), array(0, 1));
            $mform->setDefault('publish', 0);        //Default value
            // FILE MANAGER
            $mform->addElement('html', '<p><span class="text-warning">'.get_string('nouploadwarning', 'contractdocument').'</span></p>');


            $mform->addElement('html', '<p><span class="text-danger">'.get_string('fileremovewarning', 'contractdocument').'</span></p>');

            
            $mform->addElement('filemanager', 'attachment', get_string('uploaddocument', 'contractdocument'), null, $filemanageropts);
            // Buttons
            $this->add_action_buttons();
            
            return $mform;
        }
        
        public function edit($document_id){
            global $DB, $CFG;
            
            $mform = $this->_form; // Don't forget the underscore!
            $filemanageropts = $this->_customdata['filemanageropts'];
            
            $document = $DB->get_record('contractdocument', array('id'=>$document_id));
            $mform->addElement('hidden', 'document_id', $document->id); // Add elements to your form
            $mform->setType('document_id', PARAM_INT);                   //Set type of element

            $mform->addElement('text', 'name', get_string('name', 'contractdocument')); // Add elements to your form
            $mform->setType('name', PARAM_NOTAGS);                   //Set type of element
            $mform->setDefault('name', $document->name);        //Default value
            $mform->addRule('name', get_string('missingtitle', 'contractdocument'), 'required', null, 'client');
            
                $published_timestamp = $document->published_at != null ? ' ('.get_string('published_at', 'contractdocument').': '.date('d/m/Y H:i', $document->published_at).')' : null;
                $mform->addElement('advcheckbox', 'publish', get_string('publish', 'contractdocument'), get_string('publish', 'contractdocument'). $published_timestamp, array('group' => 1), array(0, 1));
                $mform->setDefault('publish', ($document->published_at == null ? 0 : 1));        //Default value
                // FILE MANAGER
                
                //file_prepare_standard_filemanager($mform);


                
                if($document->file_id == null)
                $mform->addElement('html', '<p><span class="text-warning">'.get_string('nouploadwarning', 'contractdocument').'</span></p>');


                $mform->addElement('html', '<p><span class="text-danger">'.get_string('fileremovewarning', 'contractdocument').'</span></p>');


                // file_prepare_standard_filemanager($mform);
                
                $options = array(
                    'maxfiles' => 1,
                    'maxbytes' => $CFG->maxbytes,
                    'subdirs' => 0,
                    'accepted_types' =>  '*'
                );
                
                $context = context_system::instance();
                
                if (empty($entry->id)) {
                    $entry = new stdClass;
                }

                //file_prepare_standard_filemanager($entry, 'attachment', $options, $context, 'mod_contractdocument', 'attachment', $document->id);
                $mform->addElement('filemanager', 'attachment', get_string('uploaddocument', 'contractdocument'), null, $options);

                // Buttons
                $buttonarray=array();
                $buttonarray[] = $mform->createElement('html', '<a href="admin.php">'.get_string('cancel', 'contractdocument').'</a>');
                $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('confirm', 'contractdocument'));
                $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
                
                return $mform;
            }
            
            //Custom validation should be added here
            function validation($data, $files) {
                return array();
            }

            function document_save($document_id = null, $name, $intro, $publish, $download_url, $file_id){ 
                global $DB, $USER;
            
                $record = new stdClass();
            
                if($document_id != null){
                    $document = $DB->get_record('contractdocument', array('id'=>$document_id));
                       if($document != null)
                        $record->id = $document_id;
                }
            
                $record->name         = $name;
                $record->intro         = $intro;
            
            
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
                    $DB->update_record('contractdocument', $record, false);
                    $record->updated_at         = strtotime('now');
                }
                else
                $lastinsertid = $DB->insert_record('contractdocument', $record, false);
                
                
            }
        }
<?php

require($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/contractdocument/contractdocument.php');

class form_delete extends moodleform {
    function definition() {
        global $DB;
        $document_id = optional_param('document_id', null, PARAM_INT);
        if($document_id != null)
        $mform = $this->addElements($document_id);
        
    }
    public function addElements($document_id){
        global $DB, $USER, $CFG;
        
        $mform = $this->_form;

        $document = $DB->get_record('contractdocument', array('id' => $document_id));
        $user_id = $USER->id;

        $token = make_token($user_id, $document_id);

        $mform->addElement('hidden', 'document_id', $document_id);

        $mform->addElement('hidden', 'user_id', $USER->id);

        $mform->addElement('hidden', 'token', make_token($USER->id, $document_id));

        $mform->addElement('hidden', 'token_confirm', make_token($USER->id, $document_id));

        $mform->addElement('text', 'name', get_string('name', 'contractdocument'), ['disabled' => 'disabled', 'value' => $document->title ]);

        $mform->addElement('html', '<div class="well">'.$document->intro.'</div>');

       $mform->addElement('static', 'published_at', get_string('published_at', 'contractdocument'), $document->published_at != null ? date('d/m/Y H:i', $document->published_at) : get_string('unpublished'));
      
       if($document->url != null)
       $mform->addElement('static', 'file_link', get_string('file_link', 'contractdocument'), '<a href="'.$document->url.'" target="_blank">'.get_string('file_link', 'contractdocument').'</a>');

        $document_signatures = $document_signatures = $DB->get_records('contractsign', array('contractdocument_id' => $document_id), '', '*');

       $mform->addElement('static', 'published_at', get_string('countsignatures', 'contractdocument'), count($document_signatures).' '.get_string('signatures', 'contractdocument'));
       
       $buttonarray=array();
       $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('deleteconfirm' , 'contractdocument'));
       $buttonarray[] = $mform->createElement('cancel', get_string('cancel', 'contractdocument'));
       $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
            
            return $mform;
        }
        
            function save_sign($document_id, $user_id, $accept_flag = false, $decline_flag = false){ 
                global $DB;
            
                $record = new stdClass();
          
                $document = $DB->get_record('contractdocument', array('id'=>$document_id));
                $sign = $DB->get_record('contractsign', array('contractdocument_id'=>$document_id, 'user_id' => $user_id));
                       
                $record->user_id = $user_id;
                $record->contractdocument_id = $document_id;

                if($accept_flag == true)
                $record->accepted_at = strtotime('now');

                if($decline_flag == true)
                $record->declined_at = strtotime('now');

            
                if(isset($sign->id)){
                    $record->id = $sign->id;
                    $DB->update_record('contractsign', $record, $bulk=false);
                }
                
                else
                $lastinsertid = $DB->insert_record('contractsign', $record, false); 
                
            }
        }
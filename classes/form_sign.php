<?php

require($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/contractdocument/contractdocument.php');

class form_sign extends moodleform {
    function definition() {
        global $DB;

        $id = required_param('id', PARAM_INT);

        $mform = $this->addElements($id);
    
    }
    public function addElements($cmid){
        global $DB, $USER;
        
        $mform = $this->_form; // Don't forget the underscore!

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'contractdocument');
$document = $DB->get_record('contractdocument', array('id'=> $cm->instance), '*', MUST_EXIST);

        $user_id = $USER->id;

        $token = make_token($user_id, $document->id);

        $sign = $DB->get_record('contractsign', array('contractdocument_id'=> $document->id, 'user_id' => $user_id));

    


        $mform->addElement('hidden', 'user_id',$USER->id);
        $mform->setType('user_id', PARAM_INT);

        $mform->addElement('hidden', 'token', make_token($USER->id, $document->id));
        $mform->setType('token', PARAM_TEXT);
        
        $is_manager = has_capability('mod/contractdocument:manage', context_system::instance()) ? true : false;

        if($is_manager)
        $mform->addElement('html', '<div class="well">'.get_string('previewmode', 'contractdocument').'</div>');
        else {
            if($sign == null || ($sign != null && $sign->accepted_at == null && $sign->declined_at == null)){
                $radioarray=array();
                $radioarray[] = $mform->createElement('radio', 'sign', '', get_string('acceptdocument', 'contractdocument'), 1, null);
                $radioarray[] = $mform->createElement('radio', 'sign', '', get_string('declinedocument', 'contractdocument'), 0, null);
                $mform->addGroup($radioarray, 'radioar', '', array(' '), false);
                            // Buttons
                            $buttonarray=array();
                            $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('action_accept', 'contractdocument'));
                            $mform->addGroup($buttonarray, 'buttonar', null, null, false);

            } else {

                $mform->addElement('hidden', 'undo', 1);
                $mform->setType('undo', PARAM_INT);

                //normally you use add_action_buttons instead of this code

                $buttonarray=array();
                if($sign->declined_at != null){

                    $mform->addElement('html', '<div class="alert alert-warning">' .get_string('declinesuccess', 'contractdocument'). '</div>');

                    $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('undo', 'contractdocument'));
               
                
                } else if($sign->accepted_at != null){

               
                    $mform->addElement('html', '<div class="alert alert-success">' .get_string('signsuccess', 'contractdocument'). '</div>');

                    //botão DESFAZER desativado 
                    //$buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('undo', 'contractdocument'));
              
                }
    
                $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
           
            }
        }


            
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
                else
                $record->accepted_at = null;

                if($decline_flag == true)
                $record->declined_at = strtotime('now');
                else
                $record->declined_at = null;

                if(isset($sign->id)){
                    $record->id = $sign->id;
                    $DB->update_record('contractsign', $record, $bulk=false);
                }  else{
                    $record->id = $DB->insert_record('contractsign', $record, true); 
                }
                

                return $record;
                
            }
        }
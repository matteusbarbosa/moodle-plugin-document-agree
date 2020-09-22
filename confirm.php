<?php
require_once('../../config.php');
require('./contractdocument.php');
require('./classes/form_sign.php');

$context = get_context_instance(CONTEXT_MODULE);

require_login();

$document_id = required_param('document_id', PARAM_INT);
$decline = optional_param('decline', null, PARAM_INT);
$undo_action = optional_param('undo', null, PARAM_INT);
$user_id = $USER->id;
$token = optional_param('token', null, PARAM_TEXT);

$user_signs = $DB->get_records('contractsign', array('user_id' => $user_id), '', '*');
$document = $DB->get_record('contractdocument', array('id' => $document_id));


$nav_node = $PAGE->navigation->add(get_string('listdocuments', 'contractdocument'), new moodle_url('/mod/contractdocument/list.php'), navigation_node::TYPE_CONTAINER);
$thingnode = $nav_node->add(get_string('viewdocument', 'contractdocument'), null);
$thingnode->make_active();

$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'contractdocument'));
$PAGE->set_url($CFG->wwwroot . '/mod/contractdocument/confirm.php');
$PAGE->set_heading(get_string('pluginname', 'contractdocument'));

$is_manager = has_capability('mod/contractdocument:manage', $PAGE->context) ? true : false;

echo $OUTPUT->header();


$mform = new form_sign('confirm.php?document_id='.$document_id);


if ($data = $mform->get_data()) {
    $urltogo = new moodle_url('confirm.php', array('document_id' =>$data->document_id));
    if(isset($data->undo)){
        $sign = $DB->get_record('contractsign', ['contractdocument_id' =>$data->document_id, 'user_id' => $user_id], 'id');
        $sign->accepted_at = null;
        $sign->declined_at = null;
        
        $mform->save_sign( $data->document_id, $user_id, false, false);
 
    }
    
    $accept_flag = isset($data->sign) && $data->sign == 1 ? true : false;
    $decline_flag = isset($data->sign) && $data->sign == 0 ? true : false;
    $mform->save_sign($data->document_id, $data->user_id, $accept_flag, $decline_flag);

    echo '<script>location.href="'.($urltogo). '";</script>';
}

$token = make_token($user_id, $document_id);

if($document == null){
    echo $OUTPUT->notification(format_string(get_string('documentnotfound', 'contractdocument')));
    echo $OUTPUT->footer();
    die;
}

?>


<h2><?php echo get_string('contractdocument', 'contractdocument')?></h2>

<div class="row">
    <div class="col-sm-12">
        <div class="well">
<h3><?php echo $document->title; ?> <?php if ($is_manager): ?>(<a href="form.php?document_id=<?php echo $document->id; ?>"><?php echo get_string('managedocument', 'contractdocument');?></a>) <?php endif; ?></h3>
        </div>
    </div>
</div>


<?php if($document->warningtext != null && $document_id == null): ?>

<div class="row">
    <div class="col-sm-12">
        <div class="well">
        <?php echo $document->warningtext; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($document->intro != null && $document_id != null): ?>

<div class="row">
    <div class="col-sm-12">
        <div class="well">
        <?php echo $document->intro; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($document->file_id != null && $document_id != null): ?>
<div class="row">
    <div class="col-sm-12">
        <iframe src="<?php echo $document->url; ?>" style="width: 100%; height:400px;"></iframe>
    </div>
</div>
<?php endif; ?>

<style>

#fgroup_id_radioar{
    margin: 0px auto;
    width: 80%;
    display: block;
}
#fgroup_id_radioar .fgroup {
    margin: 0;
}
#fgroup_id_radioar .fgroup input{
 float: left;
}

#fgroup_id_radioar .fitemtitle {
    display: none;
}

#fgroup_id_buttonar {
    background-color: transparent;
    padding: 18px 0px 0px 0px;
    margin: 6px 0;
}

div#box-choose-wrap{
    z-index: 10;
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    display: block;
}

    div#fixed-choose label {
    font-size: 18px;
    color: #fff;
    width: 100%;
    display: block;
}

    div#fixed-choose {
    bottom: 0;
    border: 1px solid black;
    padding: 12px;
    background: rgba(0,0,0,0.8);
    width: 50vw;
    display: block; 
    margin: 0px auto;
}

div#fixed-choose input[type=radio] {
    border: 0px;
    width: 25px;
    height: 2em;
    vertical-align:middle;
}
</style>


<div id="box-choose-wrap">
<div class="well" id="fixed-choose" >
<?php   $mform->display(); ?>
</div>
</div>
    <?php
    echo $OUTPUT->footer();
    
    // The rest of your code goes below this.
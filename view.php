<?php

global $CFG;
require('../../config.php');
require_once($CFG->dirroot.'/lib/completionlib.php');
require_once('lib.php');
 
$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'contractdocument');
$document = $DB->get_record('contractdocument', array('id'=> $cm->instance), '*', MUST_EXIST);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

require('./contractdocument.php');
require('./classes/form_sign.php');

$context =  context_module::instance($cm->id);

require_login();

$document_id = optional_param('id', null, PARAM_INT);
$decline = optional_param('decline', null, PARAM_INT);
$undo_action = optional_param('undo', null, PARAM_INT);
$user_id = $USER->id;
$token = optional_param('token', null, PARAM_TEXT);

//$user_signs = $DB->get_records('contractsign', array('user_id' => $user_id), '', '*');

$nav_node = $PAGE->navigation->add(get_string('course'), new moodle_url($CFG->wwwroot. '/course/view.php?id='.$course->id), navigation_node::TYPE_CONTAINER);
$thingnode = $nav_node->add(get_string('viewdocument', 'contractdocument').': '.$document->name, null);
$thingnode->make_active();

$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'contractdocument'));
$PAGE->set_url($CFG->wwwroot . '/mod/contractdocument/view.php');
$PAGE->set_heading(get_string('pluginname', 'contractdocument'));

$is_manager = has_capability('mod/contractdocument:manage', $PAGE->context) ? true : false;

$mform = new form_sign('view.php?id='.$id);

$sign = $DB->get_record('contractsign', ['contractdocument_id' =>$cm->instance, 'user_id' => $user_id], 'id, accepted_at');

if ($data = $mform->get_data()) {
    
    $urltogo = new moodle_url('view.php', array('id' =>$id));
    if(isset($data->undo)){
        $sign->accepted_at = null;
        $sign->declined_at = null;
        
        $mform->save_sign( $document->id, $user_id, false, false);
 
    }
    
    $accept_flag = isset($data->sign) && $data->sign == 1 ? true : false;
    $decline_flag = isset($data->sign) && $data->sign == 0 ? true : false;
    $sign = $mform->save_sign($document->id, $data->user_id, $accept_flag, $decline_flag);



    echo '<script>location.href="'.($urltogo). '";</script>';
}

echo $OUTPUT->header();


$token = make_token($user_id, $document->id);

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
<h3><?php echo $document->name; ?> <?php if ($is_manager): ?>(<a href="<?php echo $CFG->wwwroot ?>/course/modedit.php?update=<?php echo $cm->id; ?>"><?php echo get_string('managedocument', 'contractdocument');?></a>) <?php endif; ?></h3>
        </div>
    </div>
</div>


<?php if($is_manager || $sign == null || @$sign->accepted_at == null && $document->warningtext != null): ?>

<div class="row">
    <div class="col-sm-12">
    <h4><?php echo get_string('warningtext', 'contractdocument'); ?></h4>
        <div class="well">
        <?php echo $document->warningtext; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($is_manager || $sign != null && $sign->accepted_at != null && $document->intro != null): ?>

<div class="row">
    <div class="col-sm-12">
        <div class="well">
        <?php echo $document->intro; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($is_manager || ($sign != null && $sign->accepted_at != null && $document->file_id != null)): ?>
<div class="row">
    <div class="col-sm-12">
<?php
$path = $document->url;
$ext = pathinfo($path, PATHINFO_EXTENSION);

?>
<?php if($ext == 'swf'): ?>
    <script type="text/javascript" src="swfobject/swfobject/swfobject.js"></script>


    <script type="text/javascript">
		swfobject.registerObject("swf_embed", "10.0.0", "./swfobject/swfobject/src/expressinstall.swf");
		</script>

    <object id="swf_embed" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="768" height="500">
				<param name="movie" value="<?php echo $document->url; ?>" />
        		<!--[if !IE]>-->
				<object type="application/x-shockwave-flash" data="<?php echo $document->url; ?>" width="768" height="500">
				<!--<![endif]-->
				<div>
					<h1>Alternative content</h1>
					<p><a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>
				</div>
				<!--[if !IE]>-->
				</object>
				<!--<![endif]-->
			</object>
   
<?php else: ?>
        <iframe src="<?php echo $document->url; ?>" style="width: 100%; height:400px;"></iframe>
        <?php endif; ?>
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

<?php if($is_manager || $sign == null || ($sign != null && $sign->accepted_at == null)): ?>
<div id="box-choose-wrap">
<div class="well" id="fixed-choose" >
<?php $mform->display(); ?>
</div>
</div>
<?php endif; ?>

    <?php
    echo $OUTPUT->footer();
    
    // The rest of your code goes below this.
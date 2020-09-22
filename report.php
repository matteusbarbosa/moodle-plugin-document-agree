<?php

require_once('../../config.php');
require('./contractdocument.php');

require_login();

$course_id = optional_param('course_id', null, PARAM_INT);
$document_id = optional_param('document_id', null, PARAM_INT);
$user_id = optional_param('user_id', null, PARAM_INT);

$documents_list = $DB->get_records('contractdocument', array(), ' name ASC', 'id, name');

$PAGE->set_blocks_editing_capability('mod/contractdocument:manage');
$previewnode = $PAGE->navigation->add(get_string('managedocuments', 'contractdocument'), new moodle_url('/mod/contractdocument/admin.php'), navigation_node::TYPE_CONTAINER);
$nav_node = $previewnode->add(get_string('reports', 'contractdocument'), null);
$nav_node->make_active();
$error = null;

$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'contractdocument'));
$PAGE->set_url($CFG->wwwroot . '/mod/contractdocument/reportdocument.php');
$PAGE->set_heading(get_string('pluginname', 'contractdocument'));

$is_manager = has_capability('mod/contractdocument:manage', $PAGE->context) ? true : false;

$users_list = $DB->get_records('user', null, 'firstname', 'id, firstname, lastname, email');

if(!$is_manager)
redirect($CFG->wwwroot . '/mod/contractdocument/list.php');


if($course_id){


    $course = $DB->get_record('course', array('id' => $course_id), 'id, fullname');
    
    $signs = $DB->get_records_sql('SELECT cs.id, c.fullname, cd.name AS doc_name, u.firstname, u.lastname, u.email, cs.accepted_at, cs.declined_at
    FROM mdl_course AS c
    JOIN mdl_course_modules as cmd ON cmd.course = c.id 
    JOIN mdl_contractdocument AS cd ON cd.id = cmd.instance
    JOIN mdl_contractsign AS cs ON cd.id = cs.contractdocument_id
    JOIN mdl_user AS u ON u.id = cs.user_id
    LEFT JOIN mdl_modules AS m ON m.id = cmd.module
    WHERE c.id = ? AND m.name = ?', [$course_id, 'contractdocument']);
    
    
    if(empty($signs))
    $error = $OUTPUT->notification(format_string(get_string('zerosignatures', 'contractdocument')));
    
} else if($user_id){

    $user = $DB->get_record('user', array('id' => $user_id), 'id, firstname, lastname');
    
    if($document_id)
    $search = ['contractdocument_id' => $document_id, 'user_id' => $user_id];
    else
    $search = ['user_id' => $user_id];
    
    $signs = $DB->get_records('contractsign', $search, '', '*');
    
    foreach($signs as $k => $sign){
        $q = $DB->get_record('contractdocument', array('id' => $sign->contractdocument_id), 'name', '*');
        
        
        $signs[$k]->document_name = !empty($q->name) ? $q->name : 'Sem nome';
    }
    
    if(empty($signs))
    $error = $OUTPUT->notification(format_string(get_string('zerosignatures', 'contractdocument')));
    
} else if($document_id){
    $document = $DB->get_record('contractdocument', array('id'=>$document_id));
    
    if($user_id)
    $search = ['contractdocument_id' => $document_id, 'user_id' => $user_id];
    else
    $search = ['contractdocument_id' => $document_id];
    
    $signs = $DB->get_records('contractsign', $search, '', '*');
    foreach($signs as $k => $us){
        $q = $DB->get_record('user', array('id' => $us->user_id), 'firstname', '*');
        $signs[$k]->user_name = $q->firstname;
    }
    
    if(empty($document))
    $error = $OUTPUT->notification(format_string(get_string('documentnotfound', 'contractdocument')));
    
}

echo '<script>console.log('.$user_id.')</script>';

$courses = get_courses();


echo $OUTPUT->header();

?>

<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        /* display: none; <- Crashes Chrome on hover */
        -webkit-appearance: none;
        margin: 0; /* <-- Apparently some margin are still there even though it's hidden */
    }
    
    .dt-button.buttons-excel {
        background-color: green;
    }
    .dt-button.buttons-pdf{
        background-color: red;
    }
</style>


<?php 
if($course_id == null && $document_id == null && $user_id == null):
echo $OUTPUT->notification(format_string(get_string('reporttyperequired', 'contractdocument')));
endif;
?>

<form method="get" id="searchform" action="">
    <div class="row">
        <div class="col-sm-4">
            <label for="user_id"><?php echo get_string('course'); ?></label> 
            <select name="course_id" class="form-control">
                <option value=""></option>
                <?php foreach($courses as $k => $c): ?>
                <?php if($c->id == $course_id): ?>
                <option selected value="<?php echo $c->id; ?>"><?php echo $c->fullname; ?></option>
                <?php else: ?>
                <option value="<?php echo $c->id; ?>"><?php echo $c->fullname; ?></option>
                <?php endif; ?>
                <?php endforeach;?>
            </select>
        </div>
        <div class="col-sm-4">
            <label for="document_id"><?php echo get_string('document', 'contractdocument'); ?></label>     
            <div class="input-group">  
                <select name="document_id" class="form-control">
                    <option value="0"></option>
                    <?php foreach($documents_list as $k => $doc): ?>
                    <?php if($doc->id == $document_id): ?>
                    <option selected value="<?php echo $doc->id; ?>"><?php echo $doc->name; ?></option>
                    <?php else: ?>
                    <option value="<?php echo $doc->id; ?>"><?php echo $doc->name; ?></option>
                    <?php endif; ?>
                    <?php endforeach;?>
                </select>
                
            </div><!-- /input-group -->
            
        </div>
        <div class="col-sm-4">
            <label for="user_id"><?php echo get_string('user'); ?></label> 
            <div class="input-group">
                <select name="user_id" class="form-control">
                    <option value=""></option>
                    <?php foreach($users_list as $k => $u): ?>
                    <?php if($u->id == $user_id): ?>
                    <option selected value="<?php echo $u->id; ?>"><?php echo $u->firstname.' '.$u->lastname.' ('.$u->email.')'; ?></option>
                    <?php else: ?>
                    <option value="<?php echo $u->id; ?>"><?php echo $u->firstname.' '.$u->lastname.' ('.$u->email.')'; ?></option>
                    <?php endif; ?>
                    <?php endforeach;?>
                </select>
                
                
            </div><!-- /input-group -->
        </div>
        <div class="col-sm-2">
            <button type="submit" style="margin-top: 26px;" class="btn btn-primary" name="submit"><?php echo get_string('search'); ?></button>
        </div>
    </div>
</form>

<hr/>
<?php echo $error; ?>
<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <?php if(isset($course) && $course_id != null && !empty($signs)): ?>
            <h2><?php echo get_string('reportcourse', 'contractdocument'); ?> – <?php echo $course->fullname; ?></h2>
            <table class="table table-striped" id="list_documents">
                <thead>
                    <tr>
                        <th><?php echo get_string('course'); ?></th>
                        <th><?php echo get_string('document', 'contractdocument'); ?></th>
                        <th><?php echo get_string('username'); ?></th>
                        <th><?php echo get_string('email'); ?></th>
                        <th><?php echo get_string('status'); ?></th>
                        <th><?php echo get_string('confirmed_at', 'contractdocument'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($signs as $k => $sign): ?>
                    
                    <?php
                    
                    $status = get_string('status_pending', 'contractdocument');
                    $confirmed_at = get_string('status_pending', 'contractdocument');
                    
                    if($sign->accepted_at != null){
                        $status = get_string('status_accepted', 'contractdocument');
                        $confirmed_at = date('d/m/Y H:i', $sign->accepted_at);
                    }
                    
                    if($sign->declined_at != null){
                        $status = get_string('status_declined', 'contractdocument');
                        $confirmed_at = date('d/m/Y H:i', $sign->declined_at);
                    }
                    ?>
                    
                    <tr>
                        <td><?php echo $sign->fullname; ?></td>
                        
                        <td><?php echo $sign->doc_name; ?></td>
                        <td><?php echo $sign->firstname.' '.$sign->lastname; ?></td>
                        <td><?php echo $sign->email; ?></td>
                        <td><?php echo $status; ?></td>
                        <td><?php echo $confirmed_at; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php echo get_string('course'); ?></th>
                        <th><?php echo get_string('document', 'contractdocument'); ?></th>
                        <th><?php echo get_string('username'); ?></th>
                        <th><?php echo get_string('email'); ?></th>
                        <th><?php echo get_string('status'); ?></th>
                        <th><?php echo get_string('confirmed_at', 'contractdocument'); ?></th>
                    </tr>
                </tfoot>
            </table>
            <?php elseif(isset($user) && $user_id != null): ?>
 
            <h2><?php echo get_string('reportuser', 'contractdocument'); ?> – <?php echo $user->firstname.' '.$user->lastname; ?></h2>
            <table class="table table-striped" id="list_documents">
                <thead>
                    <tr>
                        <th style="display: none;"><?php echo get_string('user', 'contractdocument'); ?></th>
                        <th><?php echo get_string('document', 'contractdocument'); ?></th>
                        <th><?php echo get_string('status', 'contractdocument'); ?></th>
                        <th><?php echo get_string('confirmed_at', 'contractdocument'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($signs as $k => $sign): ?>
                    <?php
                    
                    $status = get_string('status_pending', 'contractdocument');
                    $confirmed_at = get_string('status_pending', 'contractdocument');
                    
                    if($sign->accepted_at != null){
                        $status = get_string('status_accepted', 'contractdocument');
                        $confirmed_at = date('d/m/Y H:i', $sign->accepted_at);
                    }
                    
                    if($sign->declined_at != null){
                        $status = get_string('status_declined', 'contractdocument');
                        $confirmed_at = date('d/m/Y H:i', $sign->declined_at);
                    }
                    ?>
                    <tr>
                        <td style="display: none;"><?php echo $user->firstname.' '.$user->lastname; ?></td>
                        <td><?php echo $sign->document_name; ?></td>
                        <td><?php echo $status; ?></td>
                        <td><?php echo $confirmed_at; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th style="display: none;"><?php echo get_string('user', 'contractdocument'); ?></th>
                        <th><?php echo get_string('document', 'contractdocument'); ?></th>
                        <th><?php echo get_string('status', 'contractdocument'); ?></th>
                        <th><?php echo get_string('confirmed_at', 'contractdocument'); ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <?php elseif(isset($document) && $document->id != null): ?>
            <h2><?php echo get_string('reportdocument', 'contractdocument'); ?> – <?php echo $document->name; ?></h2>
            <table class="table table-striped" id="list_documents">
                <thead>
                    <tr>
                        <th><?php echo get_string('user', 'contractdocument'); ?></th>
                        <th><?php echo get_string('status', 'contractdocument'); ?></th>
                        <th><?php echo get_string('confirmed_at', 'contractdocument'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($signs as $k => $sign): ?>
                    
                    <?php
                    
                    $status = get_string('status_pending', 'contractdocument');
                    $confirmed_at = get_string('status_pending', 'contractdocument');
                    
                    if($sign->accepted_at != null){
                        $status = get_string('status_accepted', 'contractdocument');
                        $confirmed_at = date('d/m/Y H:i', $sign->accepted_at);
                    }
                    
                    if($sign->declined_at != null){
                        $status = get_string('status_declined', 'contractdocument');
                        $confirmed_at = date('d/m/Y H:i', $sign->declined_at);
                    }
                    ?>
                    
                    <tr>
                        <td><?php echo $sign->user_name; ?></td>
                        
                        <td><?php echo $status; ?></td>
                        <td><?php echo $confirmed_at; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php echo get_string('user', 'contractdocument'); ?></th>
                        <th><?php echo get_string('status', 'contractdocument'); ?></th>
                        <th><?php echo get_string('confirmed_at', 'contractdocument'); ?></th>
                    </tr>
                </tfoot>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="export_buttons"></div>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css">
<script
src="https://code.jquery.com/jquery-1.12.4.min.js"
integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
crossorigin="anonymous"></script>
<script src="jszip.min.js"></script>
<script src="pdfmake.min.js"></script>
<script src="vfs_fonts.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.js"></script>

<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>




<script>
    $(document).ready( function () {
        var list_documents = $('table#list_documents').DataTable({
            dom: 'Bfrtip',
            buttons: [
            'excel', 'pdf'
            ],
            "language":{
                "sEmptyTable": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ resultados por página",
                "sLoadingRecords": "Carregando...",
                "sProcessing": "Processando...",
                "sZeroRecords": "Nenhum registro encontrado",
                "sSearch": "Pesquisar",
                "oPaginate": {
                    "sNext": "Próximo",
                    "sPrevious": "Anterior",
                    "sFirst": "Primeiro",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            },
            paging: true,
            searching: true
        });
        
        new $.fn.dataTable.Buttons( list_documents, {
            dom: 'Bfrtip',
            buttons: [
            'copy', 'excel', 'pdf'
            ]
        } );
        
    } );
</script>

<?php
echo $OUTPUT->footer();

// The rest of your code goes below this.
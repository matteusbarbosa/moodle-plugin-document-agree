<?php

require_once('../../config.php');
require('./contractdocument.php');
$context = get_context_instance(CONTEXT_MODULE);

require_login();

$document_id = optional_param('document_id', 1, PARAM_INT);
$user_id = $USER->id;

$PAGE->set_blocks_editing_capability('mod/contractdocument:manage');
$user_signs = $DB->get_records('contractsign', array('user_id' => $user_id), '', '*');
$documents_list = $DB->get_records('contractdocument', array(), 'id DESC', '*');

$nav_node = $PAGE->navigation->add(get_string('managedocuments', 'contractdocument'), null, navigation_node::TYPE_CONTAINER);
$nav_node->make_active();

$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'contractdocument'));
$PAGE->set_url($CFG->wwwroot . '/mod/contractdocument/admin.php');
$PAGE->set_heading(get_string('pluginname', 'contractdocument'));

$is_manager = has_capability('mod/contractdocument:manage', $PAGE->context) ? true : false;

if(!$is_manager)
redirect($CFG->wwwroot . '/mod/contractdocument/list.php');

echo $OUTPUT->header();

?>

<h2><?php echo get_string('managedocuments', 'contractdocument'); ?></h2>

<div class="row">
<div class="col-sm-12">
<div class="table-responsive">
<table class="table table-striped" id="list_documents">
<thead>
<tr>
<th><?php echo get_string('document', 'contractdocument'); ?></th>
<th><?php echo get_string('status', 'contractdocument'); ?></th>
<th><?php echo get_string('published_at', 'contractdocument'); ?></th>
<th><?php echo get_string('actions', 'contractdocument'); ?></th>
<th><?php echo get_string('deletedocument', 'contractdocument'); ?></th>
</tr>
</thead>
<tbody>
<?php foreach($documents_list as $k => $document): ?>
<?php $cm = get_coursemodule_from_id('contractdocument', $document->id, 0, IGNORE_MISSING); ?>
<?php $sign = isset($user_signs[$document->id]) ? $user_signs[$document->id] : null; ?>
<?php $sign_id = $sign != null ? $sign->id : null; ?>
<?php $token = make_token($user_id, $sign_id); ?>
<tr>
<td><?php echo $document->name; ?></td>
<td><?php echo $document->published_at != null ? get_string('published', 'contractdocument') : get_string('unpublished', 'contractdocument'); ?></td>                        
<td><?php echo $document->published_at != null ? date('d/m/Y H:i', $document->published_at) : get_string('unpublished', 'contractdocument'); ?></td>
<td>
<a href="view.php?id=<?php echo $document->id; ?>"><?php echo get_string('preview', 'contractdocument');?></a>
<?php if($cm != null): ?>           
| <a href="<?php echo $CFG->wwwroot ?>/course/modedit.php?update=<?php echo $cm->id; ?>"><?php echo get_string('managedocument', 'contractdocument');?></a>
<?php endif; ?>
</td>
<td><a href="delete.php?document_id=<?php echo $document->id.'&token='.$token; ?>"><?php echo get_string('deletedocument', 'contractdocument');?></a></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr>
<th><?php echo get_string('document', 'contractdocument'); ?></th>
<th><?php echo get_string('status', 'contractdocument'); ?></th>
<th><?php echo get_string('published_at', 'contractdocument'); ?></th>
<th><?php echo get_string('actions', 'contractdocument'); ?></th>
<th><?php echo get_string('deletedocument', 'contractdocument'); ?></th>
</tr>
</tfoot>
</table>
</div>
</div>
</div>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css">
<script
src="https://code.jquery.com/jquery-1.12.4.min.js"
integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
crossorigin="anonymous"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>

<script>
$(document).ready( function () {
    $('table#list_documents').DataTable({
        "buttons": [
            {
                extend: 'pdf',
                text: 'Exportar PDF',
                /*  exportOptions: {
                    modifier: {
                        page: 'current'
                    }
                }*/
            }
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
        searching: true,
        "columnDefs": [
            { "orderable": false, "targets": [4] }
            ]
        });
    } );
    </script>
    
    <?php
    echo $OUTPUT->footer();
    
    // The rest of your code goes below this.
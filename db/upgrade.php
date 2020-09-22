<?php
/*
function xmldb_mymodule_upgrade($oldversion) {
    global $CFG;
    
    $result = TRUE;
    
    // Insert PHP code from XMLDB Editor here
    
    return $result;
    
    
} */

function xmldb_contractdocument_upgrade($oldversion) {
    table_sign_upgrade();
    table_document_upgrade();
    
    return true;
}

function table_sign_upgrade(){
   /* global $DB;
    
    $dbman = $DB->get_manager();
    $table = new xmldb_table('contractsign');
    $field_1 = new xmldb_field('declined_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $field_2 = new xmldb_field('accepted_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    
    // Conditionally launch add field newfield.
    if (!$dbman->field_exists($table, $field_1)) {
        $dbman->add_field($table, $field_1);
    }
    
    // Conditionally launch add field newfield.
    if (!$dbman->field_exists($table, $field_2)) {
        $dbman->add_field($table, $field_2);
    } */
}


function table_document_upgrade(){
    global $DB;
    /*
    $dbman = $DB->get_manager();
    $table = new xmldb_table('contractdocument'); 
    $field_1 = new xmldb_field('intro', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $field_2 = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, null, null, null, null, null);
    
    // Conditionally launch add field newfield.
    if (!$dbman->field_exists($table, $field_1)) {
        $dbman->add_field($table, $field_1);
    }
    if (!$dbman->field_exists($table, $field_2)) {
        $dbman->add_field($table, $field_2);
    } */

}

<?php



function xmldb_contractdocument_install($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    $t_contractsign = new xmldb_table('contractsign');
    $t_contractdocument = new xmldb_table('contractdocument');
    
    if (!$dbman->table_exists($t_contractsign)) {
        table_sign();
    }
    
    if (!$dbman->table_exists($t_contractdocument)) {
        table_document();
        
    }
    
    return true;
}



function table_document(){
    global $DB;
    $dbman = $DB->get_manager();
    // Define table contractdocument to be created.
    $table = new xmldb_table('contractdocument');
    
    // Adding fields to table contractdocument.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('file_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('name', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('intro', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('introformat', XMLDB_TYPE_INTEGER, null, null, null, null, null);
    $table->add_field('warningtext', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('created_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('updated_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('published_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('url', XMLDB_TYPE_TEXT, null, null, null, null, null);
    
    // Adding keys to table contractdocument.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    
    // Conditionally launch create table for contractdocument.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    
    // Contract savepoint reached.
    //upgrade_block_savepoint(true, 2018051603, 'contractdocument');
}

function table_sign(){
    global $DB;
    $dbman = $DB->get_manager();
    // Define table contractsign to be created.
    $table = new xmldb_table('contractsign');
    
    // Adding fields to table contractsign.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('user_id', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
    $table->add_field('contractdocument_id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
    $table->add_field('accepted_at', XMLDB_TYPE_NUMBER, '10', null, null, null, null);
    $table->add_field('declined_at', XMLDB_TYPE_NUMBER, '10', null, null, null, null);
    
    // Adding keys to table contractsign.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    
    // Conditionally launch create table for contractsign.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    
    // Contract savepoint reached.
    // upgrade_block_savepoint(true, 2018051603, 'contractdocument');
    
}
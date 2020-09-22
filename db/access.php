<?php

$capabilities = array(

    'mod/contractdocument:myaddinstance' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
        //'clonepermissionsfrom' => 'moodle/site:configview'
    ),

    'mod/contractdocument:addinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
        //'clonepermissionsfrom' => 'moodle/site:configview'
    ),

    'mod/contractdocument:view' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
       // 'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),
 
    'mod/contractdocument:manage' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
        //'clonepermissionsfrom' => 'moodle/site:configview'
    ),
);
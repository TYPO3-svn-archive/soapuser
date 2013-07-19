<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'SOAP User Management',
    'description' => 'Interface for authenticate, login and administer frontend users by an example SOAP server. It is a base for developing fe_user authentication by other SOAP servers.',
    'category' => 'services',
    'shy' => 0,
    'version' => '1.0.1',
    'dependencies' => 'felogin',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'alpha',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearcacheonload' => 1,
    'lockType' => '',
    'author' => 'Dirk Wildt (Die Netzmacher)',
    'author_email' => 'http://wildt.at.die-netzmacher.de',
    'author_company' => '',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'constraints' => array(
        'depends' => array(
            'felogin' => '',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
            'devlog' => '',
        ),
    ),
    'suggests' => array(
        '0' => 'devlog',
    ),
);

?>
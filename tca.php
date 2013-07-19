<?php




  ///////////////////////////////////////
  // 
  // tx_soapuser_groups
  
$TCA['tx_soapuser_groups'] = array (
  'ctrl' => $TCA['tx_soapuser_groups']['ctrl'],
  'interface' => array (
    'showRecordFieldList' => 'hidden,soapusergroup,fe_groups.note'
  ),
  'feInterface' => $TCA['tx_soapuser_groups']['feInterface'],
  'columns' => array (
    'hidden' => array (    
      'exclude' => 1,
      'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config'  => array (
        'type'    => 'check',
        'default' => '0'
      )
    ),
    'soapusergroup' => array (    
      'exclude' => 0,    
      'label' => 'LLL:EXT:soapuser/locallang_db.xml:tx_soapuser_groups.soapusergroup',
      'config' => array (
        'type' => 'input',  
        'size' => '30',  
        'eval' => 'required',
      )
    ),
    'fe_groups'  => array (
      'exclude'     => 0,
      'label' => 'LLL:EXT:soapuser/locallang_db.xml:tx_soapuser_groups.fe_group',
      'config'      => array (
        'type'      => 'select',
        'size'      => 10,
        'maxitems'  => 20,
        'items' => array (
          array ('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
          array ('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
          array ('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--'),
        ),
        'exclusiveKeys'       => '-1,-2',
        'foreign_table'       => 'fe_groups',
        'foreign_table_where' => 'ORDER BY fe_groups.title'
     ),
    ),
    'note' => array (    
      'exclude' => 0,    
      'label'   => 'LLL:EXT:soapuser/locallang_db.xml:tx_soapuser_groups.note',
      'config'  => array (
        'type' => 'text',
        'cols' => '50',
        'rows' => '10',
      ),
    ),
  ),
  'types' => array (
    '0' => array('showitem' => 'hidden,soapusergroup,fe_groups,note')
  ),
);
  // tx_soapuser_groups

?>

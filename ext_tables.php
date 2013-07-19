<?php

if (!defined ('TYPO3_MODE'))  die ('Access denied.');



    ///////////////////////////////////////////////////////////
    //
    // INDEX

    // tt_content
    // Methods for backend workflows
    // Plugin 1 configuration
    // Wizard Icons
    // TCA for tables
    // Add pagetree icons
    // Enables the Include Static Templates
    // fe_groups



    ///////////////////////////////////////////////////////////
    //
    // tt_content

  t3lib_div::loadTCA('tt_content');
    // tt_content



    ///////////////////////////////////////////////////////////
    //
    // Methods for backend workflows
  
  require_once(t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_soapuser_pi1_backend.php');

  

    ///////////////////////////////////////////////////////////
    //
    // Plugin 1 configuration

  $TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
    // Remove the default tt_content fields layout, select_key, pages and recursive.
  $TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
    // Display the field pi_flexform
  t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/pi1/flexform.xml');
    // Register our file with the flexform structure
  t3lib_extMgm::addPlugin(array('LLL:EXT:soapuser/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1', 'EXT:soapuser/ext_icon.gif'),'list_type');
    // Add the Flexform to the Plugin List
    // Plugin 1 configuration



    ///////////////////////////////////////////////////////////
    //
    // Wizard Icons

//  if (TYPO3_MODE=='BE')
//  {
//    $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_soapuser_pi1_be_wizicon'] =
//      t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_soapuser_pi1_be_wizicon.php';
//  }
    // Wizard Icons



    ////////////////////////////////////
    //
    // TCA for tables

    // tx_soapuser_groups
  $TCA['tx_soapuser_groups'] = array (
    'ctrl' => array (
      'title'     => 'LLL:EXT:soapuser/locallang_db.xml:tx_soapuser_groups',
      'label'     => 'soapgroup',  
      'tstamp'    => 'tstamp',
      'crdate'    => 'crdate',
      'cruser_id' => 'cruser_id',
      'delete'    => 'deleted',
      'enablecolumns' => array (
        'disabled' => 'hidden',
      ),
      'default_sortby'    => 'ORDER BY soapgroup, fe_groups',  
      'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
      'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
      //'rootlevel'         => '1',
    ),
  );
  //t3lib_extMgm::allowTableOnStandardPages('tx_soapuser_groups');
    // tx_soapuser_groups




    ////////////////////////////////////////////////////////////////////////////
    //
    // Add pagetree icons

  $TCA['pages']['columns']['module']['config']['items'][] =
     array('DAT Users: FE-Login', 'feusrlgin', t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif');
  $TCA['pages']['columns']['module']['config']['items'][] =
     array('DAT Users: Administration', 'feusradmn', t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif');
  t3lib_SpriteManager::addTcaTypeIcon('pages', 'contains-feusrlgin', '../typo3conf/ext/soapuser/ext_icon.gif');
  t3lib_SpriteManager::addTcaTypeIcon('pages', 'contains-feusradmn', '../typo3conf/ext/soapuser/ext_icon.gif');
    // Add pagetree icons



    ////////////////////////////////////////////////////////////////////////////
    //
    // Enables the Include Static Templates

  t3lib_extMgm::addStaticFile($_EXTKEY,'static/felogin/pi1/', 'SOAP users: felogin');
  t3lib_extMgm::addStaticFile($_EXTKEY,'static/pi1/', 'SOAP users for powermail');
    // Enables the Include Static Templates



    ////////////////////////////////////////////////////////////////////////////
    //
    // fe_groups

  t3lib_div::loadTCA('fe_groups');
  $TCA['fe_groups']['ctrl']['title']          = 'LLL:EXT:soapuser/locallang_db.xml:tx_soapuser_groups.fe_group';
  $TCA['fe_groups']['ctrl']['default_sortby'] = 'ORDER BY title';
    // fe_groups

?>
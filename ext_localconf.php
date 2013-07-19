<?php

if (!defined ('TYPO3_MODE'))  die ('Access denied.');



  ////////////////////////////////////////////////////
  //
  // Index
  //
  // addPItoST43
  // Add service
  // Hooks
  



  ////////////////////////////////////////////////////
  //
  // addPItoST43
  
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_soapuser_pi1.php','_pi1','list_type',1);
  // addPItoST43



  ////////////////////////////////////////////////////
  //
  // Add service
  
t3lib_extMgm::addService($_EXTKEY, 'auth',  'tx_soapuser_sv1',
  array(
    'title'       => 'User authentication by a SOAP server',
    'description' => 'Authentication with username/password by a SOAP server',
    'subtype'     => 'authUserFE,getUserFE',
    'available'   => true,
    'priority'    => 80,
    'quality'     => 80,
    'os'          => '',
    'exec'        => '',
    'classFile'   => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_soapuser_sv1.php',
    'className'   => 'tx_soapuser_sv1',
  )
);
  // Add service



  ////////////////////////////////////////////////////
  //
  // Hooks
  
if (TYPO3_MODE!='BE') 
{
  require_once(t3lib_extMgm::extPath($_EXTKEY).'lib/hooks/class.tx_soapuser_hooks.php');
}
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['postProcContent'][]  = 'tx_soapuser_hooks->felogin_postProcContent';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['logout_confirmed'][] = 'tx_soapuser_hooks->felogin_logout_confirmed';
  // Hooks
?>
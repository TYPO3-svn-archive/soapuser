<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 - Dirk Wildt <http://wildt.at.die-netzmacher.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/



require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Plugin 'Login' for the 'soapuser' extension.
 *
 * @author    Dirk Wildt <http://wildt.at.die-netzmacher.de>
 * @package    TYPO3
 * @subpackage  soapuser
 *
 * @version 0.1.0
 * @since 0.0.1
 */

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   68: class tx_soapuser_pi1 extends tslib_pibase
 *
 *              SECTION: Main Process
 *  142:     public function main( $content, $conf )
 *
 *              SECTION: DRS - Development Reporting System
 *  203:     private function init_accessByIP( )
 *  232:     private function initDrs( )
 *
 *              SECTION: Flexform
 *  316:     private function initFlexform( )
 *  335:     private function initFlexformSheetPowermail( )
 *
 *              SECTION: SOAP
 *  407:     private function soapUpdate( $content )
 *
 *              SECTION: Session
 *  537:     private function sessionSetForPowermail( $content )
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_soapuser_pi1 extends tslib_pibase
{

 /**
  * Class name
  *
  * @var string
  */
  public $prefixId  = 'tx_soapuser_pi1';

 /**
0  * Path to this script relative to the extension directory
  *
  * @var string
  */
  public $scriptRelPath  = 'pi1/class.tx_soapuser_pi1.php';

 /**
  * Extension key
  *
  * @var string
  */
  public $extKey = 'soapuser';

 /**
  * Configuration by the extension manager
  *
  * @var array
  */
  private $arr_extConf;

 /**
  * Current IP is met allowed IPs
  *
  * @var boolean
  */
  private $bool_accessByIP;

 /**
  * Current TypoScript configuration
  *
  * @var array
  */
  public $conf;

 /**
  * Flexform value powermail.uid
  *
  * @var integer
  */
  private $ffPowermailUid;

 /**
  * Flexform value powermail.confirm
  *
  * @var boolean
  */
  private $ffPowermailConfirm;

 /**
  * Current feuser record from table fe_users
  *
  * @var array
  */
  private $feuserRecord;






  /***********************************************
   *
   * Main Process
   *
   **********************************************/



/**
 * main( ): Main method of your PlugIn
 *
 * @param    string        $content: The content of the PlugIn
 * @param    array        $conf: The PlugIn Configuration
 * @return    string        The content that should be displayed on the website
 * @version 0.1.0
 * @since   0.0.1
 */
  public function main( $content, $conf )
  {
      // Globalise TypoScript configuration
    $this->conf = $conf;
      // Init localisation
    $this->pi_loadLL();
      // Get the values from the localconf.php file
    $this->arr_extConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey] );
      // Init DRS - Development Reporting System
    $this->initDrs( );
      // Init access by IP
    $this->init_accessByIP( );
      // Init flexform values
    $this->initFlexform( );

      // Update SOAP user data
    $content = $this->soapUpdate( $content );
      // Set session data for Powermail
    $content = $this->session( $content );

      // Don't display content, if current IP doesn't match list of allowed IPs
    if( ! $this->bool_accessByIP )
    {
      return;
    }
      // Don't display content, if current IP doesn't match list of allowed IPs

      // Display content for the current IP
    $prompt = 'Debugging report isn\'t visible for other clients. It\'s visible for allowed IPs only.';
    $content = '
      <div style="border:.4em solid  darkBlue;margin:0 0 1em 0;padding:1em;text-align:center;">
        ' . $prompt . '
      </div>' . PHP_EOL .
      $content;
    return $this->pi_wrapInBaseClass( $content );
      // Display content for the current IP
  }









  /***********************************************
   *
   * DRS - Development Reporting System
   *
   **********************************************/



  /**
 * init_accessByIP( ):  Set the global $bool_accessByIP.
 *
 * @return    void
 * @version 0.1.0
 * @since   0.1.0
 */
  private function init_accessByIP( )
  {
      // No access by default
    $this->bool_accessByIP = false;

      // Get list with allowed IPs
    $csvIP      = $this->arr_extConf['allowedIPs'];
    $currentIP  = t3lib_div :: getIndpEnv( 'REMOTE_ADDR' );

      // Current IP is an element in the list
    $pos = strpos( $csvIP, $currentIP );
    if( ! ( $pos === false ) )
    {
      $this->bool_accessByIP = true;
    }
//var_dump( __METHOD__, __LINE__, $csvIP, $currentIP, $this->bool_accessByIP );
      // Current IP is an element in the list
  }



/**
 * initDrs( ): Set the booleans for Warnings, Errors and DRS - Development Reporting System
 *
 * version 0.1.0
 * since  0.0.1
 *
 * @return    void
 */
  private function initDrs( )
  {

      //////////////////////////////////////////////////////////////////////
      //
      // Set the DRS mode

    if ($this->arr_extConf['drs_mode'] == 'All')
    {
      $this->b_drs_all        = true;
      $this->b_drs_error      = true;
      $this->b_drs_warn       = true;
      $this->b_drs_info       = true;
      $this->b_drs_flexform   = true;
      $this->b_drs_security   = true;
      $this->b_drs_services   = true;
      $this->b_drs_session    = true;
      $this->b_drs_sql        = true;
      $this->b_drs_update     = true;
      $prompt = 'DRS - Development Reporting System: ' . $this->arr_extConf['drs_mode'];
      t3lib_div::devlog('[INFO/DRS] '. $prompt, $this->extKey, 0);
    }
    if ($this->arr_extConf['drs_mode'] == 'Flexform')
    {
      $this->b_drs_error      = true;
      $this->b_drs_warn       = true;
      $this->b_drs_info       = true;
      $this->b_drs_flexform   = true;
      $prompt = 'DRS - Development Reporting System: ' . $this->arr_extConf['drs_mode'];
      t3lib_div::devlog('[INFO/DRS] '. $prompt, $this->extKey, 0);
    }
    if ($this->arr_extConf['drs_mode'] == 'Security')
    {
      $this->b_drs_error      = true;
      $this->b_drs_warn       = true;
      $this->b_drs_info       = true;
      $this->b_drs_security   = true;
      $prompt = 'DRS - Development Reporting System: ' . $this->arr_extConf['drs_mode'];
      t3lib_div::devlog('[INFO/DRS] '. $prompt, $this->extKey, 0);
    }
    if ($this->arr_extConf['drs_mode'] == 'Servives')
    {
      $this->b_drs_error      = true;
      $this->b_drs_warn       = true;
      $this->b_drs_info       = true;
      $this->b_drs_services   = true;
      $prompt = 'DRS - Development Reporting System: ' . $this->arr_extConf['drs_mode'];
      t3lib_div::devlog('[INFO/DRS] '. $prompt, $this->extKey, 0);
    }
    if ($this->arr_extConf['drs_mode'] == 'Session')
    {
      $this->b_drs_error      = true;
      $this->b_drs_warn       = true;
      $this->b_drs_info       = true;
      $this->b_drs_session    = true;
      $prompt = 'DRS - Development Reporting System: ' . $this->arr_extConf['drs_mode'];
      t3lib_div::devlog('[INFO/DRS] '. $prompt, $this->extKey, 0);
    }
    if ($this->arr_extConf['drs_mode'] == 'Update')
    {
      $this->b_drs_error      = true;
      $this->b_drs_warn       = true;
      $this->b_drs_info       = true;
      $this->b_drs_sql        = true;
      $this->b_drs_update     = true;
      $prompt = 'DRS - Development Reporting System: ' . $this->arr_extConf['drs_mode'];
      t3lib_div::devlog('[INFO/DRS] '. $prompt, $this->extKey, 0);
    }
      // Set the DRS mode

  }









  /***********************************************
   *
   * Flexform
   *
   **********************************************/



/**
 * initFlexform( ):
 *
 * version 0.1.0
 * since  0.0.1
 *
 * @return    void
 */
  private function initFlexform( )
  {
      // Init methods for pi_flexform
    $this->pi_initPIflexForm();

      // Sheet powermail
    $this->initFlexformSheetPowermail( );
  }



/**
 * initFlexformSheetPowermail( ):
 *
 * version 0.1.0
 * since  0.1.0
 *
 * @return    void
 */
  private function initFlexformSheetPowermail( )
  {
    $arr_piFlexform = $this->cObj->data['pi_flexform'];
    $sheet          = 'powermail';

    
    
      ////////////////////////////////////////////////
      //
      // Field uid

    $field = 'uid';
      // Set the global ffPowermailUid
    $this->ffPowermailUid = ( int ) $this->pi_getFFvalue($arr_piFlexform, $field, $sheet, 'lDEF', 'vDEF');

      // DRS
    switch( true )
    {
      case( empty( $this->ffPowermailUid ) ):
        if( $this->b_drs_error )
        {
          $prompt = 'powermail.uid is empty. You will get trouble with prefilled data in the powermail form.';
          t3lib_div::devlog(' [ERROR/FLEXFORM] '. $prompt, $this->extKey, 3 );
        }
        break;
      default:
        if( $this->b_drs_warn )
        {
          $prompt = 'powermail.uid is ' . $this->ffPowermailUid . '. Take care for a proper uid. With
            an unproper uid you will get trouble with prefilled data in the powermail form.';
          t3lib_div::devlog(' [ERROR/FLEXFORM] '. $prompt, $this->extKey, 3 );
        }
        break;
    }
      // DRS

      // DRS
    if( $this->b_drs_flexform )
    {
      $prompt = 'powermail.confirm is ' . $this->ffPowermailUid;
      t3lib_div::devlog(' [INFO/FLEXFORM] '. $prompt, $this->extKey, 0 );
      if( $this->ffPowermailUid )
      {
        $prompt = 'SOAP data will updated only, if the confirm page in the Powermail plugin is enabled!';
        t3lib_div::devlog(' [INFO/FLEXFORM] '. $prompt, $this->extKey, 2 );
      }
      if( ! $this->ffPowermailUid )
      {
        $prompt = 'SOAP data will updated only, if the confirm page in the Powermail plugin is disabled!';
        t3lib_div::devlog(' [INFO/FLEXFORM] '. $prompt, $this->extKey, 2 );
      }
    }
      // DRS
      // Field uid

    
    
      ////////////////////////////////////////////////
      //
      // Field confirm

    $field = 'confirm';
      // Get the value
    $value = $this->pi_getFFvalue($arr_piFlexform, $field, $sheet, 'lDEF', 'vDEF');

      // Set the global ffPowermailConfirm
    switch( true )
    {
      case( $value === '0' ):
        $this->ffPowermailConfirm = false;
        break;
      case( $value === null ):
      case( $value === '1' ):
      default:
        $this->ffPowermailConfirm = true;
        break;
    }
      // Set the global ffPowermailConfirm

      // DRS
    if( $this->b_drs_flexform )
    {
      $prompt = 'powermail.confirm is ' . $this->ffPowermailConfirm;
      t3lib_div::devlog(' [INFO/FLEXFORM] '. $prompt, $this->extKey, 0 );
      if( $this->ffPowermailConfirm )
      {
        $prompt = 'SOAP data will updated only, if the confirm page in the Powermail plugin is enabled!';
        t3lib_div::devlog(' [INFO/FLEXFORM] '. $prompt, $this->extKey, 2 );
      }
      if( ! $this->ffPowermailConfirm )
      {
        $prompt = 'SOAP data will updated only, if the confirm page in the Powermail plugin is disabled!';
        t3lib_div::devlog(' [INFO/FLEXFORM] '. $prompt, $this->extKey, 2 );
      }
    }
      // DRS
      // Field confirm
  }









  /***********************************************
   *
   * SQL
   *
   **********************************************/



 /**
  * feuserSqlSelect( ):
  *
  * @return    boolean        true:
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserSqlSelect( )
  {
    $loginUser = $GLOBALS['TSFE']->loginUser;
    if( ! $loginUser )
    {
      if( $this->b_drs_error )
      {
        $prompt = 'Abort: $GLOBALS[TSFE]->loginUser isn\'t true!';
        t3lib_div::devlog(' [ERROR/UPDATE] '. $prompt, $this->extKey, 3 );
      }
      return false;
    }
    
    $uid = $GLOBALS['TSFE']->fe_user->user['uid'];
    if( empty( $uid ) )
    {
      if( $this->b_drs_error )
      {
        $prompt = 'Abort: $GLOBALS[TSFE]->fe_user->user[uid] is empty!';
        t3lib_div::devlog(' [ERROR/UPDATE] '. $prompt, $this->extKey, 3 );
      }
      return false;
    }
    
      // Pid of fe_users
    $pid      = ( int ) $this->arr_extConf['fe_usersPid'];

      // Query
    $select_fields  = '*';
    $from_table     = 'fe_users';
    $where_clause   = "pid = " . $pid . " AND uid = " . $uid;
    $groupBy        = '';
    $orderBy        = '';
    $limit          = '';
      // Query

      // DRS
    if( $this->b_drs_sql )
    {
      $query  = $GLOBALS['TYPO3_DB']->SELECTquery
                (
                  $select_fields,
                  $from_table,
                  $where_clause,
                  $groupBy,
                  $orderBy,
                  $limit
                );
      $prompt = $query;
      t3lib_div::devlog(' [INFO/SQL] '. $prompt, $this->extKey, 0 );
    }
      // DRS
      
      // Execute SELECT
    $res =  $GLOBALS['TYPO3_DB']->exec_SELECTquery
            (
              $select_fields,
              $from_table,
              $where_clause,
              $groupBy,
              $orderBy,
              $limit
            );
      // Execute SELECT

      // Handle result
    $this->feuserRecord =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $res );

      // RETURN: no row
    if( empty( $this->feuserRecord ) )
    {
      if( $this->b_drs_error )
      {
        $prompt = 'Abort. SQL query is empty!';
        t3lib_div::devlog(' [ERROR/SQL] '. $prompt, $this->extKey, 3 );
      }
      return false;
    }
      // RETURN: no row
      // Handle result

    return true;
  }









  /***********************************************
   *
   * Session
   *
   **********************************************/



/**
 * session( ): Main method of your PlugIn
 *
 * @param    string        $content: The content of the PlugIn
 * @param    array        $conf: The PlugIn Configuration
 * @return    string        The content that should be displayed on the website
 * @version 0.1.0
 * @since   0.1.0
 */
  private function session( $content )
  {
      // RETURN: Error with SQL query
    if( ! $this->feuserSqlSelect( ) )
    {
      if( $this->b_drs_error )
      {
        $prompt = 'Abort: Error with SQL query.';
        t3lib_div::devlog(' [ERROR/SESSION] '. $prompt, $this->extKey, 3 );
      }
      $content = $content . '
        <div style="border:.4em solid red;margin:0 0 1em 0;padding:1em;text-align:center;">
          ERROR with SQL query. Please take a look into the DRS - Development Reporting System.
        </div>';
      return $content;
    }
      // RETURN: Error with SQL query
    
      // Reset Powermail session data
    $this->sessionPowermailReset( );
    
      // Set SOAP user session data
    $this->sessionSoapuserSet( );
    
      // DRS
    if( $this->b_drs_session )
    {
      $prompt = 'Session data of soapuser: ' . print_r( $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' ), true );
      t3lib_div::devlog(' [INFO/SESSION] '. $prompt, $this->extKey, 0 );
    }
      // DRS
      
      // RETURN: Session data are empty
    $sessionData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
    if( empty ( $sessionData ) )
    {
      if( $this->b_drs_session )
      {
        $prompt = 'Session data are empty!';
        t3lib_div::devlog(' [ERROR/SESSION] '. $prompt, $this->extKey, 3 );
      }
      $content = $content . '
        <div style="border:.4em solid red;margin:0 0 1em 0;padding:1em;text-align:center;">
          ERROR: Session data soapuser are empty!
        </div>';
      return $content;
    }
      // RETURN: Session data are empty
    
      // Session data are set successful
    $content = $content . '
      <div style="border:.4em solid green;margin:0 0 1em 0;padding:1em;text-align:center;">
        Session data soapuser are set.
      </div>';
    return $content;
      // Session data are set successful
  }



/**
 * sessionSoapUsersSet( ):
 *
 * @return    void
 * @version 0.1.0
 * @since   0.1.0
 */
  private function sessionSoapUsersSet( )
  { 
      // Array for session data
    $newSessionData = array( );

      // Get current session data
    $currSessionData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );

      // Add client number to session data
    $newSessionData['soapuser_clientno'] = $currSessionData['soapuser_clientno'];

      // Add fe_user record elements to session data
    foreach( $this->feuserRecord as $key => $value )
    {
      switch( $key )
      {
        case( 'uc' ):
          continue 2;
          break;
        case( 'username' ):
          $newSessionData['username'] = $value;
          break;
      }
      $newSessionData[ 'fe_users.' . $key ] = $value;
    }
      // Add fe_user record elements to session data
      
      // Set session data
    $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $newSessionData );
    $GLOBALS["TSFE"]->storeSessionData();
  }



/**
 * sessionPowermailReset( ):
 *
 * @return    void
 * @version 0.1.0
 * @since   0.1.0
 */
  private function sessionPowermailReset( )
  {
    $arrParamPowermail = t3lib_div::_GP( 'tx_powermail_pi1' );

      // RETURN: there are Powermail parameter
    if( ! empty( $arrParamPowermail ) )
    {
      return;
    }
      // RETURN: there are Powermail parameter

      // Remove Powermail session data
      // Powermail plugin uid
    $uid = $this->ffPowermailUid; 
      // DRS
    if( $this->b_drs_session )
    {
      $tmpData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'powermail_' . $uid );
      if( ! empty ( $tmpData ) )
      {
        $prompt = 'Powermail uid seems to be Ok: ' . $uid;
        t3lib_div::devlog(' [OK/SESSION] '. $prompt, $this->extKey, -1 );
      }
    }
      // DRS
      // Remove Powermail session data
    $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'powermail_' . $uid, array( ) );
      // DRS
    if( $this->b_drs_session )
    {
      $prompt = 'Session data powermail_' . $uid .' are removed.';
      t3lib_div::devlog(' [INFO/SESSION] '. $prompt, $this->extKey, 0 );
    }
      // DRS
      // Remove Powermail session data
  }









  /***********************************************
   *
   * SOAP
   *
   **********************************************/



/**
 * soapUpdate( ): Main method of your PlugIn
 *
 * @param    string        $content: The content of the PlugIn
 * @param    array        $conf: The PlugIn Configuration
 * @return    string        The content that should be displayed on the website
 * @version 0.1.0
 * @since   0.1.0
 */
  private function soapUpdate( $content )
  {
      // Get the Powermail Paramater
    $arrParamPowermail = t3lib_div::_GP( 'tx_powermail_pi1' );

      // RETURN: no parameter
    if( empty( $arrParamPowermail ) )
    {
      if( $this->b_drs_update )
      {
        $prompt = 'No powermail parameter. No update of the SOAP user data.';
        t3lib_div::devlog(' [INFO/UPDATE] '. $prompt, $this->extKey, 0 );
      }
      $content = $content . '
        <div style="border:.4em solid  darkBlue;margin:0 0 1em 0;padding:1em;text-align:center;">
          ' . $prompt . '
        </div>';
      return $content;
    }
      // RETURN: no parameter

    switch( true )
    {
      case( $this->ffPowermailConfirm == false ):
          // CASE: without a powermail confirmation page
          // Follow the workflow: update the SOAP user data
          // DRS
        if( $this->b_drs_update )
        {
          $prompt = 'Powermail parameter are given: SOAP user data will updated.';
          t3lib_div::devlog(' [INFO/UPDATE] '. $prompt, $this->extKey, 0 );
          $prompt = 'The update is unwanted, if the Powermail confirmation page is enabled!';
          t3lib_div::devlog(' [INFO/UPDATE] '. $prompt, $this->extKey, 2 );
        }
        break;
          // DRS
          // CASE: no confirmation page
      case( $this->ffPowermailConfirm == true ):
      default:
          // CASE: with a powermail confirmation page
          // RETURN: Current Powermail form isn't the one after confirmation
        if( $arrParamPowermail['sendNow'] != 1 )
        {
            // DRS
          if( $this->b_drs_update )
          {
            $prompt = 'Powermail parameter sendNow isn\'t 1: No update of the SOAP user data.';
            t3lib_div::devlog(' [INFO/UPDATE] '. $prompt, $this->extKey, 0 );
            $prompt = 'If you have expected another workflow, please change the value of powermail.confirm
                    in the SOAP user flexform.';
            t3lib_div::devlog(' [INFO/UPDATE] '. $prompt, $this->extKey, 2 );
          }
          $prompt = 'Powermail parameter sendNow isn\'t 1: No update of the SOAP user data.';
          $content = $content . '
            <div style="border:.4em solid  darkBlue;margin:0 0 1em 0;padding:1em;text-align:center;">
              ' . $prompt . '
            </div>';
          $prompt = 'If you have expected another workflow, please change the value of powermail.confirm
                    in the SOAP user flexform. Or take a look into the DRS - Development Reporting System.';
          $content = $content . '
            <div style="border:.4em solid orange;margin:0 0 1em 0;padding:1em;text-align:center;">
              ' . $prompt . '
            </div>';
          return $content;
            // DRS
        }
          // RETURN: Current Powermail form isn't the one after confirmation
          // Follow the workflow: update the SOAP user data
          // DRS
        if( $this->b_drs_update )
        {
          $prompt = 'Powermail parameter sendNow is 1: SOAP user data will updated.';
          t3lib_div::devlog(' [INFO/UPDATE] '. $prompt, $this->extKey, 0 );
        }
        $content = $content . '
          <div style="border:.4em solid green;margin:0 0 1em 0;padding:1em;text-align:center;">
            ' . $prompt . '
          </div>';
        break;
          // DRS
          // CASE: with a powermail confirmation page
    }


      // DRS
    if( $this->b_drs_update )
    {
      $prompt = 'If you have expected another workflow, please change the value of powermail.confirm
                in the SOAP user flexform.';
      t3lib_div::devlog(' [INFO/UPDATE] '. $prompt, $this->extKey, 2 );
    }

    $prompt = 'If you have expected another workflow, please change the value of powermail.confirm
              in the SOAP user flexform. Or take a look into the DRS - Development Reporting System.';
    $content = $content . '
      <div style="border:.4em solid orange;margin:0 0 1em 0;padding:1em;text-align:center;">
        ' . $prompt . '
      </div>';
    $content = $content . '
      <div style="border:.4em solid red;margin:0 0 1em 0;padding:1em;text-align:center;">
        Update data on the SOAP server isn\'t coded!
      </div>';
    return $content;
      // DRS
  }



}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/soapuser/pi1/class.tx_soapuser_pi1.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/soapuser/pi1/class.tx_soapuser_pi1.php']);
}

?>
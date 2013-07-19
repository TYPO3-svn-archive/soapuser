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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   91: class tx_soapuser_sv1 extends tx_sv_authbase
 *
 *              SECTION: Main
 *  192:     public function getUser( )
 *
 *              SECTION: Initials
 *  293:     public function init( )
 *  321:     private function initAccessByIP( )
 *  354:     public function initAuth( $mode, $loginData, $authInfo, $pObj )
 *  397:     private function initSoapMode( $prompt )
 *
 *              SECTION: DRS - Development Reporting System
 *  504:     private function initDRS( )
 *
 *              SECTION: Requirements
 *  542:     private function required( )
 *  582:     private function requiredClientNo( )
 *  631:     private function requiredPassword( )
 *  684:     private function requiredStatus( )
 *  716:     private function requiredUser( $feuser_record )
 *
 *              SECTION: Group mapping
 *  803:     private function externalGroupsToTYPO3Groups( $externalGroups )
 *
 *              SECTION: fe_users
 *  893:     private function feuserCheckPid( )
 *  962:     private function feuserGetGroupForRemove( )
 *  984:     private function feuserGetLastLoginDeprecated( )
 * 1016:     private function feuserSetEndtime( )
 * 1048:     private function feuserSetUsergroup( )
 * 1080:     private function feuserSetUsergroupBySoapuser( )
 * 1100:     private function feuserSetUsergroupByTestuser( )
 * 1135:     private function feuserSqlDelete( )
 * 1172:     private function feuserSqlDeleteAllAfter( )
 * 1229:     private function feuserSqlInsert( )
 * 1369:     private function feuserSqlSelectByUsername( )
 *
 *              SECTION: SOAP
 * 1437:     private function soap( )
 * 1470:     private function soapAuthenticate( )
 * 1524:     private function soapAuthenticateTestuser( )
 * 1629:     private function soapNewClient( )
 * 1681:     private function soapErrorAddTestPrompt( )
 * 1707:     private function soapError( $method, $line, $fault )
 *
 * TOTAL FUNCTIONS: 29
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
 
require_once( PATH_t3lib . 'class.t3lib_svbase.php' );

/**
 * Service "Authorise an external user" for the "soapuser" extension.
 *
 * @author      Dirk Wildt <wildt@die-netzmacher.de>
 * @package     TYPO3
 * @subpackage  soapuser
 * @version     0.1.5
 * @since       0.0.3
 */
class tx_soapuser_sv1 extends tx_sv_authbase
{
 /**
  * Class name
  *
  * @var string
  */
  protected $prefixId  = 'tx_soapuser_sv1';

 /**
  * Path to this script relative to the extension directory
  *
  * @var string
  */
  private $scriptRelPath  = 'sv1/class.tx_soapuser_sv1.php';

 /**
  * Extension key
  *
  * @var string
  */
  private $extKey = 'soapuser';

 /**
  * Configuration of the extension manager
  *
  * @var array
  */
  private $arr_extConf = null;

 /**
  * Current IP is met allowed IPs
  *
  * @var boolean
  */
  private $bool_accessByIP;

 /**
  * SOAP client
  *
  * @var SoapClient
  */
  private $soapclient = null;

 /**
  * External SOAP groups of the current user
  *
  * @var array
  */
  private $soapGroups = null;

 /**
  * SOAP test mode: if SOAP test mode is enabled, $soaptest becomes true.
  * Result depends on $$bool_accessByIP
  *
  * @var boolean
  */
  private $soaptest = false;

 /**
  * External groups of the testuser
  *
  * @var array
  */
  private $testuserExternalGroups = null;

  



  /***********************************************
   *
   * Main
   *
   **********************************************/

    
    
//function authUser($user)
//{
//    $this->login['uname']   = $user['username'];
//    $this->login['uident']   = $user['password'] ;
//    $this->login['uident_text']   = $user['password'] ;
////var_dump( __METHOD__, __LINE__ , $user, $this->login );
//
//    // return values:
//   // 200 - authenticated and no more checking needed - useful for IP checking without password
//   // 100 - Just go on. User is not authenticated but there's still no reason to stop.
//   // false - this service was the right one to authenticate the user but it failed
//   // true - this service was able to authenticate the user
//   $OK = 100;
//   if( $this->login['uident'] && $this->login['uname'] && $user != false )
//   {
//      $OK = $this->compareUident($user, $this->login); // true if login-data matched
//   }
//    if( $this->writeDevLog )
//    {
//        $prompt = 'authUser returns ' . $OK;
//        t3lib_div::devlog( '[WARNING/SERVICES] ' . $prompt, $this->extKey, 3 );
//    }
//   return $OK;
//}


 /**
  * getUser( ): Authorise the user. Process is successful if
  *             * username
  *             * password and
  *             * optional client no
  *             will match with data in the SOAP database.
  *
  *             In case of success username and optional client no
  *             will stored in the session ('ses' not 'user', because user isn't
  *             logged in now.
  *
  *             If data won't match, TYPO3 authorise class will try to
  *             fetch data from fe_users table in the work-flow later.
  *
  * @return    void
  * @version  0.1.0
  * @since    0.0.4
  */
  public function getUser( )
  {
      // Delete deprecated users
    $this->feuserSqlDeleteAllAfter( );
    
      // Delete current user
    $this->feuserSqlDelete( );
    
      // Array for current fe_user record
    $feuser_record  = false;

      // RETURN: requirements failed
    if( ! $this->required( ) )
    {
      return false;
    }
      // RETURN: requirements failed

      // RETURN: no access by the SOAP server
    if( ! $this->soap(  ) )
    {
      return false;
    }
      // RETURN: no access by the SOAP server

    if( ! $this->feuserSqlInsert( ) )
    {
      return false;
    }

      // Session data - step 1 from 3
    $logintype = t3lib_div::_POST( 'logintype' );
    switch( true )
    {
      case( $logintype == 'login' ):
          // Empty session
        $sessData = array( );
          // Get username from the loginform
        $sessData['username']           = $this->loginData['uname'];
        $sessData['soapuser_clientno']  = t3lib_div::_GP( 'soapuser_clientno' );
        break;
      default:
        $sessData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
        break;
    }
      // Session data - step 1 from 3
    
      // Get fe_user record from fe_users table
    switch( true )
    {
      case( $GLOBALS['TSFE']->fe_user->user['uid'] ):
        $feuser_record = $this->feuserSqlSelectByUid( );
        if( $this->writeDevLog )
        {
          $prompt = 'User data by SQL query with uid.';
          t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
        }
        break;
      case( $this->loginData['uname'] ):
        $feuser_record = $this->feuserSqlSelectByUsername( );
        if( $this->writeDevLog )
        {
          $prompt = 'User data by SQL query with username.';
          t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
        }
        break;
      default:
        $feuser_record = $this->fetchUserRecord( $this->loginData['uname'] );
          // 120701, dwildt, 1+
        $this->feuserRecord = $feuser_record;
        if( $this->writeDevLog )
        {
          $prompt = 'User data by fetchUserRecord method with username from loginform';
          t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
        }
        break;
    }
//var_dump( __METHOD__, __LINE__, $this->loginData['uname'], $sessData['username'], $GLOBALS['TSFE']->fe_user->user['uid'], $feuser_record );

      // RETURN: fe_user can't fetched
    if( ! $this->requiredUser( $feuser_record ) )
    {
      return false;
    }
      // RETURN: fe_user can't fetched
      
      // Session data - step 2 from 3
      // Add fe_users data
    if( empty ( $this->feuserRecord ) )
    {
        if( $this->writeDevLog )
        {
        $prompt = 'Global feuserRecord is empty! Any session data will written.';
        t3lib_div::devlog( '[ERROR/SERVICES] ' . $prompt, $this->extKey, 3 );
        }
    }
    foreach( ( array ) $this->feuserRecord as $key => $value )
    {
      switch( $key )
      {
        case( 'uc' ):
          continue 2;
          break;
        case( 'username' ):
          $sessData['username'] = $value;
          break;
      }
      $sessData[ 'fe_users.' . $key ] = $value;
    }
      // Add fe_users data
      // Session data - step 2 from 3
      
      // Session data - step 3 from 3
    $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
    $GLOBALS["TSFE"]->storeSessionData();

      // DRS
    if( $this->b_drs_session )
    {
      $sessData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
      $prompt   = 'soapuser: ' .  t3lib_div::arrayToLogString
                                  (
                                    $sessData,
                                    array( ),
                                    100
                                  );
      t3lib_div::devlog( '[OK/SESSION] ' . $prompt, $this->extKey, -1 );
    }
      // DRS
      
      // DRS
    if( $this->writeDevLog )
    {
      $prompt = 'User \'' . $sessData['username'] . '\' found.';
      t3lib_div::devlog( '[OK/SERVICES] ' . $prompt, $this->extKey, -1 );
      $prompt = 'fe_user record: ' .  t3lib_div::arrayToLogString
                                  (
                                    $feuser_record,
                                    array
                                    (
                                      $this->db_user['userid_column'],
                                      $this->db_user['username_column']
                                    )
                                  );
      t3lib_div::devlog( '[OK/SERVICES] ' . $prompt, $this->extKey, -1 );
    }
      // DRS
    
      // RETURN user record
    return $feuser_record;
  }









  /***********************************************
   *
   * Initials
   *
   **********************************************/



 /**
  * init( ): Check, if parent is available (formal). Initiate the class var $arr_extConf
  *
  * @return    [boolean]        true: success, false: failed
  * @version  0.0.4
  * @since    0.0.3
  */
  public function init( )
  {
      // Check parent
    $available = parent::init();

      // RETURN: no parent
    if( ! $available )
    {
      return false;
    }
      // RETURN: no parent

      // Set class var $arr_extConf, the extmanager configuration
    $this->arr_extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);

      // RETURN success
    return true;
  }



  /**
 * initAccessByIP( ):  Set the global $bool_accessByIP.
 *
 * @return    void
 * @version 0.1.0
 * @since   0.1.0
 */
  private function initAccessByIP( )
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
      // Current IP is an element in the list
  }



 /**
  * initAuth( ):  Initialize authentication service. It is extended only for initiate
  *               the DRS. If DRS is starting, global vars $writeAttemptLog and $writeDevLog
  *               will set to true.
  *
  * @param    string        $mode:      Subtype of the service which is used to call the service.
  * @param    array        $loginData: Submitted login form data: username and password only.
  * @param    array        $authInfo:  Information array. Holds submitted form data etc.
  * @param    object        $pObj:      Parent object
  * @return    void
  * @version  0.0.4
  * @since    0.0.3
  */
  public function initAuth( $mode, $loginData, $authInfo, $pObj )
  {

    $this->pObj = $pObj;

      // dwildt, 120426, dev, 1+
    $this->initDRS();

      // Debugging flags
    $this->writeAttemptLog  = $this->pObj->writeAttemptLog;
    $this->writeDevLog      = $this->pObj->writeDevLog;

    $this->initAccessByIP( );
    $this->initSoapMode( );
    $this->initSoapAdmin( );

      // Set class vars
    $this->mode       = $mode;
    $this->loginData  = $loginData;
    $this->authInfo   = $authInfo;

    if ( $this->writeDevLog )
    {
      $prompt = 'Login data: ' . t3lib_div::arrayToLogString( $this->loginData );
      t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
    }

      // Data structure of fe_users table
    $this->db_user    = $this->getServiceOption('db_user', $authInfo['db_user'], FALSE);
      // Data structure of fe_group table
    $this->db_groups  = $this->getServiceOption('db_groups', $authInfo['db_groups'], FALSE);

  }



  /**
 * initSoapAdmin(  ):  ...
 *
 * @return    string        message wrapped in HTML
 * @version 0.1.3
 * @since   0.1.3
 */
  private function initSoapAdmin( )
  {
      // Init admin client3 number
    $this->adminSoapClientno = $this->arr_extConf['adminSoapClientno'];
      // RETURN: SOAP mode is disabeld
    if( empty ( $this->adminSoapClientno ) )
    {
      if ( $this->writeDevLog )
      {
        $prompt = 'Admin for the SOAP server hasn\'t any client number.';
        t3lib_div::devlog( '[ERROR/SOAP] ' . $prompt, $this->extKey, 3 );
      }
      return false;
    }
      // RETURN: SOAP mode is disabeld
      // Init admin client number

      // Init admin login name
    $this->adminSoapLogin = $this->arr_extConf['adminSoapLogin'];
      // RETURN: SOAP mode is disabeld
    if( empty ( $this->adminSoapLogin ) )
    {
      if ( $this->writeDevLog )
      {
        $prompt = 'Admin for the SOAP server hasn\'t any login name.';
        t3lib_div::devlog( '[ERROR/SOAP] ' . $prompt, $this->extKey, 3 );
      }
      return false;
    }
      // RETURN: SOAP mode is disabeld
      // Init admin login name

      // Init admin password
    $this->adminSoapPassword = $this->arr_extConf['adminSoapPassword'];
      // RETURN: SOAP mode is disabeld
    if( empty ( $this->adminSoapPassword ) )
    {
      if ( $this->writeDevLog )
      {
        $prompt = 'Admin for the SOAP server hasn\'t any password.';
        t3lib_div::devlog( '[ERROR/SOAP] ' . $prompt, $this->extKey, 3 );
      }
      return false;
    }
      // RETURN: SOAP mode is disabeld
      // Init admin login name

      // DRS
    if ( $this->writeDevLog )
    {
      $prompt = 'Admin for the SOAP server is initialised.';
      t3lib_div::devlog( '[OK/SOAP] ' . $prompt, $this->extKey, -1 );
    }
      // DRS

    return true;
  }



  /**
 * initSoapMode(  ):  ...
 *
 * @return    string        message wrapped in HTML
 * @version 0.1.0
 * @since   0.0.1
 */
  private function initSoapMode( )
  {
    $this->soaptest = false;

      // RETURN: SOAP mode is disabeld
    if( $this->arr_extConf['soapMode'] == 'Disabled' )
    {
      if ( $this->writeDevLog )
      {
        $prompt = 'SOAP test mode is disabled.';
        t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
      }
      return;
    }
      // RETURN: SOAP mode is disabeld

      // DRS
    if ( $this->writeDevLog )
    {
      $prompt = 'SOAP test mode is enabled.';
      t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
    }
      // DRS

      // RETURN: IP isn't allowed
    if( ! $this->bool_accessByIP )
    {
        // DRS
      if ( $this->writeDevLog )
      {
        $prompt = 'Current IP doesn\'t match the list of allowed IPs. SOAP test mode is disabled!';
        t3lib_div::devlog( '[WARN/SERVICES] ' . $prompt, $this->extKey, 3 );
      }
        // DRS
      return;
    }
      // RETURN: IP isn't allowed

      // SOAP test mode is enabled
    $this->soaptest = true;

      // DRS
    if ( $this->writeDevLog )
    {
      $prompt = 'Current IP matchs the list of allowed IPs';
      t3lib_div::devlog( '[OK/SERVICES] ' . $prompt, $this->extKey, -1 );
      $prompt = 'An enabled SOAP test mode is a SECURITY RISK!';
      t3lib_div::devlog( '[WARN/SERVICES] ' . $prompt, $this->extKey, 3 );
    }
      // DRS

    $csvIP = $this->arr_extConf['allowedIPs'];
    $status_message = '
      <h1>
        SECURITY WARNING
      </h1>
      <h2>
        SOAP test mode is enabled by the extension manager
      </h2>
      <p>
        This is  a security risk!
      </p>
      <ul>
        <li>
          Every client with this IP(s) ' . $csvIP . ' can login with the access data of the testuser.
        </li>
      </ul>
      <h2>
        What can you do?
      </h2>
      <ul>
        <li>
          Please disable the SOAP test mode after Debugging.<br />
          The SOAP test mode is managed by the TYPO3 extension soapuser.
        </li>
      </ul> ';

      // Set session data: status message
    $sessData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
    $sessData['soapuser_soaptest_message'] = $status_message;
    $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
      // Set session data: status message
  }









  /***********************************************
   *
   * DRS - Development Reporting System
   *
   **********************************************/



 /**
  * initDRS( ): Set the booleans for Warnings, Errors and DRS - Development Reporting System
  *
  * @return    void
  * @version 0.0.3
  * @since 0.0.3
  */
  private function initDRS( )
  {
    switch( $this->arr_extConf['drs_mode'] )
    {
      case( 'All' ):
      case( 'Services' ):
        $this->pObj->writeAttemptLog  = true;
        $this->pObj->writeDevLog      = true;
        $this->b_drs_sql              = true;
        $this->b_drs_session          = true;
        $this->b_drs_soap             = true;
        t3lib_div::devlog( '[OK/DRS] DRS is enabled: ' . $this->arr_extConf['drs_mode'], $this->extKey, -1 );
        break;
      default:
          // do nothing;
        break;
    }
  }








  /***********************************************
   *
   * Requirements
   *
   **********************************************/



 /**
  * required( ): Checks wether required status, password and client number is met
  *
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.0
  * @since    0.0.5
  */
  private function required( )
  {
    switch( true )
    {
      case( ! $this->requiredStatus( ) ):
      case( ! $this->requiredPassword( ) ):
      case( ! $this->requiredClientNo( ) ):
//      case( ! $this->requiredUser( $feuser_record ) ):
          // DRS prompt
        if ( $this->writeDevLog )
        {
          $prompt = 'Required status, password or client number is empty or wrong.';
          t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 2 );
        }
          // DRS prompt
//        $status_message = '
//          <h1>ERROR</h1>
//          <p>
//            Sorry, required status, password or client number is empty or wrong.
//          </p>';
//        $sessData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
//        $sessData['soapuser_status_message'] = $status_message;
//        $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
        return false;
        break;
      default:
        return true;
        break;
    }
  }



 /**
  * requiredClientNo( ): Checks wether client no is empty or not
  *
  * @return    [boolean]        true: password is empty; false: password isn't empty
  * @version  0.0.5
  * @since    0.0.5
  */
  private function requiredClientNo( )
  {
      // SWITCH : is param set
    switch( true )
    {
      case( isset ( $_POST['soapuser_clientno'] ) ):
          // follow the workflow
        break;
      case( isset ( $_GET['soapuser_clientno'] ) ):
          // follow the workflow
        break;
      default:
          // Requirement isn't checked: no parameter
        if ( $this->writeDevLog )
        {
          $prompt = 'The login form hasn\'t any client number field.';
          t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
        }
        return true;
          // Requirement isn't checked: no parameter
    }
      // SWITCH : is param set

      // RETURN false : client number is empty
    $clientNo = t3lib_div::_GP( 'soapuser_clientno' );
    if( empty ( $clientNo ) )
    {
      if ( $this->writeDevLog )
      {
        $prompt = 'Client number is empty.';
        t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 2 );
      }
      return false;
    }
      // RETURN false : client number is empty

      // RETURN true : client number is set
    return true;
  }



 /**
  * requiredPassword( ): Checks wether password is empty or not
  *
  * @return    [boolean]        true: password is empty; false: password isn't empty
  * @version  0.0.5
  * @since    0.0.4
  */
  private function requiredPassword( )
  {

      // RETURN true  : password is set
    if( $this->loginData['uident'] )
    {
//      if ( $this->writeDevLog )
//      {
//        $prompt = 'Abort: password is empty.';
//        t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
//      }
      return true;
    }
      // RETURN true  : password is set



      //////////////////////////////////////////////
      //
      // Failed login attempt (no password given)

      // DRS
    $prompt = sprintf(
                'Login-attempt from %s (%s), for username \'%s\' with an empty password!',
                $this->authInfo['REMOTE_ADDR'],
                $this->authInfo['REMOTE_HOST'],
                $this->loginData['uname']
              );

    if( $this->writeDevLog )
    {
      t3lib_div::devlog( '[INFO/SERVICE] ' . $prompt, $this->extKey, 2 );
    }
    $this->writelog(255, 3, 3, 2,
      'Login-attempt from %s (%s) for username \'%s\' with an empty password!',
      array($this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $this->loginData['uname'])
    );
    t3lib_div::sysLog( $prompt, 'Core', 0 );
      // DRS
      // Failed login attempt (no password given)

    return false;
  }



 /**
  * requiredStatus( ): Checks wether password is empty or not
  *
  * @return    [boolean]        true: password is empty; false: password isn't empty
  * @version  0.0.5
  * @since    0.0.4
  */
  private function requiredStatus( )
  {

      // RETURN true : Status is login
    if( $this->loginData['status'] == 'login' )
    {
      return true;
    }
      // RETURN true : Status is login

      // RETURN false : Status isn't login
      // DRS
    if ( $this->writeDevLog )
    {
      $prompt = 'Abort: status isn\'t login.';
      t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 2 );
    }
      // DRS
    return false;
      // RETURN false : Status isn't login
  }



 /**
  * requiredUser( ): Checks wether password is empty or not
  *
  * @param    [type]        $feuser_record: ...
  * @return    [boolean]        true: password is empty; false: password isn't empty
  * @version  0.0.5
  * @since    0.0.4
  */
  private function requiredUser( $feuser_record )
  {
      // RETURN true  : user is fetched
    if( is_array( $feuser_record ) )
    {
      if ( $this->writeDevLog )
      {
        $prompt = 'User found: ' .  t3lib_div::arrayToLogString
                                    (
                                      $feuser_record,
                                      array
                                      (
                                        $this->db_user['userid_column'],
                                        $this->db_user['username_column']
                                      )
                                    );
        t3lib_div::devlog( '[OK/SERVICES] ' . $prompt, $this->extKey, -1 );
      }
      return true;
    }
      // RETURN true  : user is fetched



      //////////////////////////////////////////////
      //
      // Failed login attempt (username not found)

      // DRS
    $prompt = sprintf(
                'Login-attempt from %s (%s), username \'%s\' not found!',
                $this->authInfo['REMOTE_ADDR'],
                $this->authInfo['REMOTE_HOST'],
                $this->loginData['uname']
              );

    if( $this->writeDevLog )
    {
      t3lib_div::devlog( '[INFO/SERVICE] ' . $prompt, $this->extKey, 3 );
    }
    $this->writelog(255, 3, 3, 2,
      'Login-attempt from %s (%s), username \'%s\' not found!',
      $this->authInfo['REMOTE_ADDR'],
      $this->authInfo['REMOTE_HOST'],
      $this->loginData['uname']
    );
    t3lib_div::sysLog( $prompt, 'Core', 0 );
      // DRS
      // Failed login attempt (username not found)

    return false;
  }









  /***********************************************
   *
   * External group mapping
   *
   **********************************************/



 /**
  * externalGroupsToTYPO3Groups( ):
  *
  * @param    array        $externalGroups: External user groups mapping to fe_usergroup
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.1
  * @since    0.1.0
  */
  private function externalGroupsToTYPO3Groups( $externalGroups )
  {
    $arrTYPO3Groups = array( );
    $csvTYPO3Groups = null;

      // RETURN null: External groups are empty
    if( empty( $externalGroups ) )
    {
        // DRS prompt
      if ( $this->writeDevLog )
      {
        $prompt = 'User isn\'t attributed to any external user group.';
        t3lib_div::devlog( '[WARN/SERVICES] ' . $prompt, $this->extKey, 3 );
      }
        // DRS prompt
      return null;
    }
      // RETURN null: External groups are empty

      // Pid of fe_users
    $pid                = ( int ) $this->arr_extConf['fe_usersPid'];
      // andWhere
    $andWhereExternalGroups  = implode( "' OR externalgroup LIKE '", $externalGroups );
    $andWhereExternalGroups  = " AND (externalgroup LIKE '" . $andWhereExternalGroups . "')";

      // Query
    $select_fields  = 'fe_groups';
    $from_table     = 'tx_soapuser_groups';
    $where_clause   = "pid = " . $pid . $andWhereExternalGroups;
    $groupBy        = '';
    $orderBy        = '';
    $limit          = '';
      // Query

      // DRS prompt
    if ( $this->writeDevLog )
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
      t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
    }
      // DRS prompt

      // SELECT
    $res =  $GLOBALS['TYPO3_DB']->exec_SELECTquery
            (
              $select_fields,
              $from_table,
              $where_clause,
              $groupBy,
              $orderBy,
              $limit
            );
      // SELECT

      // Get array with TYPO3Groups
    while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $res ) )
    {
      $arrTYPO3Groups[ ] = $row['fe_groups'];
    }
    $arrTYPO3Groups = array_unique( ( array ) $arrTYPO3Groups );
      // Get array with TYPO3Groups

      // Implode array to CSV string
    $csvTYPO3Groups = implode( ',', $arrTYPO3Groups );

      // RETURN TYPO3Groups
    return $csvTYPO3Groups;
  }









  /***********************************************
   *
   * fe_users
   *
   **********************************************/



 /**
  * feuserCheckPid( ):
  *
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.0
  * @since    0.0.5
  */
  private function feuserCheckPid( )
  {
    $select_fields  = '*';
    $from_table     = 'pages';
    $where_clause   = 'uid = ' . ( int ) $this->arr_extConf['fe_usersPid'];
    $groupBy        = '';
    $orderBy        = '';
    $limit          = '';

//    $query  = $GLOBALS['TYPO3_DB']->SELECTquery
//              (
//                $select_fields,
//                $from_table,
//                $where_clause,
//                $groupBy,
//                $orderBy,
//                $limit
//              )
//    var_dump( __METHOD__, __LINE__, $query );
    $res =  $GLOBALS['TYPO3_DB']->exec_SELECTquery
            (
              $select_fields,
              $from_table,
              $where_clause,
              $groupBy,
              $orderBy,
              $limit
            );
    $row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $res );

      // RETURN: no row
    if( empty( $row ) )
    {
        // DRS prompt
      if ( $this->writeDevLog )
      {
        $prompt = 'Unproper pid for fe_users: ' . $this->arr_extConf['fe_usersPid'];
        t3lib_div::devlog( '[ERROR/SERVICES] ' . $prompt, $this->extKey, 3 );
      }
        // DRS prompt

        // Set session data: status message
      $sessData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
      $status_message = $sessData['soapuser_status_message'];
      $status_message = $status_message . '
        <h1>TYPO3 CONFIGURATION ERROR</h1>
        <p>
          Directory for fe_users doesn\'t exist. Unproper pid is ' . $this->arr_extConf['fe_usersPid'] . '
        </p>';
      $sessData['soapuser_status_message'] = $status_message;
      $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
        // Set session data: status message

      return false;
    }
      // RETURN: no row

    return true;
  }



 /**
  * feuserGetGroupForRemove( ):
  *
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserGetGroupForRemove( )
  {
    $usergroup = null;

    $fe_usersRemoveGroup = ( int ) $this->arr_extConf['fe_usersRemoveGroup'];
    if( $fe_usersRemoveGroup > 0 )
    {
      $usergroup = $fe_usersRemoveGroup;
    }

    return $usergroup;
  }



 /**
  * feuserGetLastLoginDeprecated( ):
  *
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserGetLastLoginDeprecated( )
  {
    switch( $this->arr_extConf['fe_usersRemoveAfter'] )
    {
      case( '30 minutes' ):
        return time( ) - 30 * 60;
        break;
      case( '1 hour' ):
        return time( ) - 1 * 60 * 60;
        break;
      case( '12 hours' ):
        return time( ) - 12 * 60 * 60;
        break;
      case( '24 hours' ):
        return time( ) - 24 * 60 * 60;
        break;
      case( '2 hours (recommended)' ):
      default:
        return time( ) - 2 * 60 * 60;
        break;
    }
  }



 /**
  * feuserSetEndtime( ):
  *
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserSetEndtime( )
  {
    switch( $this->arr_extConf['fe_usersEndtime'] )
    {
      case( '30 minutes' ):
        return time( ) + 30 * 60;
        break;
      case( '2 hours' ):
        return time( ) + 2 * 60 * 60;
        break;
      case( '12 hours' ):
        return time( ) + 12 * 60 * 60;
        break;
      case( '24 hours' ):
        return time( ) + 24 * 60 * 60;
        break;
      case( '1 hour (recommended)' ):
      default:
        return time( ) + 1 * 60 * 60;
        break;
    }
  }



 /**
  * feuserSetUsergroup( ):
  *
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserSetUsergroup( )
  {
    $csvTYPO3Groups = null;

    switch( $this->soaptest )
    {
      case( true ):
        $csvTYPO3Groups = $this->feuserSetUsergroupByTestuser( );
        break;
      case( false ):
      default:
        $csvTYPO3Groups = $this->feuserSetUsergroupBySoapuser( );
        break;
    }

    if( empty ( $csvTYPO3Groups ) )
    {
      // Login abbrechen!
    }

    return $csvTYPO3Groups;
  }



 /**
  * feuserSetUsergroupBySoapuser( ):
  *
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.1
  * @since    0.1.1
  */
  private function feuserSetUsergroupBySoapuser( )
  {
    $csvTYPO3Groups = null;

      // Convert External groups to TYPO3 groups
    $csvTYPO3Groups = $this->externalGroupsToTYPO3Groups( $this->soapGroups );

      // RETURN: no TYPO3 group
    if( empty ( $csvTYPO3Groups ) )
    {
      return null;
    }
      // RETURN: no TYPO3 group

      // Append the TYPO3 group for automatic clean up
    $fe_usersRemoveGroup = $this->feuserGetGroupForRemove( );
    if( $fe_usersRemoveGroup )
    {
      $csvTYPO3Groups = $csvTYPO3Groups . ',' . $fe_usersRemoveGroup;
    }
      // Append the TYPO3 group for automatic clean up

      // RETURN TYPO3 groups as CSV string
    return $csvTYPO3Groups;

    return null;
  }



 /**
  * feuserSetUsergroupByTestuser( ):
  *
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserSetUsergroupByTestuser( )
  {
    $csvTYPO3Groups    = null;

      // Convert external groups to TYPO3 groups
    $csvTYPO3Groups = $this->externalGroupsToTYPO3Groups( $this->testuserSoapGroups );

      // RETURN: no TYPO3 group
    if( empty ( $csvTYPO3Groups ) )
    {
      return null;
    }
      // RETURN: no TYPO3 group

      // Append the TYPO3 group for automatic clean up
    $fe_usersRemoveGroup = $this->feuserGetGroupForRemove( );
    if( $fe_usersRemoveGroup )
    {
      $csvTYPO3Groups = $csvTYPO3Groups . ',' . $fe_usersRemoveGroup;
    }
      // Append the TYPO3 group for automatic clean up

      // RETURN TYPO3 groups as CSV string
    return $csvTYPO3Groups;
  }



 /**
  * feuserSqlDelete( ):
  *
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserSqlDelete( )
  {
      // RETURN: unproper fe_users pid
    if( ! $this->feuserCheckPid( ) )
    {
      return false;
    }
      // RETURN: unproper fe_users pid

      // Pid of fe_users
    $pid      = ( int ) $this->arr_extConf['fe_usersPid'];
      // Take username
    $username = $this->loginData['uname'];

    $where = "pid = " . $pid . " AND username = '" . $username . "'";

      // DRS prompt
    if ( $this->writeDevLog )
    {
      $query  = $GLOBALS['TYPO3_DB']->DELETEquery( 'fe_users', $where ) ;
      $prompt = $query;
      t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
    }
      // DRS prompt

    $GLOBALS['TYPO3_DB']->exec_DELETEquery( 'fe_users', $where ) ;
  }



 /**
  * feuserSqlDeleteAllAfter( ):
  *
  * @return    boolean        true: password is empty; false: password isn't empty
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserSqlDeleteAllAfter( )
  {
      // RETURN: unproper fe_users pid
    if( ! $this->feuserCheckPid( ) )
    {
      return false;
    }
      // RETURN: unproper fe_users pid

      // get the group for clean up
    $fe_usersRemoveGroup = $this->feuserGetGroupForRemove( );

      // RETURN: there isn't any group for clean up
    if( ! $fe_usersRemoveGroup )
    {
        // DRS prompt
      if ( $this->writeDevLog )
      {
        $prompt = 'Deprecated users aren\'t removed, because there isn\'t any id for the clean up usergroup!';
        t3lib_div::devlog( '[WARN/SERVICES] ' . $prompt, $this->extKey, 3 );
      }
        // DRS prompt
      return false;
    }
      // RETURN: there isn't any group for clean up

      // Pid of fe_users
    $pid        = ( int ) $this->arr_extConf['fe_usersPid'];
      // Last login has to be newer than now minus the removeAllAfter value
    $lastloginDeprecated  = $this->feuserGetLastLoginDeprecated( );

    $where =  "pid = " . $pid .
              " AND lastlogin < " . $lastloginDeprecated .
              " AND FIND_IN_SET( '" . $fe_usersRemoveGroup . "', usergroup )";

      // DRS prompt
    if ( $this->writeDevLog )
    {
      $query  = $GLOBALS['TYPO3_DB']->DELETEquery( 'fe_users', $where ) ;
      $prompt = $query;
      t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
    }
      // DRS prompt

      // DELETE deprecated user records
    $GLOBALS['TYPO3_DB']->exec_DELETEquery( 'fe_users', $where ) ;
  }



 /**
  * feuserSqlInsert( ):
  *
  * @return    boolean        true:
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserSqlInsert( )
  {
      // RETURN: unproper fe_users pid
    if( ! $this->feuserCheckPid( ) )
    {
      return false;
    }
      // RETURN: unproper fe_users pid

    $usergroup = $this->feuserSetUsergroup( );

    if( empty ( $usergroup ) )
    {
        // DRS prompt
      if ( $this->writeDevLog )
      {
        $prompt = 'Any TYPO3 usergroup isn\'t attributed to the user.';
        t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
      }
        // DRS prompt
        // Set session data: status message
      $sessData       = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
      $status_message = $sessData['soapuser_status_message'];
      $status_message = $status_message . '
                        <h1>
                          ACCESS ERROR
                        </h1>
                        <p>
                          Any TYPO3 usergroup isn\'t attributed to the user.
                        </p>
                        <ul>
                          <li>
                            The user won\'t have any access without any usergroup.
                          </li>
                          <li>
                            User won\'t authenticated by TYPO3 extension soapuser.
                          </li>
                        </ul>
                        <h2>
                          What can you do?
                        </h2>
                        <ul>
                          <li>
                            Please report this problem to the external group administrator.
                          </li>
                        </ul>';
      $sessData['soapuser_status_message'] = $status_message;
      $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
      return false;
    }

      // Pid of fe_users
    $pid      = ( int ) $this->arr_extConf['fe_usersPid'];
      // timestamp
    $currTime = time( );
      // endtime
    $endtime  = $this->feuserSetEndtime( );
      // Get the user object (user data) from the SOAP server
    $user = $this->soapGetUser( );

//      // Take username and password from the login form
//    $username = $this->loginData['uname'];
    $password = $this->loginData['uident'];

      // Values without a correspondending fe_users field
    $user->newLogin;
    $user->password;        // is empty!
    $user->oldPassword;

    $user->salutation;
    $user->stateiso;
    $user->title;

    $user->homePhone;
    $user->mobile;
      // Values without a correspondending fe_users field

      // Set the fe_users record
    $fields_values = array
    (
      'pid'           => $pid,
      'tstamp'        => $currTime,
      'crdate'        => $currTime,
      'cruser_id'     => '',
      'deleted'       => '0',
      'disable'       => '0',
      'starttime'     => '',
      'endtime'       => $endtime,
      'username'      => $user->login,
      'password'      => $password,
      'usergroup'     => $usergroup,
      'name'          => '',
      'first_name'    => $user->firstName,
      'middle_name'   => '',
      'last_name'     => $user->lastName,
      'address'       => $user->address,
      'zip'           => $user->zip,
      'city'          => $user->city,
      'country'       => $user->country,
      'www'           => '',
      'company'       => $user->company,
      'telephone'     => $user->phone,
      'fax'           => $user->fax,
      'email'         => $user->email,
      'fe_cruser_id'  => '0',
      'lastlogin'     => $currTime,
      'is_online'     => $currTime,
    );
      // Set the fe_users record

      // DRS prompt
    if ( $this->writeDevLog )
    {
      $query  = $GLOBALS['TYPO3_DB']->INSERTquery( 'fe_users', $fields_values, false );
      $prompt = $query;
      $prompt = str_replace( ',', ', ', $prompt);
      t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
    }
      // DRS prompt

    $GLOBALS['TYPO3_DB']->exec_INSERTquery( 'fe_users', $fields_values, false );
    if( ! $this->feuserSqlSelectByUsername( ) )
    {
        // DRS prompt
      if ( $this->writeDevLog )
      {
        $prompt = 'User can\'t inserted!';
        t3lib_div::devlog( '[ERROR/SERVICES] ' . $prompt, $this->extKey, 3 );
      }
        // DRS prompt

        // Set session data: status message
      $sessData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
      $status_message = $sessData['soapuser_status_message'];
      $status_message = $status_message . '
        <h1>SQL ERROR</h1>
        <p>
          User can\'t inserted!
        </p>';
      $sessData['soapuser_status_message'] = $status_message;
      $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
        // Set session data: status message

      return false;
    }

    return true;
  }



 /**
  * feuserSqlSelectByUid( ):
  *
  * @return    boolean        true:
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserSqlSelectByUid( )
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
    $where_clause   = "pid = " . $pid . " AND uid = " . $uid . "' AND disable = 0 AND deleted = 0";
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

        // 120701, dwildt, 1-
//    return true;
        // 120701, dwildt, 1+
    return $this->feuserRecord;
  }



 /**
  * feuserSqlSelectByUsername( ):
  *
  * @return    array        
  * @version  0.1.0
  * @since    0.1.0
  */
  private function feuserSqlSelectByUsername( )
  {
      // Pid of fe_users
    $pid      = ( int ) $this->arr_extConf['fe_usersPid'];
      // Take username from the login form
    $username = $this->loginData['uname'];

    $select_fields  = '*';
    $from_table     = 'fe_users';
    $where_clause   = "pid = " . $pid . " AND username = '" . $username . "' AND disable = 0 AND deleted = 0";
    $groupBy        = '';
    $orderBy        = '';
    $limit          = '';

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
    $res =  $GLOBALS['TYPO3_DB']->exec_SELECTquery
            (
              $select_fields,
              $from_table,
              $where_clause,
              $groupBy,
              $orderBy,
              $limit
            );
    $row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $res );
// dwildt, 120701
$this->feuserRecord = $row;
      // RETURN: no row
    if( empty( $row ) )
    {
      return null;
    }
      // RETURN: no row

    return $row;
  }









  /***********************************************
   *
   * SOAP
   *
   **********************************************/



 /**
  * soap( ):
  *
  * @return    [boolean]        true: password is empty; false: password isn't empty
  * @version  0.1.0
  * @since    0.0.5
  */
  private function soap( )
  {
    //$this->soapTest( );

      // Create the SOAP client
    if( ! $this->soapNewClient( ) )
    {
        // RETURN: there is an error
      return false;
    }
      // Create the SOAP client

      // Authenticate the user
    if( ! $this->soapAuthenticate( ) )
    {
        // RETURN: there is an error
      return false;
    }
      // Authenticate the user

    return true;
  }



 /**
  * soapAuthenticate( ): Authenticates the user by the SOAP server. If authentication is
  *                      successful, global $soapGroups will contain items.
  *
  * @return    boolean        true: authentication without any error; false: authentication with an error
  * @version  0.1.0
  * @since    0.1.0
  */
  private function soapAuthenticate( )
  {

      // RETURN: SOAP test is enabled
    if( $this->soaptest )
    {
      return $this->soapAuthenticateTestuser( );
    }
      // RETURN: SOAP test is enabled

      // Current login data
    $this->clientNo       = t3lib_div::_GP( 'soapuser_clientno' );
    $this->soapLogin       = $this->loginData['uname'];
    $this->soapPassword    = $this->loginData['uident'];
      // Current login data

      // TRY: authenticate
    try
    {
      $this->soapGroups = $this->soapclient->authenticate
                          (
                            $this->clientNo,
                            $this->soapLogin,
                            $this->soapPassword
                          );
      if( empty ( $this->soapGroups ) )
      {
        if ( $this->writeDevLog )
        {
          $prompt = 'SOAP authentification failed: no groups.';
          t3lib_div::devlog( '[WARN/SERVICES] ' . $prompt, $this->extKey, 3 );
        }
          // Authentification failed
        return false;
      }
        // Authentification was successful
      return true;
    }
    catch ( SoapFault $fault )
    {
      $prompt = null;
      $this->soapError( __METHOD__, __LINE__, $fault, $prompt );
//    $this->soapErrorAddTestPrompt( );
      return false;
    }
      // TRY: authenticate

      // Authentification was successful
    return true;
  }



 /**
  * soapAuthenticateTestuser( ):
  *
  * @return    boolean        true: authentication without any error; false: authentication with an error
  * @version  0.1.0
  * @since    0.1.0
  */
  private function soapAuthenticateTestuser( )
  {
    $this->clientNo  = t3lib_div::_GP( 'soapuser_clientno' );
    $this->soapLogin       = $this->loginData['uname'];
    $this->soapPassword    = $this->loginData['uident'];

    switch( true )
    {
      case( $this->clientNo  != $this->arr_extConf['testuserSoapClientno']  ):
      case( $this->soapLogin       != $this->arr_extConf['testuserSoapLogin']       ):
      case( $this->soapPassword    != $this->arr_extConf['testuserSoapPassword']    ):
          // RETURN: Authentification failed
        return false;
        break;
      default:
          // Authentification is successfull - step 1 from 2
          // follow the workflow
        break;

    }

      // Get external groups
    $csvExternalGroups = $this->arr_extConf['testuserExternalGroups'];
    $arrExternalGroups = explode( ',', $csvExternalGroups );

    foreach( ( array ) $arrExternalGroups as $key => $value )
    {
      $arrExternalGroups[$key] = trim( $value );
      if( empty( $arrExternalGroups[$key] ) )
      {
        unset( $arrExternalGroups[$key] );
      }
    }
      // Get external^d groups

      // RETURN: Authentification failed - step 2 from 2: no groups
    if( empty( $arrExternalGroups ) )
    {
      if ( $this->writeDevLog )
      {
        $prompt = 'Testuser authentification failed: no groups.';
        t3lib_div::devlog( '[WARN/SERVICES] ' . $prompt, $this->extKey, 3 );
      }
        // Set session data: status message
      $sessData       = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
      $status_message = $sessData['soapuser_status_message'];
      $status_message = $status_message . '
                        <h1>
                          TESTUSER ACCESS ERROR
                        </h1>
                        <p>
                          Any TYPO3 usergroup isn\'t attributed to the user.
                        </p>
                        <ul>
                          <li>
                            The user won\'t have any access without any usergroup.
                          </li>
                          <li>
                            User won\'t authenticated by TYPO3 extension soapuser.
                          </li>
                        </ul>
                        <h2>
                          What can you do?
                        </h2>
                        <ul>
                          <li>
                            Please take care of proper external groups for SOAP authentification.
                          </li>
                          <li>
                            You can maintain the external groups in the estension manager of TYPO3
                            extension soapuser.
                          </li>
                        </ul>';
      $sessData['soapuser_status_message'] = $status_message;
      $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
      return false;
    }
      // RETURN: Authentification failed - step 2 from 2: no groups

      // Set global $testuserExternalGroups
    $this->testuserExternalGroups = $arrExternalGroups;

      // DRS
    if ( $this->writeDevLog )
    {
      $prompt = 'Authentification of the testuser is successfull: access data are proper, user
                is attributed to some external groups.';
      t3lib_div::devlog( '[OK/SERVICES] ' . $prompt, $this->extKey, -1 );
    }
      // DRS

    return true;

  }



 /**
  * soapGetUser( ): Get the user data
  *
  * @return    boolean        true: authentication without any error; false: authentication with an error
  * @version  0.1.5
  * @since    0.1.2
  */
  private function soapGetUser( )
  {
    $user = null;

    $user->city           = '';
    $user->company        = '';
    $user->country        = '';
    $user->clientno       = '';
    $user->email          = '';
    $user->fax            = '';
    $user->firstName      = '';
    $user->homePhone      = '';
    $user->lastName       = '';
    $user->login          = '';
    $user->mobile         = '';
    $user->newLogin       = '';
    $user->oldPassword    = '';
    $user->password       = '';
    $user->phone          = '';
    $user->salutation     = '';
    $user->stateiso       = '';
    $user->zip            = '';

      // RETURN: SOAP test is enabled
    if( $this->soaptest )
    {
      $user->login          = $this->loginData['uname'];
      $user->password       = $this->loginData['uident'];
      return $user;
    }
      // RETURN: SOAP test is enabled

      // TRY: get the user data
    try
    {
      $user = $this->soapclient->getUser
              (
                $this->adminSoapCustomerno,
                $this->adminSoapLogin,
                $this->adminSoapPassword,
                $this->clientNo,
                $this->SoapLogin
              );
      if( empty ( $user ) )
      {
        if ( $this->writeDevLog )
        {
          $prompt = 'SOAP authentification failed: no groups.';
          t3lib_div::devlog( '[WARN/SERVICES] ' . $prompt, $this->extKey, 3 );
        }
          // Authentification failed
        return false;
      }
        // Get user data was successful

      return $user;
    }
    catch ( SoapFault $fault )
    {
      $prompt = null;
      $this->soapError( __METHOD__, __LINE__, $fault, $prompt );
      return false;
    }
      // TRY: get the user data

      // Undefined error
    return false;
  }



 /**
  * soapNewClient( ): Creates a SOAP client. Client is needed for connection and exchange
  *                   with SOAP server.
  *
  * @return    boolean        true: Client is generated; false: Creation failed
  * @version  0.1.0
  * @since    0.1.0
  */
  private function soapNewClient( )
  {
      // RETURN: SOAP test is enabled
    if( $this->soaptest )
    {
      if ( $this->writeDevLog )
      {
        $prompt = 'SOAP test mode is enabled: no SOAP client is needed.';
        t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
      }
      return true;
    }
      // RETURN: SOAP test is enabled

    $documentRoot = t3lib_div::getIndpEnv( 'TYPO3_DOCUMENT_ROOT' );
    $documentRoot = rtrim( $documentRoot, '/' );
    $wsdl         = $documentRoot . '/' . $this->arr_extConf['wsdlFile'];

    if ( $this->writeDevLog )
    {
      $prompt = 'wsdl file: ' . $wsdl;
      t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 0 );
    }

    try
    {
      $options = array
      (
        'trace' => 1
      );
      $this->soapclient = new SoapClient( $wsdl, $options );
      if ( $this->writeDevLog )
      {
        $prompt = 'New SOAP client is created successfully.';
        t3lib_div::devlog( '[OK/SERVICES] ' . $prompt, $this->extKey, -1 );
      }

    }
    catch ( SoapFault $fault )
    {
      $prompt = 'Path of the WSDL file is: ' . $wsdl;
      $this->soapError( __METHOD__, __LINE__, $fault, $prompt );
      return false;
    }

    return true;
  }



 /**
  * soapErrorAddTestPrompt( ):
  *
  * @return    void
  * @version  0.1.0
  * @since    0.1.0
  */
  private function soapErrorAddTestPrompt( )
  {
    $sessData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
    $status_message = $sessData['soapuser_status_message'];
    $status_message = '
                      <div style="background:red;color:white;font-weight:bold;margin:1em 0;padding:.4em;">
                        SOAP TEST MODE: Server error will ignored!
                      </div> ' . PHP_EOL .
                      $status_message;
    $sessData['soapuser_status_message'] = $status_message;
    $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
  }



 /**
  * soapError( ): Prompts a qualified error message to the DRS and into the session.
  *               See sesssion array soapuser_status_message.
  *
  * @param    [type]        $$method: ...
  * @param    [type]        $line: ...
  * @param    [type]        $fault: ...
  * @return    void
  * @version  0.1.0
  * @since    0.0.5
  */
  private function soapError( $method, $line, $fault, $addPrompt )
  {
    $status_message = '
      <h1>SERVER ERROR</h1>
      <p>
        Sorry, this error shouldn\'t occured.
      </p>';

      // DRS prompt
    if ( $this->writeDevLog )
    {
      $prompt = $method . ' (' . $line . '): ' .
                'SOAP code: '   . $fault->faultcode . PHP_EOL .
                'SOAP string: ' . $fault->faultstring . PHP_EOL .
                'LastRequest: ' . $this->soapclient->__getLastRequest( ) . PHP_EOL .
                'LastRequestHeaders: ' . $this->soapclient->__getLastRequestHeaders( ) . PHP_EOL .
                'LastResponse: ' . $this->soapclient->__getLastResponse( ) . PHP_EOL .
                'LastResponseHeaders: ' . $this->soapclient->__getLastResponseHeaders( );
      t3lib_div::devlog( '[ERROR/SERVICES] ' . $prompt, $this->extKey, 3 );
    }
      // DRS prompt

    $status_message = $status_message . PHP_EOL . '
                      <h2>
                        Prompt by the SOAP client
                      </h2>
                      <ul>
                        <li>
                          Code: ' . $fault->faultcode . '
                        </li>
                        <li>
                          Prompt: ' . $fault->faultstring . '
                        </li>
                        <li>
                          LastRequest: ' . $this->soapclient->__getLastRequest( ) . '
                        </li>
                        <li>
                          LastRequestHeaders: ' . $this->soapclient->__getLastRequestHeaders( ) . '
                        </li>
                        <li>
                          LastResponse: ' . $this->soapclient->__getLastResponse( ) . '
                        </li>
                        <li>
                          LastResponseHeaders: ' . $this->soapclient->__getLastResponseHeaders( ) . '
                        </li>
                      </ul>';

    if( $addPprompt )
    {
      $status_message = $status_message . PHP_EOL . '
                      <h2>
                        Additional prompt by the method
                      </h2>
                      <ul>
                        <li>
                          ' . $addPrompt . '
                        </li>
                      </ul>';

    }
    $status_message = $status_message . PHP_EOL . '
                      <h2>
                        PHP information
                      </h2>
                      <ul>
                        <li>
                          method: ' . $method . '
                        </li>
                        <li>
                          line: ' . $line . '
                        </li>
                      </ul>';

    switch( $fault->faultcode )
    {
      case( 'HTTP' ):
          // DRS prompt
        if ( $this->writeDevLog )
        {
          $prompt = 'Please check the URL manually, which is used by the wsdl file. See property targetNamespace.';
          t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 2 );
        }
          // DRS prompt
        $status_message = $status_message . PHP_EOL . '
                          <h2>
                            Possible reasons
                          </h2>
                          <ul>
                            <li>
                              Maybe the SOAP server isn\'t online.
                            </li>
                            <li>
                              Maybe the URL in the WSDL file isn\'t proper.
                            </li>
                          </ul>
                          <h2>
                            What can you do?
                          </h2>
                          <ul>
                            <li>
                              <strong>If your are a valid client,</strong> please send an e-mail to the
                              administrator of this website. Please include this error message,
                            </li>
                            <li>
                              <strong>If your are the administrator</strong> of this website, please check the URL
                              of the SOAP server manually.<br />
                              See property targetNamespace in the WSDL file.<br />
                              The data exchange with the SOAP server is managed by the TYPO3 extension soapuser.
                            </li>
                          </ul> ';
        break;
      case( 'WSDL' ):
          // DRS prompt
        if ( $this->writeDevLog )
        {
          $prompt = 'Please check the path of the WSDL file and the WSDL file.';
          t3lib_div::devlog( '[INFO/SERVICES] ' . $prompt, $this->extKey, 2 );
        }
          // DRS prompt
        $status_message = $status_message . PHP_EOL . '
                          <h2>
                            Possible reasons
                          </h2>
                          <ul>
                            <li>
                              Maybe the path of the WSDL file isn\'t proper.
                            </li>
                            <li>
                              Maybe the WSDL file isn\'t proper.
                            </li>
                          </ul>
                          <h2>
                            What can you do?
                          </h2>
                          <ul>
                            <li>
                              <strong>If your are a valid client,</strong> please send an e-mail to the
                              administrator of this website. Please include this error message,
                            </li>
                            <li>
                              <strong>If your are the administrator</strong> of this website, please check the path
                              of the WSDL file and the WSDL file manually.<br />
                              The data exchange with the SOAP server is managed by the TYPO3 extension soapuser.
                            </li>
                          </ul> ';
        break;
    }

      // Set session data: status message
    $sessData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
    $sessData['soapuser_status_message'] = $status_message;
    $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
      // Set session data: status message
  }

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/soapuser/sv1/class.tx_soapuser_sv1.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/soapuser/sv1/class.tx_soapuser_sv1.php']);
}




?>
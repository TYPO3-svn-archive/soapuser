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
 * 'Hooks' for the 'soapuser' extension. Hook methods for foreign extensions liek felogin.
 *
 * @author    Dirk Wildt <http://wildt.at.die-netzmacher.de>
 * @package    TYPO3
 * @subpackage  soapuser
 *
 * @version 0.1.0
 * @since 0.0.4
 */

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   52: class tx_soapuser_hooks
 *   72:     public function felogin_postProcContent( $params, &$pObj )
 *  104:     private function zz_replaceFormMarker( $content, $formsMarker )
 *  151:     private function zz_removeMarker( $content )
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



class tx_soapuser_hooks
{
    // [object] parent object
  private $pObj = null;
    // [array] TypoScript configuration of the parent object
  private $conf = null;

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
  * DRS mode all
  *
  * @var boolean
  */
  private $drsAll   = false;
  
 /**
  * DRS mode hooks
  *
  * @var boolean
  */
  private $drsHooks = false;
  
 /**
  * DRS mode session
  *
  * @var boolean
  */
  private $drsSession = false;
  
 /**
  * DRS mode sql
  *
  * @var boolean
  */
  private $drsSql = false;

 /**
  * Current feuser record from table fe_users
  *
  * @var array
  */
  private $feuserRecord;

  




/**
 * felogin_postProcContent( ): Handle the login form of the felogin extension
 *                             before sending. Replace and remove markers.
 *
 * @param    array        $params:  Given parameter
 * @param    array        &$pObj:   Reference to the parent object
 * @return    string        $content: The rendered content
 * @version  0.0.4
 * @since    0.0.4
 */
  public function felogin_postProcContent( $params, &$pObj )
  {
    $this->init( );

    if ( $this->drsHooks )
    {
      $prompt = __METHOD__ . ' is called.';
      t3lib_div::devlog( '[INFO/HOOKS] ' . $prompt, $this->extKey, 0 );
    }
    
      // Move session date from a non logged in user to a logged in user
    $sessData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );
    $GLOBALS['TSFE']->fe_user->setKey( 'user', 'soapuser', $sessData );
    $GLOBALS["TSFE"]->storeSessionData();
      // Move session date from a non logged in user to a logged in user
      
//    if ( $this->drsHooks )
//    {
//      $prompt = print_r( $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' ), true );
//      t3lib_div::devlog( '[INFO/HOOKS] ses: ' . $prompt, $this->extKey, 3 );
//      $prompt = print_r( $GLOBALS['TSFE']->fe_user->getKey( 'user', 'soapuser' ), true );
//      t3lib_div::devlog( '[INFO/HOOKS] user: ' . $prompt, $this->extKey, 3 );
//    }
    
      // Class var $pObj
    $this->pObj = $pObj;
      // Class var $conf
    $this->conf = $pObj->conf['extensions.']['soapuser.'];

      // Current content
    $content = $params['content'];
      // Form login of the felogin extension with replaced soapuser markers
    $content = $this->zz_replaceFormMarker( $content, $formsMarker );

      // Remove left over markers
    $content = $this->zz_removeMarker( $content );

      // Reset session data
    $this->sessionResetData( );

      // Return the content
    return $content;
  }




/**
 * felogin_logout_confirmed( ):
 *
 * @param    array        $params:  Given parameter
 * @param    array        &$pObj:   Reference to the parent object
 * @return    string        $content: The rendered content
 * @version  0.0.4
 * @since    0.0.4
 */
  public function felogin_logout_confirmed( $params, &$pObj )
  {
    $this->init( );

    if ( $this->drsHooks )
    {
      $prompt = __METHOD__ . ' is called.';
      t3lib_div::devlog( '[INFO/HOOKS] ' . $prompt, $this->extKey, 0 );
    }
    
//    $sessData = null;
//    $GLOBALS['TSFE']->fe_user->setKey( 'user', 'soapuser', $sessData );
//    $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
//    $GLOBALS["TSFE"]->storeSessionData();
//var_dump(__METHOD__, __LINE__, 'felogin_logout_confirmed' );

      // Reset session data
    $this->sessionResetData( );
  }



/**
 * zz_replaceFormMarker( ): Replace marker in the given content
 *
 * @param    string        $content:     Current content
 * @return    string        $content:     The rendered content
 * @version  0.1.0
 * @since    0.0.4
 */
  private function zz_replaceFormMarker( $content )
  {
      // Array with markers for the form login of the felogin extension
    $formsMarker = $this->conf['forms.']['marker.'];

      // Flag for handle empty marker
    $removeEmptyMarker = $this->conf['workflow.']['removeEmptyMarker'];
    
      // Default value
    $markerArray = array();

      // LOOP: form marker
    foreach( $formsMarker as $markerKey => $markerValue )
    {
        // Avoid IDE warning
      $markerValue = 'dummy';
      
        // CONTINUE: current marker is an array
      if( is_array ( $formsMarker[$markerKey] ) )
      {
        continue;
      }
        // CONTINUE: current marker is an array

        // Render current cObj
      $cObj_name  = $formsMarker[$markerKey];
      $cObj_conf  = $formsMarker[$markerKey . '.' ];
      $value      = $this->pObj->cObj->cObjGetSingle( $cObj_name, $cObj_conf );
        // Render current cObj

        // CONTINUE: marker is empty and empty marker should not removed
      if( empty ( $value ) && ( ! $removeEmptyMarker ) )
      {
        continue;
      }
        // CONTINUE: marker is empty and empty marker should not removed
    
        // Set the marker array
      $marker               = '###' . strtoupper( $markerKey ) . '###';
      $markerArray[$marker] = $value;
        // Set the marker array
    }
      // LOOP: form marker

      // Replace the markers in the form
    $content = $this->pObj->cObj->substituteMarkerArray( $content, $markerArray );
    
      // Return the content with replaced markers
    return $content;
  }



 /**
  * init( )
  *
  * @return   void
  * @version  0.1.0
  * @since    0.1.0
  */
  private function init( )
  {
      // Set class var $arr_extConf, the extmanager configuration
    $this->arr_extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
    
    $this->initDRS( );
  }



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
      case( 'Hooks' ):
        $this->drsAll     = true;
        $this->drsError   = true;
        $this->drsWarn    = true;
        $this->drsInfo    = true;
        $this->drsHooks   = true;
        $this->drsSession = true;
        $this->drsSql     = true;
        t3lib_div::devlog( '[OK/DRS] DRS is enabled: ' . $this->arr_extConf['drs_mode'], $this->extKey, -1 );
        break;
      default:
          // do nothing;
        break;
    }
  }



 /**
  * sessionResetData( ):
  *
  * @return    void
  * @version  0.1.0
  * @since    0.1.0
  */
  private function sessionResetData( )
  {

//var_dump( __METHOD__, __LINE__, t3lib_div::_GET( ), t3lib_div::_POST( ), $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' ) );
  
    $logintype = t3lib_div::_POST( 'logintype' );
    switch( true )
    {
      case( $logintype == 'logout' ):
          // Logout: Reset session data
        $sessData = array( );
        $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
        $GLOBALS["TSFE"]->storeSessionData();
        if ( $this->drsHooks )
        {
          $prompt = 'Session data soapuser are removed.';
          t3lib_div::devlog( '[INFO/HOOKS] ' . $prompt, $this->extKey, 0 );
        }
        return;
        break;
          // Logout: Reset session data
      default:
          // Update session data
          // RETURN: Error with SQL query
        if( ! $this->feuserSqlSelect( ) )
        {
          if( $this->drsError )
          {
            $prompt = 'Abort: Error with SQL query.';
            t3lib_div::devlog(' [ERROR/SESSION] '. $prompt, $this->extKey, 3 );
          }
          return;
        }
          // RETURN: Error with SQL query

          // Set SOAP user session data
        $this->sessionSoapUsersSet( );
        break;
    }

      // DRS
    if ( $this->drsHooks )
    {
      $prompt = 'Session data soapuser aren\'t removed.';
      t3lib_div::devlog( '[INFO/HOOKS] ' . $prompt, $this->extKey, 0 );
    }
      // DRS
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

      // Get current session data
    $sessData = $GLOBALS['TSFE']->fe_user->getKey( 'ses', 'soapuser' );

//      // Add client number to session data
//    $sessData['soapuser_clientno'] = $sessData['soapuser_clientno'];

      // Add fe_user record elements to session data
    foreach( $this->feuserRecord as $key => $value )
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
      // Add fe_user record elements to session data
      
      // Set session data
    $GLOBALS['TSFE']->fe_user->setKey( 'ses', 'soapuser', $sessData );
    $GLOBALS["TSFE"]->storeSessionData();

      // DRS
    if ( $this->drsHooks || $this->drsSession )
    {
      $prompt = 'Session data soapuser are updated.';
      t3lib_div::devlog( '[INFO/HOOKS] ' . $prompt, $this->extKey, 0 );
    }
      // DRS
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
      if( $this->drsError )
      {
        $prompt = 'Abort: $GLOBALS[TSFE]->loginUser isn\'t true!';
        t3lib_div::devlog(' [ERROR/UPDATE] '. $prompt, $this->extKey, 3 );
      }
      return false;
    }
    
    $uid = $GLOBALS['TSFE']->fe_user->user['uid'];
    if( empty( $uid ) )
    {
      if( $this->drsError )
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
    if( $this->drsSql )
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
      if( $this->drsError )
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
   * ZZ
   *
   **********************************************/



   /**
 * zz_removeMarker( ): Removes left over markers in the current content.
 *                     Removing depends on TypoScript property workflow.removeEmptyMarker
 *
 * @param    string        $content:     Current content
 * @param    array        $formsMarker: Array of markers. Markers have to be a cObj
 * @return    string        $content:     The rendered content
 * @version  0.0.4
 * @since    0.0.4
 */
  private function zz_removeMarker( $content )
  {
      // TypoScript configuration
    $removeEmptyMarker = $this->conf['workflow.']['removeEmptyMarker'];

      // CONTINUE: left over markers should not removed
    if( ! $removeEmptyMarker )
    {
      return $content;
    }
      // CONTINUE: left over markers should not removed

      // Remove left over markers
    $content = preg_replace('|###.*?###|i', '', $content );

      // Return the content with removed markers
    return $content;
  }

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/browser/pi1/class.tx_soapuser_hooks.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/browser/pi1/class.tx_soapuser_hooks.php']);
}

?>
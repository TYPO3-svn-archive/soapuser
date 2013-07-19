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
* Class provides methods for the extension manager.
*
* @author    Dirk Wildt <http://wildt.at.die-netzmacher.de>
* @package    TYPO3
* @subpackage    soapuser
* @version  0.1.1
* @since    0.0.1
*/


  /**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   49: class tx_soapuser_extmanager
 *   67:     function promptCheckUpdate()
 *  102:     function promptCurrIP()
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_soapuser_extmanager
{
  
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
   * confChecker( ): ...
   *
   * @return    string        message wrapped in HTML
   * @version 0.1.0
   * @since   0.0.1
   */
  function confChecker( )
  {
//.message-notice
//.message-information
//.message-ok
//.message-warning
//.message-error

    $prompt = null;
    $this->arr_extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['soapuser']);
    
    $this->init_accessByIP( ); 
      
      // loginSecurityLevel
    $prompt = $this->confCheckerLoginSecurityLevel( $prompt );
      // saltedpasswords
    $prompt = $this->confCheckerSaltedPasswords( $prompt );
      // SOAP admin
    $prompt = $this->confCheckerSoapAdmin( $prompt );
      // SOAP testuser
    $prompt = $this->confCheckerSoapTestUser( $prompt );
      // pid of fe_users 
    $prompt = $this->confCheckerFeusersPid( $prompt );
      // remove group for fe_users 
    $prompt = $this->confCheckerFeusersRemoveGroup( $prompt );
      // Soap mode and current IP
    $prompt = $this->confCheckerSoapMode( $prompt );
      // DRS
    $prompt = $this->confCheckerDrs( $prompt );
    

    return $prompt;
  }



  /**
   * confCheckerDrs(  ):  ...
   *
   * @return    string        message wrapped in HTML
   * @version 0.1.0
   * @since   0.0.1
   */
  private function confCheckerDrs( $prompt )
  {

    switch( true )
    {
      case( $this->arr_extConf['drs_mode'] != 'Don\'t log anything' ):
        $prompt = $prompt . '
          <div class="typo3-message message-warning">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptDrsWarn') . '
            </div>
          </div>';
        break;
      default:
        $prompt = $prompt . '
          <div class="typo3-message message-information">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptDrsOk') . '
            </div>
          </div>';
        break;
    }
      // Debugging tip
    

    return $prompt;
  }



  /**
   * confCheckerFeusersPid( ): ...
   *
   * @return    string        message wrapped in HTML
   * @version 0.1.0
   * @since   0.1.0
   */
  private function confCheckerFeusersPid( $prompt )
  {
    if( empty( $this->arr_extConf['fe_usersPid'] ) )
    {
      $prompt = $prompt . '
        <div class="typo3-message message-error">
          <div class="message-body">
            ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerFeusersPidEmpty') . '
          </div>
        </div>';
      return $prompt;
    }

    $select_fields  = '*';
    $from_table     = 'pages';
    $where_clause   = 'uid = ' . ( int ) $this->arr_extConf['fe_usersPid'];
    $groupBy        = '';
    $orderBy        = '';
    $limit          = '';

//    var_dump
//    ( 
//      $GLOBALS['TYPO3_DB']->SELECTquery
//      ( 
//        $select_fields, 
//        $from_table, 
//        $where_clause, 
//        $groupBy, 
//        $orderBy, 
//        $limit 
//      ) 
//    );
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
      $prompt = $prompt . '
        <div class="typo3-message message-error">
          <div class="message-body">
            ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerFeusersPidEmptyRow') . '
          </div>
        </div>';
      return $prompt;      
    }
      // RETURN: no row

    $prompt = $prompt . '
      <div class="typo3-message message-ok">
        <div class="message-body">
          ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerFeusersPidOk') . '
        </div>
      </div>';

    return $prompt;
  }



  /**
   * confCheckerFeusersRemoveGroup( ): ...
   *
   * @return    string        message wrapped in HTML
   * @version 0.1.0
   * @since   0.1.0
   */
  private function confCheckerFeusersRemoveGroup( $prompt )
  {
    if( empty( $this->arr_extConf['fe_usersRemoveGroup'] ) )
    {
      $prompt = $prompt . '
        <div class="typo3-message message-error">
          <div class="message-body">
            ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerFeusersRemoveGroupEmpty') . '
          </div>
        </div>';
      return $prompt;
    }


    $prompt = $prompt . '
      <div class="typo3-message message-ok">
        <div class="message-body">
          ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerFeusersRemoveGroupOk') . '
        </div>
      </div>';

    return $prompt;
  }



  /**
   * confCheckerLoginSecurityLevel( ): ...
   *
   * @return    string        message wrapped in HTML
   * @version 0.1.0
   * @since   0.1.0
   */
  private function confCheckerLoginSecurityLevel( $prompt )
  {
    if( $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'] == 'rsa' )
    {
      $prompt = $prompt . '
        <div class="typo3-message message-error">
          <div class="message-body">
            ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerRsaError') . '
          </div>
        </div>';
    }
    else
    {
//        $prompt = $prompt . $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'];
      $prompt = $prompt . '
        <div class="typo3-message message-ok">
          <div class="message-body">
          ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerRsaOk') . '
          </div>
        </div>';
    }
    return $prompt;
    
  }



  /**
   * confCheckerSaltedPasswords( ): ...
   *
   * @return    string        message wrapped in HTML
   * @version 0.1.0
   * @since   0.1.0
   */
  private function confCheckerSaltedPasswords( $prompt )
  {
    if( t3lib_extMgm::isLoaded( 'saltedpasswords' ) )
    {
      $arr_extConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['saltedpasswords'] );
      if( $arr_extConf['FE.']['enabled'] )
      {
        $prompt = $prompt . '
          <div class="typo3-message message-error">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerSaltedpasswordsError') . '
            </div>
          </div>';
      }
      else
      {
//        $prompt = $prompt . $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'];
        $prompt = $prompt . '
          <div class="typo3-message message-ok">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerSaltedpasswordsOk') . '
            </div>
          </div>';
      }
    }
    
    return $prompt;
  }



  /**
   * confCheckerSoapMode(  ):  ...
   *
   * @return    string        message wrapped in HTML
   * @version 0.1.0
   * @since   0.0.1
   */
  private function confCheckerSoapMode( $prompt )
  {
    switch( true )
    {
      case( $this->arr_extConf['soapMode'] != 'Disabled' ):
        $prompt = $prompt . '
          <div class="typo3-message message-warning">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptSoapModeWarn') . '
            </div>
          </div>';
        $prompt = $this->confCheckerCurrentIP( $prompt );
        break;
      default:
        $prompt = $prompt . '
          <div class="typo3-message message-information">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptSoapModeOk') . '
            </div>
          </div>';
        break;
    }

    return $prompt;
  }



  /**
   * confCheckerCurrentIP(  ):  ...
   *
   * @return    string        message wrapped in HTML
   * @version 0.1.0
   * @since   0.0.1
   */
  private function confCheckerCurrentIP( $prompt )
  {
    switch( $this->bool_accessByIP )
    {
      case( false ):            
        $prompt = $prompt . '
          <div class="typo3-message message-error">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptAccessByIpError') . '
            </div>
          </div>';
        break;
      default:
        $prompt = $prompt . '
          <div class="typo3-message message-ok">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptAccessByIpOk') . '
            </div>
          </div>';
        break;
    }

    return $prompt;
  }



  /**
   * confCheckerSoapTestUser( ): ...
   *
   * @return    string        message wrapped in HTML
   * @version 0.1.0
   * @since   0.1.0
   */
  private function confCheckerSoapTestUser( $prompt )
  {
    
    switch( true )
    {
      case( empty( $this->arr_extConf['testuserSoapCustomerno'] ) ):
      case( empty( $this->arr_extConf['testuserSoapLogin'] ) ):
      case( empty( $this->arr_extConf['testuserSoapPassword'] ) ):
      case( empty( $this->arr_extConf['testuserExternalGroups'] ) ):
        $prompt = $prompt . '
          <div class="typo3-message message-warning">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerTestuserError') . '
            </div>
          </div>';
        break;
      default:
        $prompt = $prompt . '
          <div class="typo3-message message-ok">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerTestuserOk') . '
            </div>
          </div>';
        break;
    }
      
    return $prompt;
  }






  /**
   * confCheckerSoapAdmin( ): ...
   *
   * @return    string        message wrapped in HTML
   * @version 0.1.0
   * @since   0.1.0
   */
  private function confCheckerSoapAdmin( $prompt )
  {
    
    switch( true )
    {
      case( empty( $this->arr_extConf['adminSoapCustomerno'] ) ):
      case( empty( $this->arr_extConf['adminSoapLogin'] ) ):
      case( empty( $this->arr_extConf['adminSoapPassword'] ) ):
        $prompt = $prompt . '
          <div class="typo3-message message-error">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerAdminError') . '
            </div>
          </div>';
        break;
      default:
        $prompt = $prompt . '
          <div class="typo3-message message-ok">
            <div class="message-body">
              ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptConfCheckerAdminOk') . '
            </div>
          </div>';
        break;
    }

    return $prompt;
  }



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
   * promptCurrIP( ): Displays the IP of the current backend user
   *
   * @return    string        message wrapped in HTML
   * @version 0.0.1
   * @since   0.0.1
   */
  function promptCurrIP( )
  {
//.message-notice
//.message-information
//.message-ok
//.message-warning
//.message-error

      $prompt = null;

      $prompt = $prompt.'
<div class="typo3-message message-information">
  <div class="message-body">
    ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptCurrIPBody') . ': ' . t3lib_div :: getIndpEnv('REMOTE_ADDR') . '
  </div>
</div>';

    return $prompt;
  }



  /**
   * promptExternalLinks(): Displays the quick start message.
   *
   * @return  string    message wrapped in HTML
   * @version 0.0.1
   * @since   0.0.1
   */
  function promptExternalLinks()
  {
//.message-notice
//.message-information
//.message-ok
//.message-warning
//.message-error

      $prompt = null;

      $prompt = $prompt.'
<div class="message-body">
  ' . $GLOBALS['LANG']->sL('LLL:EXT:soapuser/lib/locallang.xml:promptExternalLinksBody'). '
</div>';

    return $prompt;
  }









}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/soapuser/lib/class.tx_soapuser_extmanager.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/soapuser/lib/class.tx_soapuser_extmanager.php']);
}

?>
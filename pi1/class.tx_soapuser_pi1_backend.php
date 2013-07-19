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
 * The class tx_soapuser_pi1_backend bundles methods for backend support like itemsProcFunc
 *
 * @author    Dirk Wildt http://wildt.at.die-netzmacher.de
 * @package    TYPO3
 * @subpackage    soapuser
 * @version 0.1.0
 * @since 0.1.0
 */

 /**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   49: class tx_soapuser_pi1_backend
 *
 *              SECTION: Sheets
 *   71:     public function sDEF_info( $arr_pluginConf )
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_soapuser_pi1_backend
{



  /***********************************************
   *
   * Sheets
   *
   **********************************************/



  /**
 * sDef_getArrViewsList: Get data query (and andWhere) for all list views of the current plugin.
 * Tab [General/sDEF]
 *
 * @param    array        $arr_pluginConf: Current plugin/flexform configuration
 * @return    array        with the names of the views list
 * @version 0.1.0
 * @since 0.1.0
 */
  public function sDEF_info( $arr_pluginConf )
  {
    $dummy = $arr_pluginConf;
    return $GLOBALS['LANG']->sL('LLL:EXT:soapuser/pi1/locallang_flexform.xml:sDEF.info') . '</h1>';
  }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/soapuser/pi1/class.tx_soapuser_pi1_backend.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/soapuser/pi1/class.tx_soapuser_pi1_backend.php']);
}
?>
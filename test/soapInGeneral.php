<?php
  error_reporting(-1);
  ini_set('display_errors', TRUE);

  $wsdl         = 'http://www.thomas-bayer.com/axis2/services/BLZService?wsdl';
  $bankleitzahl = '12070000'; // testdaten



  class RequestType
  {
      public $blz;
  }

  $options = array();
  $options['classmap']['getBankType']  = 'RequestType';

  $bank         = new RequestType;
  $bank->blz    = $bankleitzahl;

  $soapclient = new SoapClient($wsdl,$options);
  $result     = $soapclient->getBank($bank);
?>
<h1>Result</h1>
<pre> <?php var_dump($result)?> </pre>
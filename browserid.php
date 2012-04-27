<?php

class BrowserIDVerifier {
  private $_config;
  
  public function __construct(array $config) {
    $this->_config = $config;
    $this->_config['absPath'] = '/var/www/phpvoot/ext/browserid';
  }

  private function verifyAssertion($assertion, $audience) {
    $url = 'https://browserid.org/verify';
    $params = 'assertion='.$assertion.'&audience=' . $audience;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch,CURLOPT_POST,2);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($ch);
    curl_close($ch);
    try {
      $resultObj = json_decode($result, true);
      return (isset($resultObj['email']) ? $resultObj['email'] : false);
    } catch (Exception $e) {
      return false;
    }
  }
  
  public function requireAuth() {
    session_start();
    if (!isset($_SESSION['browserid_attr'])) {
      if(isset($_POST['assertion'])) {
        $result = $this->verifyAssertion($_POST['assertion'], 'http://lamp.unhosted.org');
        if($result) {
          $_SESSION['browserid_attr'] = $result;
        } else {//var_dump($_POST);
          die(file_get_contents($this->_config['absPath'].'/dialog.html'));
        }
        //var_dump($_SESSION);die();
      } else {var_dump($_POST);
        die(file_get_contents($this->_config['absPath'].'/dialog.html'));
      }
    }
      //var_dump($_SESSION);//die();
  }
  public function getAttributes() {
    return (isset($_SESSION['browserid_attr']) ? $_SESSION['browserid_attr'] : null);
  }
}

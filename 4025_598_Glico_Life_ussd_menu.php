<?php

ini_set("date.timezone", "Africa/Accra");

//Tell browser site should only be loaded over https
header("Strict-Transport-Security:max-age=63072000");

include 'DynamicMenuController.php';
include '/var/www/html-ke/hub/channels/ussdMenus/dynamic_menus/configs/Glico/BeepLogger.php';
include '/var/www/html-ke/hub/channels/ussdMenus/dynamic_menus/configs/Glico/GlicoConfig.php';

class Glico_life extends DynamicMenuController{
     private $log = null;
     private $config = null;
     private $terminator = "\n0. back\n51. main menu \n52. exit";
     private $myterminator = "\n51. main menu \n52. exit ";
     private $histerminator= "\n50. main menu \n51. next \n52. exit";


     public static $mobilePackages = array();
     public static $mobileMoneyOptions= array();
     public static $Products= array();

     public $productPage=1;


public function __construct() {
    $this->log = new BeepLogger();
    parent::__construct();
}

function StartPage($input){
    if ($input == 0 || $input == null) {
        $message = "Welcome To Glico Life\n ";
        $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
        $this->sessionState = "CONTINUE";
        $this->nextFunction = "InsurancePolicy";
        $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'StartPage');
    }
}


function InsurancePolicy($input){
    if($input == 1){
        $this->saveSessionVar('productPage', $this->productPage);
        $productPage=$this->getSessionVar('productPage');
        $result = $this->getProducts();
        $this->saveSessionVar('packages', $result);
        $result = $this->showPackages($result,$productPage);
        $this->displayText = "Please Select Product:" ."\n" ."\n" . $result . $this->histerminator;
        $this->sessionState = "CONTINUE";
        $this->nextFunction = "selectProduct";
        $this->previousPage = "StartPage";
        $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Buy Insurance Policy');
}else if ($input ==2) {
        $this->displayText = "  Enter Policy Number: \n " . $this->terminator;
        $this->sessionState = "CONTINUE";
        $this->nextFunction = "policy_number";
        $this->previousPage = "StartPage";
        $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Pay Premium');
}else if ($input ==3){
        $message = "Please Select Option:\n ";
        $this->displayText ="$message\n1.Momo\n2.Bank \n" . $this->terminator;
        $this->sessionState = "CONTINUE";
        $this->nextFunction = "auto_deduct";
        $this->previousPage = "StartPage";
        $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Auto Deduct');
}else if ($input ==4){
        $this->displayText = "Please Enter your name: \n " . $this->terminator;
        $this->sessionState = "CONTINUE";
        $this->nextFunction = "phone_number";
        $this->previousPage = "StartPage";
        $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Special Promotion');
}else if ($input ==5){
        $this->displayText = "Please use this link: https://portal.glicolife.com/ \n" . $this->myterminator;
        $this->sessionState = "CONTINUE";
         $this->nextFunction = "END";
        $this->previousPage = "StartPage";
        $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Check Your Statement');
}else if ($input ==6){
        $this->displayText = "Please use this link: https://glicolife.com/online-claims/ \n" . $this->myterminator;
        $this->sessionState = "CONTINUE";
        $this->nextFunction = "END";
        $this->previousPage = "StartPage";
        $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Initiate A Claim');
}else if ($input ==7){
        $this->displayText = "You can contact us on 0202222113 or 0302218500 or email: customerservices@glicogroup.com \n" . $this->myterminator;
        $this->sessionState = "CONTINUE";
        $this->nextFunction = "END";
        $this->previousPage = "StartPage";
        $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Contact Us');
}else{
        $this->displayText = "Invalid input";
}
}

function selectProduct($input){
 if ($input ==51){
    $productPage=$this->getSessionVar('productPage');
    $result=$this->getSessionVar('packages');
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Value of Packages: ".$result);
    $result = $this->showPackages($result,$productPage);
    $this->displayText = "Please Select Product:" ."\n"."\n" . $result . $this->histerminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "selectProduct";
    $this->previousPage = "selectProduct";
}else{
    $this->product_selected($input);
}
}

function getProducts(){
    //"client=ussd_client&secret_key=asfn349nasd8sdf9"
    //$header = array('Content-Type: application/x-www-form-urlencoded');
    $payload = GlicoConfig::$Payload;
    $token = $this->authToken($payload);
    $hdr = array('Authorization: Bearer ' . $token,'Content-Type: application/x-www-form-urlencoded');
    //$payload = array('client'=>'ussd_client','secret_key'='asfn349nasd8sdf9');
    $url = GlicoConfig::Packages_API_URL;
    $Packages=$this->post_1($url , array("is_micro"=>0), $hdr);
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Value of Packages: ".$Packages);
    $decode=json_decode($Packages,true);

    return $decode;

}


function showPackages($decode,$productPage){
    $displayText = "";
    $itemsPerPage = 3;
    $itemsPerPageCount = 0;
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Value of productPage: ".$productPage);

    // Check the item index to display
    $displayItems = $productPage * $itemsPerPage;
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "DisplayItems: ".$displayItems);

    foreach($decode['packages'] as $i => $package)
    {
    // Display items based on the index and show the 3 items before the index
    if($i < $displayItems && $i >= ($displayItems-3)){
     $displayText .= $i.": ".$package['description']."\n";
     $itemsPerPageCount++;
      }

    if($itemsPerPageCount == $itemsPerPage){
        $productPage++;
        $this->saveSessionVar('productPage', $productPage);
        $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Now Value of productPage is: ".$productPage);
        $itemsPerPageCount = 0;
        break;
       }
    }

     return $displayText;
}


function findPackage($input){
    $packages=$this->getSessionVar('packages');

    foreach($packages['packages'] as $i => $package)
    {
        if($input == $i){
            return $package['plan_code'];
            break;
        }

    }

}

function findNarration($input){
    $packages=$this->getSessionVar('packages');

    foreach($packages['packages'] as $i => $package)
    {
        if($input == $i){
            return $package['benefit'];
            break;
        }

    }

}

function product_selected($input){
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Input received is ".$input);
    $packages=$this->getSessionVar('packages');
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "value of package is ".$packages['packages']);
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "length of package is ".count($packages['packages']));
if ($input ==null){
     $this->saveSessionVar('productPage', $this->productPage);
     $productPage=$this->getSessionVar('productPage');
     $result = $this->getProducts();
     $this->saveSessionVar('packages', $result);
     $result = $this->showPackages($result,$productPage);
     $this->displayText = "Please Select Product:" ."\n" . $result . $this->histerminator;
     $this->sessionState = "CONTINUE";
     $this->nextFunction = "selectProduct";
     $this->previousPage = "StartPage";
}else if ($input ==50){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}else if($input < 0 || $input > count($packages['packages'])){
    $this->displayText = "Wrong input";
}else{
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "About to fetch plan code...");
    $planCode = $this->findPackage($input);
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Plancode: ".$planCode);
    $this->saveSessionVar('plancode', $planCode);
    $Plancode=$this->getSessionVar('plancode');
    //"client=ussd_client&secret_key=asfn349nasd8sdf9"
    //$header = array('Content-Type: application/x-www-form-urlencoded');
    $payload = GlicoConfig::$Payload;
    $token = $this->authToken($payload);
    $hdr = array('Authorization: Bearer ' . $token,'Content-Type: application/x-www-form-urlencoded');
    //$payload = array('client'=>'ussd_client','secret_key'='asfn349nasd8sdf9');
    $url = GlicoConfig::Packages_API_URL;
    $Packages=$this->post_1($url , array("is_micro"=>0), $hdr);
    $decode=json_decode($Packages,true);
    foreach($decode['packages'] as $i => $package);
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "About to fetch narration(benefit)...");
    $narration=$this->findNarration($input);
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Narration(Benfit) is: " .$narration);
    $this->saveSessionVar('benefit', $benefit);
    $benefit=$this->getSessionVar('benefit');
    $message = "About Product:\n";
    $this->displayText = "$message\n" . $narration . "\n1.Proceed\n" . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "prod_amt";
    $this->previousPage = "InsurancePolicy";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Plancode is: ".$Plancode);
    }
}

function prod_amt($input){
if ($input ==1){
    $this->displayText = "Please Enter the Amount(Premium): \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "name";
    $this->previousPage = "InsurancePolicy";
}else if ($input =='0'){
    $this->saveSessionVar('productPage', $this->productPage);
    $productPage=$this->getSessionVar('productPage');
    $result = $this->getProducts();
    $this->saveSessionVar('packages', $result);
    $result = $this->showPackages($result,$productPage);
    $this->displayText = "Please Select Product:" ."\n" . $result . $this->histerminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "selectProduct";
    $this->previousPage = "StartPage";
}else if ($input ==51){
    $message = "Welcome To Glico Life ";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}else{
    $this->displayText ="Input is not correct. Please try again";
    $this->sessionState = "END";
}
}

function name($input){
    $this->saveSessionVar('amount', $input);
    $Amount=$this->getSessionVar('amount');
if ($Amount < 50){
    $this->displayText = "Amount cannot be less than 50 cedis.Please Enter the Amount(Premium): \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "name";
    $this->previousPage = "InsurancePolicy";
}else{
    $this->displayText = "Please Enter your name: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "product_selected1";
    $this->previousPage = "InsurancePolicy";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Amount is: ".$Amount);
}
if ($input ==null){
    $this->displayText = "No Value Entered!\nPlease Enter the Amount(Minimum amount is GHS 50): \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "name";
    $this->previousPage = "InsurancePolicy";
}else if ($input ==0){
    $this->InsurancePolicy(1);
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function policy_number($input){
    $this->saveSessionVar('policyNumber', $input);
    $PolicyNumber=$this->getSessionVar('policyNumber');
    //"client=ussd_client&secret_key=asfn349nasd8sdf9"
    //$header = array('Content-Type: application/x-www-form-urlencoded');
    $payload = GlicoConfig::$Payload;
    $token = $this->authToken($payload);
    $hdr = array('Authorization: Bearer ' . $token,'Content-Type: application/x-www-form-urlencoded');
    //$payload = array('client'=>'ussd_client','secret_key'='asfn349nasd8sdf9');
    
    $url = GlicoConfig::Validate_Policy_API_URL;
    $validate=$this->post_1($url , array('policy_no'=>$PolicyNumber), $hdr);
    $decode=json_decode($validate,true);
if($decode['success'] == 'true'){
    $this->displayText = "Please Enter the Amount((Premium): \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "approve";
    $this->previousPage = "InsurancePolicy";
}else{
    $this->displayText = "Policy Number not found!\nEnter Policy Number: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "policy_number";
    $this->previousPage = "StartPage";
}
if ($input ==null){
    $this->displayText = "Policy Number not Provided.\nPlease Enter the Policy Number: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "policy_number";
    $this->previousPage = "StartPage";
}else if ($input ==0){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState ="END";
}
}


function approve($input){
    $this->saveSessionVar('amount', $input);
    $Amount=$this->getSessionVar('amount');
if (!preg_match('/^[0-9]+$/',$Amount)){
    $this->displayText = "Invalid Amount!\nPlease Enter the Amount(Premium): \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "policy_checkout";
    $this->previousPage = "InsurancePolicy";
}else{
    $this->displayText = "You are about to pay GHS $Amount\n1. Proceed\n" . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "policy_checkout";
    $this->previousPage = "InsurancePolicy";
}
if ($input ==null){
    $this->displayText = "Amount not entered!\nPlease Enter the Amount((Premium): \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "approve";
    $this->previousPage = "InsurancePolicy";
}else if ($input ==0){
    $this->displayText = "  Enter Policy Number: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "policy_number";
    $this->previousPage = "StartPage";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Pay Premium');
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState ="END";
}
}


function end($input){
if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function auto_deduct($input){
if ($input ==null){
    $message = "Please Select Option:\n ";
    $this->displayText ="$message\n1.Momo\n2.Bank \n" . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "auto_deduct";
    $this->previousPage = "StartPage";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Auto Deduct');
}else if ($input ==1){
    $this->displayText = " Enter Momo Number: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "policy";
    $this->previousPage = "InsurancePolicy";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Momo');
}else if ($input ==2){
    $this->displayText = "Please use this link: https://glicolife.com/online-banks/ \n" . $this->myterminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "end";
    $this->previousPage = "InsurancePolicy";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Bank');
}else if ($input ==0){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function policy($input){
    $this->saveSessionVar('momo', $input);
    $Momo=$this->getSessionVar('momo');
    $this->displayText = " Enter Policy Number: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "auto_amount";
    $this->previousPage = "InsurancePolicy";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Momo Number is: ".$Momo);
if ($input ==null){
    $this->displayText = "No value entered!\n Enter Momo Number: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "policy";
    $this->previousPage = "InsurancePolicy";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Momo');
}elseif ($input ==0){
    $message = "Please Select Option:\n ";
    $this->displayText ="$message\n1.Momo\n2.Bank \n" . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "auto_deduct";
    $this->previousPage = "StartPage";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Auto Deduct');
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function auto_amount($input){
    $this->saveSessionVar('policy', $input);
    $Policy=$this->getSessionVar('policy');
    $this->displayText = "Please Enter the Amount: \n" . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "thanks";
    $this->previousPage = "auto_deduct";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Policy Number is: ".$Policy);
if ($input ==null){
    $this->displayText = "Policy Number not Provided.\n Enter Policy Number: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "auto_amount";
    $this->previousPage = "InsurancePolicy";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Momo Number is: ".$Momo);
}else if ($input ==0){
    $this->displayText = " Enter Momo Number: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "policy";
    $this->previousPage = "InsurancePolicy";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Momo');
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function thanks($input){
    $this->saveSessionVar('amount', $input);
    $Amount=$this->getSessionVar('amount');
    $this->displayText = "Thank you";
    $this->sessionState = "END";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Amount is: ".$Amount);
if ($input ==null){
    $this->displayText = "No Value Provided!\nPlease Enter the Amount: \n" . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "thanks";
    $this->previousPage = "auto_deduct";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Policy Number is: ".$Policy);
}else if ($input ==0){
    $this->displayText = " Enter Policy Number: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "auto_amount";
    $this->previousPage = "InsurancePolicy";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Momo Number is: ".$Momo);
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function phone_number($input){
    $this->saveSessionVar('name', $input);
    $Name=$this->getSessionVar('name');
if (preg_match("/^([a-zA-Z' -]+)$/",$Name)){
    $this->displayText = "Please Enter your Phone Number:\n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "Date";
    $this->previousFunction = "InsurancePolicy";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Name is: ".$Name);
}else{
    $this->displayText = "Name not valid.\nPlease Enter your name: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "phone_number";
    $this->previousPage = "InsurancePolicy";
}
if ($input ==null){
    $this->displayText = "Name not entered!\nPlease Enter your name: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "phone_number";
    $this->previousPage = "StartPage";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, 'Special Promotion');
}else if ($input =='0'){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==51){
     $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function Date($input){
    $this->saveSessionVar('number', $input);
    $Number=$this->getSessionVar('number');
if (preg_match('/^[0-9]+$/',$Number)){
    $this->displayText = "Enter Date of Birth(yyyy-mm-dd) \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "special_policy";
    $this->previousFunction = "phone_number";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Number is: ".$Number);
}else{
     $this->displayText = "Invalid Phone Number.Please Enter your Phone Number: \n " . $this->terminator;
     $this->sessionState = "CONTINUE";
     $this->nextFunction = "phone_number";
     $this->previousPage = "InsurancePolicy";
}
if ($input ==null){
     $this->displayText = "No Number Entered!\nPlease Enter your Phone Number:\n " . $this->terminator;
     $this->sessionState = "CONTINUE";
     $this->nextFunction = "Date";
     $this->previousFunction = "InsurancePolicy";
     $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Name is: ".$Name);
}else if ($input =='0'){
     $this->displayText = "Please Enter your name: \n " . $this->terminator;
     $this->sessionState = "CONTINUE";
     $this->nextFunction = "phone_number";
     $this->previousPage = "InsurancePolicy";
}else if ($input ==51){
     $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function special_policy($input){
   $this->saveSessionVar('date', $input);
   $Date=$this->getSessionVar('date');
   list($y, $m, $d) = explode('-', $Date);
if(checkdate($m, $d, $y)){
   $this->displayText = "  Enter Policy Number: \n " . $this->terminator;
   $this->sessionState = "CONTINUE";
   $this->nextFunction = "special_end";
   $this->previousPage = "Date";
   $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Date Of Birth is: ".$Date);
}else{
    $this->displayText = "Please enter a Valid Date!! \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "special_policy";
    $this->previousFunction = "phone_number";
}
if ($input ==null){
    $this->displayText = "No Date entered!\nEnter Date of Birth(yyyy-mm-dd) \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "special_policy";
    $this->previousFunction = "phone_number";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Number is: ".$Number);
}else if ($input =='0'){
    $this->displayText = "Please Enter your Phone Number:\n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "Date";
    $this->previousFunction = "InsurancePolicy";
}else if ($input ==51){
     $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function confirm_details($input){
    $Name=$this->getSessionVar('name');
    $Number=$this->getSessionVar('number');
    $Date=$this->getSessionVar('date');
    $this->saveSessionVar('policy', $input);
    $Policy=$this->getSessionVar('policy');
    $this->displayText = "Name: " .$Name. "\nPhone Numer: " .$Number. "\nDate of Birth: " .$Date. "\nPolicy Number: " .$Policy. "\n1. Proceed\n" . $this->myterminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "special_end";
    $this->previousFunction = "special_policy";
if ($input ==null){
    $this->displayText = "Policy Number not Provided.\nEnter Policy Number: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "confirm_details";
    $this->previousPage = "Date";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Date Of Birth is: ".$Date);
}else if ($input =='0'){
    $this->displayText = "Enter Date of Birth(yyyy-mm-dd) \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "special_policy";
    $this->previousFunction = "phone_number";
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}

function special_end($input){
    $Name=$this->getSessionVar('name');
    $Number=$this->getSessionVar('number');
    $Date=$this->getSessionVar('date');
    $Policy=$this->getSessionVar('policy');
    //"client=ussd_client&secret_key=asfn349nasd8sdf9"
    //$header = array('Content-Type: application/x-www-form-urlencoded');
    $payload = GlicoConfig::$Payload;
    $token = $this->authToken($payload);
    $hdr = array('Authorization: Bearer ' . $token,'Content-Type: application/x-www-form-urlencoded');
    //$payload = array('client'=>'ussd_client','secret_key'='asfn349nasd8sdf9');
    $url = GlicoConfig::Registration_API_URL;
    $register=$this->post_1($url ,array('name'=>$Name,'phone_number'=>$Number,'dateOfBirth'=>$Date,'policy_no'=>$Policy), $hdr);

if ($input ==1){
    $this->displayText = "Thank You!";
    $this->sessionState = "END";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Policy Number is: ".$Policy);
}else if ($input ==null){
    $this->displayText = "Name: " .$Name. "\nPhone Numer: " .$Number. "\nDate of Birth: " .$Date. "\nPolicy Number: " .$Policy. "\n1. Proceed\n" . $this->myterminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "special_end";
    $this->previousFunction = "special_policy";
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}else{
    $message = "Input not valid!";
    $this->displayText = "$message\nName: " .$Name. "\nPhone Numer: " .$Number. "\nDate of Birth: " .$Date. "\nPolicy Number: " .$Policy. "\n1. Proceed\n" . $this->myterminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "special_end";
    $this->previousFunction = "special_policy";
}
}


function product_selected1($input){
    $this->saveSessionVar('name', $input);
    $Name=$this->getSessionVar('name');
if (preg_match("/^([a-zA-Z' -]+)$/",$Name)){
    $this->displayText = "Please Enter your Phone Number:\n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "confirm_number";
    $this->previousFunction = "product_selected";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Name is: ".$Name);
}else{
    $this->displayText = "Invalid Name.\nPlease Enter your name: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "product_selected1";
    $this->previousPage = "InsurancePolicy";
}
if ($input ==null){
     $this->displayText = "Name not entered!\nPlease Enter your name: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "product_selected1";
    $this->previousPage = "InsurancePolicy";
}else if ($input =='0'){
    $this->displayText = "Please Enter the Amount(Minimum amount is GHS 50): \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "name";
    $this->previousPage = "InsurancePolicy";
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function confirm_number($input){
    $this->displayText = "Please confirm your Phone Number:\n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "dateOfBirth";
    $this->previousFunction = "product_selected";
if ($input ==null){
    $this->displayText = "Number not entered!\nPlease Enter your Phone Number:\n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "confirm_number";
    $this->previousFunction = "product_selected";
}else if ($input =='0'){
    $this->displayText = "Please Enter your Phone Number:\n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "confirm_number";
    $this->previousFunction = "product_selected";
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}

function dateOfBirth($input){
    $this->saveSessionVar('number', $input);
    $Number=$this->getSessionVar('number');
if (strlen($Number) < 10){
    $this->displayText = "Invalid Phone Number!\nPlease Enter your Phone Number:\n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "confirm_number";
    $this->previousFunction = "product_selected";
}else{
    $this->displayText = "Enter Date of Birth(yyyy-mm-dd) \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "product_checkout";
    $this->previousPage = "product_selected1";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Phone Number is: ".$Number);
}
if ($input ==null){
    $this->displayText = "Please confirm your Phone Number:\n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "dateOfBirth";
    $this->previousFunction = "product_selected";
}else if ($input ==0){
    $this->displayText = "Please Enter your name: \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "product_selected1";
    $this->previousPage = "InsurancePolicy";
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}


function product_checkout($input){
    $this->saveSessionVar('date', $input);
    $Date=$this->getSessionVar('date');
    $mail="noemail@none.com";
    $this->saveSessionVar('email', $mail);
    $Email=$this->getSessionVar('email');
    $Plancode=$this->getSessionVar('plancode');
    $Amount=$this->getSessionVar('amount');
    $Name=$this->getSessionVar('name');
    $Number=$this->getSessionVar('number');
    $extradata=json_encode(array('plan_code'=>$Plancode,'client_name'=>$Name,'mobile_no'=>$Number,'dob'=>$Date,'email'=>$Email));
    $this->saveSessionVar('extradata', $extradata);
    $id= GlicoConfig::BUY_POLICY_SERVICE_ID;
    $this->saveSessionVar('id', $id);
    $ServiceID=$this->getSessionVar('id');
    $servicecode= GlicoConfig::BUY_POLICY_SERVICE_CODE;
    $this->saveSessionVar('service', $servicecode);
    $SERVICE=$this->getSessionVar('service');
    $Date=$this->getSessionVar('date');
    list($y, $m, $d) = explode('-', $Date);
    if(checkdate($m, $d, $y)){
    $result = GlicoConfig::getMobileMoneyOptions();
    $message = $this->formMessage($result);
    $this->displayText = "Please select payment method:\n\n". $message ." \n ";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "processmomo";
    $this->previousPage = "dateOfBirth";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Date is: ".$Date);
}else{
    $this->displayText = "Please enter a Valid Date!! \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "product_checkout";
    $this->previousPage = "dateOfBirth";
}
if ($input ==null){
    $this->displayText = "Date not Entered!\nEnter Date of Birth(yyyy-mm-dd) \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "product_checkout";
    $this->previousPage = "product_selected1";
}else if ($input =='0'){
    $this->displayText = "Please Enter your Phone Number:\n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "confirm_number";
    $this->previousFunction = "product_selected";
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}
}    


function policy_checkout($input){
    $Amount=$this->getSessionVar('amount');
    $PolicyNumber=$this->getSessionVar('policyNumber');
    $extradata=json_encode(array('policy_no'=>$PolicyNumber));
    $this->saveSessionVar('extradata', $extradata);
    $id= GlicoConfig::PAY_PREMIUM_SERVICE_ID;
    $this->saveSessionVar('id', $id);
    $ServiceID=$this->getSessionVar('id');
    $servicecode= GlicoConfig::PAY_PREMIUM_SERVICE_CODE;
    $this->saveSessionVar('service', $servicecode);
    $SERVICE=$this->getSessionVar('service');
if ($input ==1){
    $result = GlicoConfig::getMobileMoneyOptions();
    $message = $this->formMessage($result);
    $this->displayText = "Please select payment method:\n\n" .  $message ." \n " . $this->home;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "processmomo";
    $this->previousPage = "policy_number";
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Amount is: ".$Amount);
}else if ($input ==null){
    $this->displayText = "Nothing entered. Please enter 1 to proceed! \n " . $this->terminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "policy_checkout";
    $this->previousPage = "InsurancePolicy";
}else if ($input ==0){
     $this->displayText = "Please Enter the amount \n " . $this->terminator;
     $this->sessionState = "CONTINUE";
     $this->nextFunction = "approve";
     $this->previousPage = "InsurancePolicy";
}else if ($input ==51){
    $message = "Welcome To Glico Life \n";
    $this->displayText = "$message\n1.Buy An Insurance Policy\n2.Pay Premium\n3.Auto Deduct Setup\n4.Special Promotion\n5.Check Your Statement\n6.Initiate A Claim\n7.Contact us";
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "InsurancePolicy";
}else if ($input ==52){
    $this->displayText = "Thank you for using this service";
    $this->sessionState = "END";
}else{
    $this->displayText = "Invalid input";
}
}


function processmomo($input){
    if ($input < 6){
      switch ($input) {
        case 1:
             $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Procesing MTN payment option");
             $url = GlicoConfig::MTNMOMOAPI_URL;
             $mno = GlicoConfig::MTN;
             $payerClientID = GlicoConfig::MTNCLIENT_ID;
             $checkouttype = GlicoConfig::USSD_PUSH;
             break;
        case 2:
              $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Procesing TIGO payment option");
              $url = GlicoConfig::TIGOMOMOAPI_URL;
              $mno = GlicoConfig::TIGO;
              $payerClientID = GlicoConfig::TIGOCLIENT_ID;
              $checkouttype = GlicoConfig::STK_LAUNCH;
              break;
       case 3:
             $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Procesing AIRTEL payment option");
             $url = GlicoConfig::AIRTELMOMOAPI_URL;
             $mno = GlicoConfig::AIRTEL;
             $payerClientID = GlicoConfig::AIRTELCLIENT_ID;
             $checkouttype = GlicoConfig::STK_LAUNCH;
             break;
       case 4:
             $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Procesing VODAFONE payment option");
             $url = GlicoConfig::VODAFONEMOMOAPI_URL;
             $mno = GlicoConfig::VODAFONE;
             $payerClientID = GlicoConfig::VODAFONECLIENT_ID;
             $checkouttype = GlicoConfig::STK_LAUNCH;
             break;
       default:
             $this->InvalidEntry($input);
             }
 
             $this->saveSessionVar("mno", $mno);
             $this->saveSessionVar("url", $url);
             $this->saveSessionVar("payerClientID", $payerClientID);
             $this->saveSessionVar("checkouttype", $checkouttype);
 
         }
if ($payerClientID == GlicoConfig::VODAFONECLIENT_ID){
    $this->displayText = "Enter Voucher code received for *110# \n" . $this->myterminator;
    $this->sessionState = "CONTINUE";
    $this->nextFunction = "getvodafonevouchercode";
    $this->previousPage = "processMoMo";
}else{
    $this->saveSessionVar("voucherCode", $input);
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Proceeding to invoke MoMo API");

    $response = $this->invokeMoMoAPI();
}
if ($response) {
     $this->displayText = "Dear Customer you will receive instructions to approve the payment \n";
     $this->sessionState = "END";
     $this->previousPage = "processmomo";
} else {
     $this->displayText = "Sorry a problem occured while processing your request.\n";
     $this->sessionState = "END";
     $this->previousPage = "StartPage";
}
}


function invokeMomoAPI(){
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Inside invokeMoMoAPI");
    $customer = $this->getSessionVar("customer");
    $mno = $this->getSessionVar("mno");
    $url = $this->getSessionVar("url");
    $payerClientID = $this->getSessionVar("payerClientID");
    $Amount = $this->getSessionVar("amount");
    $voucherCode = $this->getSessionVar("voucherCode");
    $checkouttype = $this->getSessionVar("checkouttype");
    $SERVICE=$this->getSessionVar('service');
    $ServiceID=$this->getSessionVar('id');
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Customer variable:".print_r($customer));
 
 $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Processing ". $mno ." payment option");
 
 $extrajson=$this->getSessionVar('extradata');
 
 $headers = array ('content-type: application/json');


 $paymentData = array(
    "MSISDN" => $this->_msisdn,
    "ACCOUNT_NUMBER" => $this->_msisdn,
    "AMOUNT" => $Amount,
    "PRINCIPAL_AMOUNT" => $Amount,
    "CHARGE_AMOUNT" => $Amount,
    "CHARGE_RATE" => GlicoConfig::CHARGE_RATE,
    "CURRENCY" => GlicoConfig::currencyCode,
    "PAYER_CLIENT_ID" => $payerClientID,
    "ACCESS_POINT" => GlicoConfig::ACCESS_POINT,
    "SERVICE_CODE" => $SERVICE,
    "CHECKOUT_TYPE" => $checkouttype,
    "SERVICE_ID" => $ServiceID,
    "EXTRA_DATA" => $extrajson,
    "CALLBACK_DATA" => "",
    "SERVICE" => GlicoConfig::CHECKOUT_SERVICE_CODE,
    "REQUEST_MODE" => GlicoConfig::CHECKOUT_ASYNC_MODE,
    "ORIGIN" => GlicoConfig::REQUEST_ORIGIN,
    "UUID" => self::generateUUID(),
    "CLIENT_ID" => GlicoConfig::MULA_PROXY_CLIENT_ID
      );

$this->log->debug(GlicoConfig::INFO, $this->_msisdn, "sending request to ".$mno." MOMO API");
$this->log->debug(GlicoConfig::INFO, $this->_msisdn, "sending data\n".json_encode($paymentData));

$data =$this->post($url,$paymentData,$headers);
      $res = json_decode($data,true);

        if ($res['STATUS_CODE'] == GlicoConfig::MOMOAPI_SUCCESS)
        {
           return true;
        } else {
         return false;
        }
}


/**
     * checkout
     *
     * @return void
     */
public function post ($url , $payload , $headers)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_PORT => "9001",
    CURLOPT_URL => $url,
    CURLOPT_VERBOSE => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => $headers,
));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
if ($err) {
    // echo "cURL Error #:" . $err;
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Error sending request ".$err."\n");
} else {
    //echo $response;
    $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "response from server ".json_encode($response));
}
    return $response;
}


/**
     * authentication
     *
     * @return bearer token
     */
function authToken($payload){
$url=GlicoConfig::Authentication_API_URL;
$header= GlicoConfig::$Head;
        
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => http_build_query($payload),//"client=ussd_client&secret_key=asfn349nasd8sdf9",
    CURLOPT_HTTPHEADER => $header
));

$response = curl_exec($curl);
$this->log->debug(GlicoConfig::INFO, $this->_msisdn, "sending request to authentication api" . http_build_query($payload));
curl_close($curl);

 $auth_array = json_decode($response, true);
 $bearer_token = $auth_array['access_token'];
 //echo $bearer_token;
 $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "bearer token" . $bearer_token);
// return $bearer_token;
$this->log->debug(GlicoConfig::INFO, $this->_msisdn, "response from authentication api ". json_encode($response));
return $bearer_token;
}


function post_1($url , $payload , $headers){
$curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_HTTPHEADER => $headers,
));
   
   $response = curl_exec($curl);
   $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "sending policy request ".http_build_query($payload));
   $err = curl_error($curl);
   curl_close($curl);
   
if ($err) {
   // echo "cURL Error #:" . $err;
   $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "Error sending request ".$err."\n");
} else {
   //echo $response;
   $this->log->debug(GlicoConfig::INFO, $this->_msisdn, "response from policy ".json_encode($response));
}
   return $response;
   
}

function formMessage($ResponseArray) {
    $del_val = "Glico Insurance";
    $message = "";
    for ($i = 0; $i < count($ResponseArray); $i++) {
        $current = $i + 1;
        if ($ResponseArray[$i] == "Glico Insurance") {

        } else {
            $message .= $current . "." . $ResponseArray[$i] . "\n";
        }
    }
    return $message;
}


private static function generateUUID()
{
     return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
     // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
     // 16 bits for "time_mid"
      mt_rand(0, 0xffff),
     // 16 bits for "time_hi_and_version",
     // four most significant bits holds version number 4
     mt_rand(0, 0x0fff) | 0x4000,
     // 16 bits, 8 bits for "clk_seq_hi_res",
     // 8 bits for "clk_seq_low",
     // two most significant bits holds zero and one for variant DCE1.1
     mt_rand(0, 0x3fff) | 0x8000,
    // 48 bits for "node"
     mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
  );
}


}


$menu = new Glico_life();

echo $menu->navigate();
?>
<?php
    /* from http://halls-of-valhalla.org */
    session_start();
     
    /*
    To use:
    1. Include this file
    2. Add getTokenForURL() to URLs with GET parameters
    3. Add getTokenForm() to forms
    4. When validating GET and POST data, execute validateCSRFToken().
    */
     
    if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])){header('Location: index.php');} //makes this file include-only by checking if the requested file is the same as this file
     
    function getCSRFToken(){
    if(!isset($_SESSION['token']) or empty($_SESSION['token']))
    $_SESSION['token'] = generateCSRFToken();
    return $_SESSION['token'];
    }
     
    function generateCSRFToken(){
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    }
     
    function validateCSRFToken(){
    if(!isValidToken()){
    actionOnInvalid();
    }
    }
     
    function isValidToken(){
    if(isset($_POST['token'])) return getCSRFToken()===$_POST['token'];
    if(isset($_GET['token'])) return getCSRFToken()===$_GET['token'];
    return false;
    }
     
    function actionOnInvalid(){
    header('Location: /images/NiceTry.png');
    exit();
    }
     
    function getTokenForm(){
    $token = getCSRFToken();
    return "<input type='hidden' name='token' value='$token'/>";
    }
     
    function getTokenForURL(){
    $token = getCSRFToken();
    return "&amp;token=$token";
    }
     
?>
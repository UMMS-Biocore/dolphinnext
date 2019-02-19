<?php
$secRaw = parse_ini_file(".sec", true);
$sec = $secRaw['Dolphinnext'];
if (!empty($sec)){
    define('DB', $sec['DB']);
    define('DBUSER', $sec['DBUSER']);
    define('DBPASS', $sec['DBPASS']);
    define('DBHOST', $sec['DBHOST']);
    define('DBPORT', $sec['DBPORT']);
    define('SSHPATH', $sec['SSHPATH']);
    define('AMAZON', $sec['AMAZON']);
    define('AMZPATH', $sec['AMZPATH']);  
    define('SALT', $sec['SALT']);  
    define('PEPPER', $sec['PEPPER']);  
    define('MASTER', $sec['MASTER']);  
    define('VERIFY', $sec['VERIFY']);  
}
$secConf = $secRaw['CONFIG'];
if (!empty($secConf)){
    $DEBROWSER_URL = isset($secConf['DEBROWSER_URL']) ? $secConf['DEBROWSER_URL'] : "";
    $OCPU_URL = isset($secConf['OCPU_URL']) ? $secConf['OCPU_URL'] : "";
    $OCPU_PUBWEB_URL= isset($secConf['OCPU_PUBWEB_URL']) ? $secConf['OCPU_PUBWEB_URL'] : "";
    $PUBWEB_URL= isset($secConf['PUBWEB_URL']) ? $secConf['PUBWEB_URL'] : "";
    $NEXTFLOW_VERSION = isset($secConf['NEXTFLOW_VERSION']) ? $secConf['NEXTFLOW_VERSION'] : "";
    $LDAP_SERVER= isset($secConf['LDAP_SERVER']) ? $secConf['LDAP_SERVER'] : "";
    $DN_STRING= isset($secConf['DN_STRING']) ? $secConf['DN_STRING'] : "";
    $BIND_USER= isset($secConf['BIND_USER']) ? $secConf['BIND_USER'] : "";
    $BIND_PASS= isset($secConf['BIND_PASS']) ? $secConf['BIND_PASS'] : "";
    $EMAIL_SENDER= isset($secConf['EMAIL_SENDER']) ? $secConf['EMAIL_SENDER'] : "";
    $EMAIL_ADMIN= isset($secConf['EMAIL_ADMIN']) ? $secConf['EMAIL_ADMIN'] : "";
    
    date_default_timezone_set($secConf['TIMEZONE']);
    define('RUNPATH', $secConf['RUNPATH']);
    define('TEMPPATH', $secConf['TEMPPATH']);
    define('API_PATH', $secConf['API_PATH']);
    define('BASE_PATH', $secConf['BASE_PATH']);
    define('OCPU_URL', $OCPU_URL);
    define('DEBROWSER_URL', $DEBROWSER_URL);
    define('OCPU_PUBWEB_URL', $OCPU_PUBWEB_URL);
    define('PUBWEB_URL', $PUBWEB_URL);
    define('NEXTFLOW_VERSION', $NEXTFLOW_VERSION);
    define('LDAP_SERVER', $LDAP_SERVER);
    define('DN_STRING', $DN_STRING);
    define('BIND_USER', $BIND_USER);
    define('BIND_PASS', $BIND_PASS);
    define('EMAIL_SENDER', $EMAIL_SENDER);
    define('EMAIL_ADMIN', $EMAIL_ADMIN);
}
$secUiconfig = $secRaw['UICONFIG'];
if (!empty($secUiconfig)){
    $SHOW_AMAZON_KEYS= isset($secUiconfig['SHOW_AMAZON_KEYS']) ? $secUiconfig['SHOW_AMAZON_KEYS'] : "true";
    $SHOW_SSH_KEYS= isset($secUiconfig['SHOW_SSH_KEYS']) ? $secUiconfig['SHOW_SSH_KEYS'] : "true";
    $SHOW_GROUPS= isset($secUiconfig['SHOW_GROUPS']) ? $secUiconfig['SHOW_GROUPS'] : "true";
    $COMPANY_NAME= isset($secUiconfig['COMPANY_NAME']) ? $secUiconfig['COMPANY_NAME'] : "";
    $ALLOW_SIGNUP= isset($secUiconfig['ALLOW_SIGNUP']) ? $secUiconfig['ALLOW_SIGNUP'] : "true";
    $ALLOW_SIGNUPGOOGLE= isset($secUiconfig['ALLOW_SIGNUPGOOGLE']) ? $secUiconfig['ALLOW_SIGNUPGOOGLE'] : "true";
    
    define('SHOW_AMAZON_KEYS', $SHOW_AMAZON_KEYS);
    define('SHOW_SSH_KEYS', $SHOW_SSH_KEYS);
    define('SHOW_GROUPS', $SHOW_GROUPS);
    define('COMPANY_NAME', $COMPANY_NAME);
    define('ALLOW_SIGNUP', $ALLOW_SIGNUP);
    define('ALLOW_SIGNUPGOOGLE', $ALLOW_SIGNUPGOOGLE);
}
?>

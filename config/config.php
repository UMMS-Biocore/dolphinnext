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
    date_default_timezone_set($secConf['TIMEZONE']);
    define('RUNPATH', $secConf['RUNPATH']);
    define('TEMPPATH', $secConf['TEMPPATH']);
    define('API_PATH', $secConf['API_PATH']);
    define('BASE_PATH', $secConf['BASE_PATH']);
    define('OCPU_URL', $secConf['OCPU_URL']);
    define('DEBROWSER_URL', $secConf['DEBROWSER_URL']);
    define('OCPU_PUBWEB_URL', $secConf['OCPU_PUBWEB_URL']);
    define('PUBWEB_URL', $secConf['PUBWEB_URL']);
    define('LDAP_SERVER', $secConf['LDAP_SERVER']);
    define('DN_STRING', $secConf['DN_STRING']);
    define('BIND_USER', $secConf['BIND_USER']);
    define('BIND_PASS', $secConf['BIND_PASS']);
    define('EMAIL_SENDER', $secConf['EMAIL_SENDER']);
    define('EMAIL_ADMIN', $secConf['EMAIL_ADMIN']);
}
$secUiconfig = $secRaw['UICONFIG'];
if (!empty($secUiconfig)){
    define('SHOW_AMAZON_KEYS', $secUiconfig['SHOW_AMAZON_KEYS']);
    define('SHOW_SSH_KEYS', $secUiconfig['SHOW_SSH_KEYS']);
    define('SHOW_GROUPS', $secUiconfig['SHOW_GROUPS']);
    define('COMPANY_NAME', $secUiconfig['COMPANY_NAME']);
    define('ALLOW_SIGNUP', $secUiconfig['ALLOW_SIGNUP']);
    define('ALLOW_SIGNUPGOOGLE', $secUiconfig['ALLOW_SIGNUPGOOGLE']);
}
?>

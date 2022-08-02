<?php
$secRaw = parse_ini_file(".sec", true);
$sec = $secRaw['Dolphinnext'];
if (!empty($sec)) {
    define('DB', $sec['DB']);
    define('DBUSER', $sec['DBUSER']);
    define('DBPASS', $sec['DBPASS']);
    define('DBHOST', $sec['DBHOST']);
    define('DBPORT', $sec['DBPORT']);
    define('SSHPATH', $sec['SSHPATH']);
    define('AMAZON', $sec['AMAZON']);
    define('AMZPATH', $sec['AMZPATH']);
    $GOOGPATH = isset($sec['GOOGPATH']) ? $sec['GOOGPATH'] : "";
    define('GOOGPATH', $GOOGPATH);
    define('SALT', $sec['SALT']);
    define('PEPPER', $sec['PEPPER']);
    define('MASTER', $sec['MASTER']);
    define('VERIFY', $sec['VERIFY']);
    $JWT_SECRET = isset($sec['JWT_SECRET']) ? $sec['JWT_SECRET'] : "";
    $JWT_COOKIE_EXPIRES_IN = isset($sec['JWT_COOKIE_EXPIRES_IN']) ? $sec['JWT_COOKIE_EXPIRES_IN'] : 365;
    define('JWT_SECRET', $JWT_SECRET);
    define('JWT_COOKIE_EXPIRES_IN', $JWT_COOKIE_EXPIRES_IN);
}
$secConf = $secRaw['CONFIG'];
if (!empty($secConf)) {
    $CENTRAL_API_URL = "https://www.dolphinnext.com";
    $MOUNTED_VOLUME = isset($secConf['MOUNTED_VOLUME']) ? $secConf['MOUNTED_VOLUME'] : "";
    $DEBROWSER_URL = isset($secConf['DEBROWSER_URL']) ? $secConf['DEBROWSER_URL'] : "";
    $OCPU_URL = isset($secConf['OCPU_URL']) ? $secConf['OCPU_URL'] : "";
    $OCPU_PUBWEB_URL = isset($secConf['OCPU_PUBWEB_URL']) ? $secConf['OCPU_PUBWEB_URL'] : "";
    $PUBWEB_URL = isset($secConf['PUBWEB_URL']) ? $secConf['PUBWEB_URL'] : "";
    $NEXTFLOW_VERSION = isset($secConf['NEXTFLOW_VERSION']) ? $secConf['NEXTFLOW_VERSION'] : "";
    $LDAP_SERVER = isset($secConf['LDAP_SERVER']) ? $secConf['LDAP_SERVER'] : "";
    $DN_STRING = isset($secConf['DN_STRING']) ? $secConf['DN_STRING'] : "";
    $BIND_USER = isset($secConf['BIND_USER']) ? $secConf['BIND_USER'] : "";
    $BIND_PASS = isset($secConf['BIND_PASS']) ? $secConf['BIND_PASS'] : "";
    $EMAIL_SENDER = isset($secConf['EMAIL_SENDER']) ? $secConf['EMAIL_SENDER'] : "";
    $EMAIL_ADMIN = isset($secConf['EMAIL_ADMIN']) ? $secConf['EMAIL_ADMIN'] : "";
    $EMAIL_BODY_ADMIN = isset($secConf['EMAIL_BODY_ADMIN']) ? $secConf['EMAIL_BODY_ADMIN'] : $EMAIL_ADMIN;
    $ENV_PATH = isset($secConf['ENV_PATH']) ? $secConf['ENV_PATH'] : "";
    $TIMEZONE = isset($secConf['TIMEZONE']) ? $secConf['TIMEZONE'] : "";
    $DEFAULT_GROUP_ID = isset($secConf['DEFAULT_GROUP_ID']) ? $secConf['DEFAULT_GROUP_ID'] : "";
    $DEFAULT_RUN_ENVIRONMENT = isset($secConf['DEFAULT_RUN_ENVIRONMENT']) ? $secConf['DEFAULT_RUN_ENVIRONMENT'] : "";
    date_default_timezone_set($TIMEZONE);
    $INITIAL_RUN_DOCKER = isset($secConf['INITIAL_RUN_DOCKER']) ? $secConf['INITIAL_RUN_DOCKER'] : "ummsbiocore/initialrun-docker:1.0";
    $INITIAL_RUN_SINGULARITY = isset($secConf['INITIAL_RUN_SINGULARITY']) ? $secConf['INITIAL_RUN_SINGULARITY'] : "https://galaxyweb.umassmed.edu/pub/dolphinnext_singularity/UMMS-Biocore-initialrun-09.06.2020.simg";
    $EMAIL_TYPE = isset($secConf['EMAIL_TYPE']) ? $secConf['EMAIL_TYPE'] : "DEFAULT";
    $EMAIL_URL = isset($secConf['EMAIL_URL']) ? $secConf['EMAIL_URL'] : "";
    $EMAIL_HEADER_KEY = isset($secConf['EMAIL_HEADER_KEY']) ? $secConf['EMAIL_HEADER_KEY'] : "";
    $EMAIL_HEADER_VALUE = isset($secConf['EMAIL_HEADER_VALUE']) ? $secConf['EMAIL_HEADER_VALUE'] : "";
    $EMAIL_HOST = isset($secConf['EMAIL_HOST']) ? $secConf['EMAIL_HOST'] : "";
    $EMAIL_USERNAME = isset($secConf['EMAIL_USERNAME']) ? $secConf['EMAIL_USERNAME'] : "";
    $EMAIL_PASSWORD = isset($secConf['EMAIL_PASSWORD']) ? $secConf['EMAIL_PASSWORD'] : "";
    $EMAIL_PORT = isset($secConf['EMAIL_PORT']) ? $secConf['EMAIL_PORT'] : "";

    define('TIMEZONE', $TIMEZONE);
    define('MOUNTED_VOLUME', $MOUNTED_VOLUME);
    define('RUNPATH', $secConf['RUNPATH']);
    define('TEMPPATH', $secConf['TEMPPATH']);
    define('API_URL', $secConf['API_URL']);
    define('CENTRAL_API_URL', $CENTRAL_API_URL);
    define('ENV_PATH', $ENV_PATH);
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
    define('EMAIL_BODY_ADMIN', $EMAIL_BODY_ADMIN);
    define('DEFAULT_GROUP_ID', $DEFAULT_GROUP_ID);
    define('DEFAULT_RUN_ENVIRONMENT', $DEFAULT_RUN_ENVIRONMENT);
    define('INITIAL_RUN_DOCKER', $INITIAL_RUN_DOCKER);
    define('INITIAL_RUN_SINGULARITY', $INITIAL_RUN_SINGULARITY);
    define('EMAIL_TYPE', $EMAIL_TYPE);
    define('EMAIL_URL', $EMAIL_URL);
    define('EMAIL_HEADER_KEY', $EMAIL_HEADER_KEY);
    define('EMAIL_HEADER_VALUE', $EMAIL_HEADER_VALUE);
    define('EMAIL_HOST', $EMAIL_HOST);
    define('EMAIL_USERNAME', $EMAIL_USERNAME);
    define('EMAIL_PASSWORD', $EMAIL_PASSWORD);
    define('EMAIL_PORT', $EMAIL_PORT);
}
$secUiconfig = $secRaw['UICONFIG'];
if (!empty($secUiconfig)) {
    $SHOW_APPS = isset($secUiconfig['SHOW_APPS']) ? $secUiconfig['SHOW_APPS'] : false;
    $SHOW_AMAZON_KEYS = isset($secUiconfig['SHOW_AMAZON_KEYS']) ? $secUiconfig['SHOW_AMAZON_KEYS'] : "true";
    $SHOW_GOOGLE_KEYS = isset($secUiconfig['SHOW_GOOGLE_KEYS']) ? $secUiconfig['SHOW_GOOGLE_KEYS'] : "true";
    $SHOW_SSH_KEYS = isset($secUiconfig['SHOW_SSH_KEYS']) ? $secUiconfig['SHOW_SSH_KEYS'] : "true";
    $SHOW_GITHUB = isset($secUiconfig['SHOW_GITHUB']) ? $secUiconfig['SHOW_GITHUB'] : "true";
    $SHOW_GROUPS = isset($secUiconfig['SHOW_GROUPS']) ? $secUiconfig['SHOW_GROUPS'] : "true";
    $COMPANY_NAME = isset($secUiconfig['COMPANY_NAME']) ? $secUiconfig['COMPANY_NAME'] : "";
    $ALLOW_SIGNUP = isset($secUiconfig['ALLOW_SIGNUP']) ? $secUiconfig['ALLOW_SIGNUP'] : "true";
    $ALLOW_SIGNUPGOOGLE = isset($secUiconfig['ALLOW_SIGNUPGOOGLE']) ? $secUiconfig['ALLOW_SIGNUPGOOGLE'] : "true";
    $PASSWORD_LOGIN = isset($secUiconfig['PASSWORD_LOGIN']) ? $secUiconfig['PASSWORD_LOGIN'] : "true";
    $GOOGLE_CLIENT_ID = isset($secUiconfig['GOOGLE_CLIENT_ID']) ? $secUiconfig['GOOGLE_CLIENT_ID'] : "";
    define('SHOW_APPS', $SHOW_APPS);
    define('SHOW_AMAZON_KEYS', $SHOW_AMAZON_KEYS);
    define('SHOW_GOOGLE_KEYS', $SHOW_GOOGLE_KEYS);
    define('SHOW_SSH_KEYS', $SHOW_SSH_KEYS);
    define('SHOW_GITHUB', $SHOW_GITHUB);
    define('SHOW_GROUPS', $SHOW_GROUPS);
    define('COMPANY_NAME', $COMPANY_NAME);
    define('ALLOW_SIGNUP', $ALLOW_SIGNUP);
    define('ALLOW_SIGNUPGOOGLE', $ALLOW_SIGNUPGOOGLE);
    define('GOOGLE_CLIENT_ID', $GOOGLE_CLIENT_ID);
    define('PASSWORD_LOGIN', $PASSWORD_LOGIN);
    $SHOW_RUN_LOG = isset($secUiconfig['SHOW_RUN_LOG']) ? $secUiconfig['SHOW_RUN_LOG'] : "true";
    $SHOW_RUN_TIMELINE = isset($secUiconfig['SHOW_RUN_TIMELINE']) ? $secUiconfig['SHOW_RUN_TIMELINE'] : "true";
    $SHOW_RUN_REPORT = isset($secUiconfig['SHOW_RUN_REPORT']) ? $secUiconfig['SHOW_RUN_REPORT'] : "true";
    $SHOW_RUN_DAG = isset($secUiconfig['SHOW_RUN_DAG']) ? $secUiconfig['SHOW_RUN_DAG'] : "true";
    $SHOW_RUN_TRACE = isset($secUiconfig['SHOW_RUN_TRACE']) ? $secUiconfig['SHOW_RUN_TRACE'] : "true";
    $SHOW_RUN_NEXTFLOWLOG = isset($secUiconfig['SHOW_RUN_NEXTFLOWLOG']) ? $secUiconfig['SHOW_RUN_NEXTFLOWLOG'] : "true";
    $SHOW_RUN_NEXTFLOWNF = isset($secUiconfig['SHOW_RUN_NEXTFLOWNF']) ? $secUiconfig['SHOW_RUN_NEXTFLOWNF'] : "true";
    $SHOW_RUN_NEXTFLOWCONFIG = isset($secUiconfig['SHOW_RUN_NEXTFLOWCONFIG']) ? $secUiconfig['SHOW_RUN_NEXTFLOWCONFIG'] : "true";
    $SHOW_HOMEPAGE = isset($secUiconfig['SHOW_HOMEPAGE']) ? $secUiconfig['SHOW_HOMEPAGE'] : "1";
    $CUSTOM_HELP_MESSAGE = isset($secUiconfig['CUSTOM_HELP_MESSAGE']) ? $secUiconfig['CUSTOM_HELP_MESSAGE'] : "";
    $CUSTOM_FILE_LOCATION_MESSAGE = isset($secUiconfig['CUSTOM_FILE_LOCATION_MESSAGE']) ? $secUiconfig['CUSTOM_FILE_LOCATION_MESSAGE'] : "";
    define('SHOW_RUN_LOG', $SHOW_RUN_LOG);
    define('SHOW_RUN_TIMELINE', $SHOW_RUN_TIMELINE);
    define('SHOW_RUN_REPORT', $SHOW_RUN_REPORT);
    define('SHOW_RUN_DAG', $SHOW_RUN_DAG);
    define('SHOW_RUN_TRACE', $SHOW_RUN_TRACE);
    define('SHOW_RUN_NEXTFLOWLOG', $SHOW_RUN_NEXTFLOWLOG);
    define('SHOW_RUN_NEXTFLOWNF', $SHOW_RUN_NEXTFLOWNF);
    define('SHOW_RUN_NEXTFLOWCONFIG', $SHOW_RUN_NEXTFLOWCONFIG);
    define('SHOW_HOMEPAGE', $SHOW_HOMEPAGE);
    define('CUSTOM_FILE_LOCATION_MESSAGE', $CUSTOM_FILE_LOCATION_MESSAGE);
    define('CUSTOM_HELP_MESSAGE', $CUSTOM_HELP_MESSAGE);

    //  WIZARD CONFIG
    $SHOW_WIZARD = isset($secUiconfig['SHOW_WIZARD']) ? $secUiconfig['SHOW_WIZARD'] : false;
    $SHOW_TEST_PROFILE = isset($secUiconfig['SHOW_TEST_PROFILE']) ? $secUiconfig['SHOW_TEST_PROFILE'] : false;
    $TEST_PROFILE_GROUP_ID = isset($secUiconfig['TEST_PROFILE_GROUP_ID']) ? $secUiconfig['TEST_PROFILE_GROUP_ID'] : "";
    define('SHOW_WIZARD', $SHOW_WIZARD);
    define('SHOW_TEST_PROFILE', $SHOW_TEST_PROFILE);
    define('TEST_PROFILE_GROUP_ID', $TEST_PROFILE_GROUP_ID);
}
// SSO Config:
$secSSOconfig = isset($secRaw['SSOCONFIG']) ? $secRaw['SSOCONFIG'] : "";
$secOKTAconfig = isset($secRaw['OKTACONFIG']) ? $secRaw['OKTACONFIG'] : "";
$SSO_LOGIN = false;
$SSO_URL = false;
$ISSUER = false;
$CLIENT_ID = false;
$CLIENT_SECRET = false;
$OKTA_API_TOKEN = false;
$OKTA_METADATA = false;
$OKTA_METHOD = false;
if (!empty($secSSOconfig) && $secSSOconfig['SSO_LOGIN'] == true) {
    $SSO_LOGIN = isset($secSSOconfig['SSO_LOGIN']) ? $secSSOconfig['SSO_LOGIN'] : $SSO_LOGIN;
    $SSO_URL = isset($secSSOconfig['SSO_URL']) ? $secSSOconfig['SSO_URL'] : $SSO_URL;
    $CLIENT_ID = isset($secSSOconfig['CLIENT_ID']) ? $secSSOconfig['CLIENT_ID'] : $CLIENT_ID;
    $CLIENT_SECRET = isset($secSSOconfig['CLIENT_SECRET']) ? $secSSOconfig['CLIENT_SECRET'] : $CLIENT_SECRET;
} else if (!empty($secOKTAconfig) && $secOKTAconfig['SSO_LOGIN'] == true) {
    $SSO_LOGIN = isset($secOKTAconfig['SSO_LOGIN']) ? $secOKTAconfig['SSO_LOGIN'] : $SSO_LOGIN;
    $ISSUER = isset($secOKTAconfig['ISSUER']) ? $secOKTAconfig['ISSUER'] : $ISSUER;
    $CLIENT_ID = isset($secOKTAconfig['CLIENT_ID']) ? $secOKTAconfig['CLIENT_ID'] : $CLIENT_ID;
    $CLIENT_SECRET = isset($secOKTAconfig['CLIENT_SECRET']) ? $secOKTAconfig['CLIENT_SECRET'] : $CLIENT_SECRET;
    $OKTA_API_TOKEN = isset($secOKTAconfig['OKTA_API_TOKEN']) ? $secOKTAconfig['OKTA_API_TOKEN'] : $OKTA_API_TOKEN;
    $OKTA_METADATA = isset($secOKTAconfig['OKTA_METADATA']) ? $secOKTAconfig['OKTA_METADATA'] : $OKTA_METADATA;
    $OKTA_METHOD = isset($secOKTAconfig['OKTA_METHOD']) ? $secOKTAconfig['OKTA_METHOD'] : $OKTA_METHOD;
}

define('SSO_LOGIN', $SSO_LOGIN);
define('SSO_URL', $SSO_URL);
define('ISSUER', $ISSUER);
define('CLIENT_ID', $CLIENT_ID);
define('CLIENT_SECRET', $CLIENT_SECRET);
define('OKTA_API_TOKEN', $OKTA_API_TOKEN);
define('OKTA_METADATA', $OKTA_METADATA);
define('OKTA_METHOD', $OKTA_METHOD);


// DMETA Config:
$secDMETAconfig = isset($secRaw['DMETACONFIG']) ? $secRaw['DMETACONFIG'] : "";
$SHOW_DMETA = false;
$DMETA_URL = false;
$DMETA_LABEL = false;
if (!empty($secDMETAconfig)) {
    $SHOW_DMETA = isset($secDMETAconfig['SHOW_DMETA']) ? $secDMETAconfig['SHOW_DMETA'] : $SHOW_DMETA;
    $DMETA_URL = isset($secDMETAconfig['DMETA_URL']) ? $secDMETAconfig['DMETA_URL'] : $DMETA_URL;
    $DMETA_LABEL = isset($secDMETAconfig['DMETA_LABEL']) ? $secDMETAconfig['DMETA_LABEL'] : $DMETA_LABEL;
}
define('SHOW_DMETA', $SHOW_DMETA);
define('DMETA_URL', $DMETA_URL);
define('DMETA_LABEL', $DMETA_LABEL);


$line = fgets(fopen(__DIR__ . "/../NEWS", 'r'));
if (!empty($line)) {
    $line = trim($line);
    $lines = explode(" ", $line);
    $DN_VERSION = end($lines);
    if (!empty($DN_VERSION)) {
        define('DN_VERSION', $DN_VERSION);
    } else {
        define('DN_VERSION', "");
    }
} else {
    define('DN_VERSION', "");
}

//source ENV_PATH
if (!empty(ENV_PATH)) {
    system(". " . ENV_PATH);
}

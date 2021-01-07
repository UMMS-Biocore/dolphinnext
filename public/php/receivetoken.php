<?php
require_once(__DIR__."/../../config/config.php");
require_once(__DIR__."/../ajax/dbfuncs.php");
require_once(__DIR__."/../api/run.php");
$db = new dbfuncs();

$Params = new Params();

class Params{
    private $BASE_PATH      = BASE_PATH;
    private $SSO_URL        = SSO_URL;
    private $CLIENT_ID      = CLIENT_ID;
    private $CLIENT_SECRET  = CLIENT_SECRET;
    public $params = null;
    /**
    * Use the query (often the requested URL) to define some settings.
    */
    public function parse_params( $params = null ) {
        // If a query has been passed to the function, turn it into an array.
        if ( is_string( $params) ) {
            $params = $this->parse_args( $params );
        }

        // If a query has not been passed to this function, just use the array of variables that
        // were passed in the URL.
        if (is_null($params)) {
            if(($_POST)){
                $this->params = $_POST;
            } else if(($_GET)){
                $this->params = $_GET;
            }
        }
        return get_object_vars( $this );
    }

    function sendTokenCookie($token) {
        /* Set cookie to last 1 year */
        setcookie('jwt-dolphinnext', $token, time()+60*60*24*365, "/");
    }

    // SSO login
    function receivetoken($params){
        error_log('**ssoReceiveToken');
        $code = $params['code'];
        $url = "{$this->SSO_URL}/api/v1/oauth/token";
        $data = json_encode(array(
            'code' => $code,
            'redirect_uri' => "{$this->BASE_PATH}/php/receivetoken.php",
            'client_id'   => $this->CLIENT_ID,
            'client_secret'   => $this->CLIENT_SECRET,
            'grant_type'   => 'authorization_code',
        ));

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        // secure it:
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $body = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if(curl_errno($curl) && $statusCode != 200){
            header("Location: {$this->BASE_PATH}");
            exit();
        }
        curl_close($curl);
        $msg = json_decode($body, true);

        if (!empty($msg["access_token"])) {
            $accessToken = $msg["access_token"];
            $refreshToken = $msg["refresh_token"];
            $expiresIn = $msg["expires_in"];
            $Run = new run();
            $currentUser = $Run->saveAccessRefreshToken($accessToken, $refreshToken, $expiresIn);

            if (empty($currentUser)){
                header("Location: {$this->BASE_PATH}");
                exit();
            }
            // create session
            $_SESSION['ownerID'] = $currentUser["id"];
            $_SESSION['role'] = $currentUser["role"];
            $_SESSION['email'] = $currentUser["email"];
            $_SESSION['username'] = $currentUser["username"];
            $_SESSION['name'] = $currentUser["name"];
            $_SESSION['accessToken'] = $currentUser["accessToken"];
            $_SESSION['refreshToken'] = $currentUser["refreshToken"];
            $this->sendTokenCookie($currentUser["accessToken"]);
            error_log("ssoLoginCheck2:");
            //$_SESSION["ssoLoginCheck"] = true when auto login check performed.
            if (empty($_SESSION["ssoLoginCheck"])){
                header("Location: {$this->BASE_PATH}/php/after-sso.php");
            } else {
                if (!empty($_SESSION["redirect_original"])){
                    header("Location: {$_SESSION["redirect_original"]}");
                } else {
                    header("Location: {$this->BASE_PATH}");
                }
            }
            exit();
        }
        error_log("sso-sign-in error occured.");
        header("Location: {$this->BASE_PATH}");
        exit();
    }
}





$result=$Params->parse_params();
$data=$Params->receivetoken($result['params']);


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
            <?php echo COMPANY_NAME?> DolphinNext Pipeline Builder</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!--   app’s client ID prodcued in the Google Developers Console-->
        <meta name="google-signin-client_id" content="1051324819082-6mjdouf9dhmhv9ov5vvdkdknqrb8tont.apps.googleusercontent.com">
        <link rel="icon" type="image/png" href="images/favicon.ico" />
    </head>
    <body class="hold-transition skin-blue fixed">
        <span id="basepathinfo" basepath="<?php echo BASE_PATH ?>"></span>
        <!--        <script type="text/javascript" src="./../js/after-sso.js"></script>-->
    </body>
</html>
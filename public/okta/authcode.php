<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require(__DIR__."/../../vendor/autoload.php");
require_once(__DIR__."/../../config/config.php");
require_once(__DIR__."/../ajax/dbfuncs.php");

class AuthCode {
    private $BASE_PATH=BASE_PATH;
    private $ISSUER=ISSUER;
    private $CLIENT_ID=CLIENT_ID;
    private $CLIENT_SECRET=CLIENT_SECRET;
    private $STATE = 'applicationState';

    function verifyJwt($jwt)
    {
        try {
            $jwtVerifier = (new \Okta\JwtVerifier\JwtVerifierBuilder())
                ->setAdaptor(new \Okta\JwtVerifier\Adaptors\FirebasePhpJwt)
                ->setIssuer($this->ISSUER)
                ->setAudience('api://default')
                ->setClientId($this->CLIENT_ID)
                ->build();

            return $jwtVerifier->verify($jwt);
        } catch (\Exception $e) {
            return false;
        }
    }

    function getProfile($access_token)
    {
        try {
            $jwtVerifier = (new \Okta\JwtVerifier\JwtVerifierBuilder())
                ->setIssuer($this->ISSUER)
                ->setAudience('api://default')
                ->setClientId($this->CLIENT_ID)
                ->build();

            $jwt = $jwtVerifier->verify($access_token);

            return $jwt->claims;
        } catch (\Exception $e) {
            return false;
        }

    }


    // use access&refresh tokens (which is delivered from SSO server) to get userINFO.
    // use userINFO to update user data in the database.
    // save tokens to database.
    // on success: return updated user Object.
    // on fail: return null
    function saveAccessRefreshToken($accessToken, $refreshToken, $expiresIn){
        $expirationDate= !empty($expiresIn) ? date('Y-m-d H:i:s', strtotime("+$expiresIn seconds")) : null;
        $dbfuncs = new dbfuncs();
        //        $this->readINI();
        // GET USER INFO
        $currentUser = $this->getProfile($accessToken);

        if (!empty($currentUser["uid"]) && !empty($currentUser["sub"])) {
            $sso_user_id = $currentUser["uid"];
            $scope = "";
            $email = $currentUser["sub"];
            $parts = explode("@", $email);
            $username = $parts[0];
            $name = $parts[0];

            $checkUserData = json_decode($dbfuncs->getUserByEmailorUsername($email));
            $db_id = isset($checkUserData[0]) ? $checkUserData[0]->{'id'} : "";
            $db_role = isset($checkUserData[0]) ? $checkUserData[0]->{'role'} : "";

            if (!empty($db_id)){
                // User exist in table 
                // Update db with recent information
                $sql = "UPDATE users SET sso_id='$sso_user_id', scope='$scope',  date_modified= now(), last_modified_user ='$db_id'  WHERE id = '$db_id'";
                $dbfuncs->runSQL($sql);
            } else if (!empty($email)){
                // insert user
                // Update db with latest information
                $email_clean = str_replace("'", "''", $email);
                $any_user_check = $dbfuncs->queryAVal("SELECT id FROM users");
                $any_user_checkAr = json_decode($any_user_check,true); 
                $role = "user";
                if (empty($any_user_checkAr)){
                    $role = "admin";
                    $db_role = "admin";
                } 
                $active = 1;
                $sql = "INSERT INTO users(name, email, username, role, active, memberdate, date_created, date_modified, perms, sso_id, scope) VALUES ('$name', '$email_clean', '$username', '$role', $active, now(),now(), now(), '3', '$sso_user_id', '$scope')";
                $inUser = $dbfuncs->insTable($sql);
                $idArray = json_decode($inUser,true);
                $db_id = $idArray["id"];
                $dbfuncs->insertDefaultGroup($db_id);               
                $dbfuncs->insertDefaultRunEnvironment($db_id);               
                
            }
            if (!empty($db_id)){
                $dbfuncs->insertAccessToken($accessToken, $expirationDate, $sso_user_id, $this->CLIENT_ID, $scope, $db_id);
                if (!empty ($refreshToken)){
                    $dbfuncs->insertRefreshToken($refreshToken, $sso_user_id, $this->CLIENT_ID, $scope, $db_id);
                }
                $currentUser["id"] =$db_id;
                $currentUser["email"] =$email;
                $currentUser["username"] =$username;
                $currentUser["name"] =$name;
                $currentUser["role"] =$db_role;
                $currentUser["sso_id"] =$sso_user_id;
                $currentUser["accessToken"] =$accessToken;
                $currentUser["refreshToken"] =$refreshToken;
                return $currentUser;
            }
        }
        return null;
    }


    function AuthCodeCallbackHandler() {

        if(array_key_exists('state', $_REQUEST) && $_REQUEST['state'] !== $this->STATE) {
            throw new \Exception('State does not match.');
        }

        if(array_key_exists('code', $_REQUEST)) {
            $exchange = $this->exchangeCode($_REQUEST['code']);
            if(!isset($exchange->access_token)) {
                die('Could not exchange code for an access token');
            }

            if($this->verifyJwt($exchange->access_token) == false) {
                die('Verification of JWT failed');
            }

            $accessToken = "$exchange->access_token";
            $refreshToken = "";
            $expiresIn = $exchange->expires_in;

            $currentUser = $this->saveAccessRefreshToken($accessToken, $refreshToken, $expiresIn);

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
            setcookie('jwt-dolphinnext', $accessToken, $expiresIn, "/", false);
            header("Location: {$this->BASE_PATH}");
        }

        die('An error during login has occurred');
    }

    function exchangeCode($code) {
        $authHeaderSecret = base64_encode( $this->CLIENT_ID . ':' . $this->CLIENT_SECRET );
        $query = http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => "{$this->BASE_PATH}/okta/authorization-code-callback.php"
        ]);
        $headers = [
            'Authorization: Basic ' . $authHeaderSecret,
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'Connection: close',
            'Content-Length: 0'
        ];
        $url = $this->ISSUER.'/v1/token?' . $query;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if(curl_error($ch)) {
            $httpcode = 500;
        }
        curl_close($ch);
        return json_decode($output);
    }

}

?>
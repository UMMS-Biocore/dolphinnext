// google sign-in
function handleCredentialResponse(response) {
    let responsePayload = jwt_decode(response.credential)
    let userProfile = [];
    userProfile.push({ name: "google_id", value: responsePayload.sub });
    userProfile.push({ name: "name", value: responsePayload.name });
    userProfile.push({ name: "email", value: responsePayload.email });
    userProfile.push({ name: "google_image", value: responsePayload.picture });
    userProfile.push({ name: "p", value: 'saveGoogleUser' });
    $.ajax({
        type: "POST",
        data: userProfile,
        url: "ajax/ajaxquery.php",
        async: true,
        success: function(msg) {
            if (msg.error == 1) {
                alert('Something Went Wrong!');
            } else {
                var logInSuccess = true;
                if ($('#basepathinfo').attr('sso_login') === "1") {
                    window.location.replace("php/after-sso.php");
                } else {
                    window.location.replace("index.php");
                }

            }
        }
    });

}
window.onload = function() {
    const google_client_id = document.getElementById("basepathinfo").getAttribute("google_client_id");
    if (google_client_id) {
        google.accounts.id.initialize({
            client_id: google_client_id,
            callback: handleCredentialResponse
        });
        google.accounts.id.renderButton(
            document.getElementById("googleSignIn"), { theme: "outline", size: "large", "width": "199" }
        );
    }

}
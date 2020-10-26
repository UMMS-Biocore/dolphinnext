// google sign-in
function Google_signIn(googleUser) {
    var id_token = googleUser.getAuthResponse().id_token;
    var auth2 = gapi.auth2.getAuthInstance();
    auth2.disconnect();

    var userProfile = [];
    var profile = googleUser.getBasicProfile();
    var emailUser = profile.getEmail();
    var pattEmail = /(.*)@(.*)/;
    userProfile.push({ name: "google_id", value: profile.getId() });
    userProfile.push({name: "name",value: profile.getName()});
    userProfile.push({name: "email",value: profile.getEmail()});
    userProfile.push({name: "google_image",value: profile.getImageUrl()});
    userProfile.push({name: "p",value: 'saveGoogleUser'});
    $.ajax({
        type: "POST",
        data: userProfile,
        url: "ajax/ajaxquery.php",
        async: false,
        success: function (msg) {
            if (msg.error == 1) {
                alert('Something Went Wrong!');
            } else {
                var logInSuccess = true;
                console.log($('#basepathinfo').attr('sso_login'))
                if ($('#basepathinfo').attr('sso_login') === "1"){
                     window.location.replace("index.php?np=7");
                } else {
                     window.location.replace("index.php");
                }
               
            }
        }
    });
}


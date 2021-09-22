if (document.getElementById("after-sso-close")) {
    // if there is a parent window (window that opens popup window) 
    // then window.opener is exist.
    if (window.opener) {
        window.opener.focus();
        if (window.opener && !window.opener.closed) {
            window.opener.location.reload();
        }
    } else {
        window.location = document.getElementById("basepathinfo").getAttribute("basepath");
    }
    window.close();
}
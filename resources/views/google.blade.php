<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login with Google</title>
    <meta name="google-signin-client_id" content="{{ env('GOOGLE_CLIENT_ID') }}">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <h2>تسجيل الدخول باستخدام جوجل</h2>

    <div id="g_id_onload"
         data-client_id="{{ env('GOOGLE_CLIENT_ID') }}"
         data-context="signin"
         data-ux_mode="popup"
         data-callback="handleCredentialResponse"
         data-auto_prompt="false">
    </div>

    <div class="g_id_signin"
         data-type="standard"
         data-shape="rectangular"
         data-theme="outline"
         data-text="signin_with"
         data-size="large"
         data-logo_alignment="left">
    </div>

    <script>
        function handleCredentialResponse(response) {
            console.log("ID Token: " + response.credential);

            // إرسال التوكين إلى السيرفر
           
        }
    </script>
</body>
</html>

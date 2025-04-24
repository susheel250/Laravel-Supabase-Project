<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Handling Supabase Token</title>
</head>
<body>
    <p>Logging you in...</p>

    <script>
        const hashParams = new URLSearchParams(window.location.hash.slice(1));
        const accessToken = hashParams.get('access_token');

        if (accessToken) {
            // Send token to backend
            fetch('/post-login?access_token=' + accessToken)
                .then(() => {
                    window.location.href = '/dashboard';
                });
        } else {
            alert("No token found in URL.");
            window.location.href = '/';
        }
    </script>
</body>
</html>

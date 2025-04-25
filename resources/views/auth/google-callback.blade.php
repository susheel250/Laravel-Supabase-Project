<!DOCTYPE html>
<html>
<head>
    <title>Google OAuth Callback</title>
</head>
<body>
    <h3>Logging you in...</h3>

    <script>
        const hash = window.location.hash.substring(1);
        const params = new URLSearchParams(hash);
        const accessToken = params.get('access_token');

        if (accessToken) {
            // Send token to backend via POST
            fetch('/auth/process-supabase-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ access_token: accessToken })
            }).then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    console.error('Login failed');
                }
            });
        } else {
            console.error('Access token not found in URL');
            window.location.href = '/?error=no_token';
        }
    </script>
</body>
</html>

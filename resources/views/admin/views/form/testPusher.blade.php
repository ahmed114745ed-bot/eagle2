<!DOCTYPE html>

<head>
    <title>Pusher Test</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <!-- Include Laravel Echo script -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.min.js"></script>
</head>

<body>
    <h1>Pusher Test</h1>
    <p>
        Try publishing an event to channel <code>my-channel</code> with event name <code>my-event</code>.
    </p>
    <button type="button" onclick="inlineSaveAdmin();" class="btn btn-outline-success">Trigger Event</button>

    <script>
        // Initialize Echo with Pusher
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: 'cdf0d5778a8268abb136',
            cluster: 'eu',
            forceTLS: true
        });

        function inlineSaveAdmin() {
            console.log("Initializing channel subscription");
            var roomId = 161;
            var userId = 303;
            window.Echo.channel(`room-${roomId}-${userId}`)
                .listen('.event-name', (e) => {
                    console.log(e);
                });
        }
    </script>
</body>

</html>

{{--<!DOCTYPE html>--}}
{{--<html lang="en">--}}
{{--<head>--}}
{{--    <meta charset="UTF-8">--}}
{{--    <meta name="csrf-token" content="{{ csrf_token() }}">--}}
{{--    <title>Pusher Token Test</title>--}}
{{--</head>--}}
{{--<body>--}}
{{--<h1>Testing Broadcast via Token</h1>--}}
{{--<p>Token: {{ $token }}</p>--}}

{{--<script src="https://js.pusher.com/8.2/pusher.min.js"></script>--}}
{{--<script>--}}
{{--    const token = @json($token);--}}

{{--    const pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {--}}
{{--        cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",--}}
{{--        authEndpoint: "/api/broadcasting/auth",--}}
{{--        forceTLS: true,--}}
{{--        // This is the key part → send token in the Authorization header--}}
{{--        auth: {--}}
{{--            headers: {--}}
{{--                Authorization: `Bearer ${token}`,--}}
{{--                Accept: 'application/json',--}}
{{--                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),--}}
{{--            }--}}
{{--        }--}}
{{--    });--}}

{{--    // Test with a simple private channel--}}
{{--    const channel = pusher.subscribe('private-test-private');--}}

{{--    channel.bind('pusher:subscription_succeeded', function() {--}}
{{--        console.log("✅ Subscribed successfully with token");--}}
{{--    });--}}

{{--    channel.bind('pusher:subscription_error', function(status) {--}}
{{--        console.error("❌ Subscription error:", status);--}}
{{--    });--}}

{{--    channel.bind('any-event', function(data) {--}}
{{--        console.log("Event received:", data);--}}
{{--    });--}}
{{--</script>--}}
{{--</body>--}}
{{--</html>--}}

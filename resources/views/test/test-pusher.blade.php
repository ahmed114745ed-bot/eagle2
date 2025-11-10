<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pusher Presence Test</title>
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
</head>
<body>
<pre id="output"></pre>
<script>
    const log = msg => document.getElementById('output').innerText += msg + '\n';

    // ** Replace with your real keys **
    const PUSHER_APP_KEY = 'your-app-key';
    const PUSHER_CLUSTER = 'mt1';

    // Create Pusher instance that points to your Laravel auth endpoint
    const pusher = new Pusher(PUSHER_APP_KEY, {
        cluster: PUSHER_CLUSTER,
        authEndpoint: '/broadcasting/auth',
        auth: { headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }
    });

    const roomId = 12;   // existing chat_room.id
    const channel = pusher.subscribe(`presence-chat.room.${roomId}`);

    channel.bind('pusher:subscription_succeeded', members => {
        log('Subscribed successfully.');
        log('Current members: ' + JSON.stringify(members.members));
    });

    channel.bind('pusher:member_added', member => {
        log(member.info.name + ' joined');
    });

    channel.bind('pusher:member_removed', member => {
        log(member.info.name + ' left');
    });

    // listen for your server broadcast events
    channel.bind('OpenChat', data => {
        log('OpenChat event: ' + JSON.stringify(data));
    });
</script>
</body>
</html>

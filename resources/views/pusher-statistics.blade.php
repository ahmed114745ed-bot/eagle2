@if($error)
    <div class="alert alert-danger">
        <strong>Error:</strong> {{ $error }}
    </div>
@endif

@if(!empty($channels))
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Channel Name</th>
            <th>User Count</th>
            <th>Subscription Count</th>
        </tr>
        </thead>
        <tbody>
        @foreach($channels as $name => $channel)
            <tr>
                <td>{{ $name }}</td>
                <td>{{ $channel['user_count'] ?? 'N/A' }}</td>
                <td>{{ $channel['subscription_count'] ?? 'N/A' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

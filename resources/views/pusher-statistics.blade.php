@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Pusher Channels (from REST API)</h2>

        <table class="table table-sm table-bordered">
            <thead>
            <tr>
                <th>Channel Name</th>
                <th>Occupied</th>
                <th>User Count</th>
                <th>Subscriptions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($channels as $name => $channel)
                <tr>
                    <td>{{ $name }}</td>
                    <td>{{ $channel['occupied'] ? 'Yes' : 'No' }}</td>
                    <td>{{ $channel['user_count'] ?? '-' }}</td>
                    <td>{{ $channel['subscription_count'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No active channels</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection

@if($error)
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle"></i>
        <strong>Error:</strong> {{ $error }}
    </div>
@endif

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-signal"></i> Active Pusher Channels
        </h3>
    </div>
    <div class="box-body table-responsive no-padding">
        @if(!empty($channels))
            <table class="table table-hover table-striped">
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
                        <td><span class="label label-info">{{ $name }}</span></td>
                        <td><span class="badge bg-green">{{ $channel['user_count'] ?? 'N/A' }}</span></td>
                        <td><span class="badge bg-gray">{{ $channel['subscription_count'] ?? 'N/A' }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center p-3 text-muted">
                <i class="fa fa-info-circle"></i> No active channels found
            </div>
        @endif
    </div>
</div>

<style>
    .label {
        font-size: 100%;
    }
</style>

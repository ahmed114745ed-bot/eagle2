@php
    $baseUrl = url('/api/stream/webhooks');

    // تعريف الويب هوك المنظمة حسب الفئات
    $webhookCategories = [
        [
            'name' => __('admin.webhook_category_rooms_streaming'),
            'icon' => 'fa-video',
            'color' => '#FF6B6B',
            'webhooks' => [
                [
                    'event' => 'room_started',
                    'name' => __('admin.webhook_room_started_name'),
                    'description' => __('admin.webhook_room_started_desc')
                ],
                [
                    'event' => 'room_finished',
                    'name' => __('admin.webhook_room_finished_name'),
                    'description' => __('admin.webhook_room_finished_desc')
                ],
                [
                    'event' => 'participant_joined',
                    'name' => __('admin.webhook_participant_joined_name'),
                    'description' => __('admin.webhook_participant_joined_desc')
                ],
                [
                    'event' => 'participant_left',
                    'name' => __('admin.webhook_participant_left_name'),
                    'description' => __('admin.webhook_participant_left_desc')
                ],
                [
                    'event' => 'track_published',
                    'name' => __('admin.webhook_track_published_name'),
                    'description' => __('admin.webhook_track_published_desc')
                ],
                [
                    'event' => 'track_unpublished',
                    'name' => __('admin.webhook_track_unpublished_name'),
                    'description' => __('admin.webhook_track_unpublished_desc')
                ],
            ]
        ],
        [
            'name' => __('admin.webhook_category_calls'),
            'icon' => 'fa-phone',
            'color' => '#4ECDC4',
            'webhooks' => [
                [
                    'event' => 'call_initiated',
                    'name' => __('admin.webhook_call_initiated_name'),
                    'description' => __('admin.webhook_call_initiated_desc')
                ],
                [
                    'event' => 'call_ringing',
                    'name' => __('admin.webhook_call_ringing_name'),
                    'description' => __('admin.webhook_call_ringing_desc')
                ],
                [
                    'event' => 'call_accepted',
                    'name' => __('admin.webhook_call_accepted_name'),
                    'description' => __('admin.webhook_call_accepted_desc')
                ],
                [
                    'event' => 'call_rejected',
                    'name' => __('admin.webhook_call_rejected_name'),
                    'description' => __('admin.webhook_call_rejected_desc')
                ],
                [
                    'event' => 'call_busy',
                    'name' => __('admin.webhook_call_busy_name'),
                    'description' => __('admin.webhook_call_busy_desc')
                ],
                [
                    'event' => 'call_ended',
                    'name' => __('admin.webhook_call_ended_name'),
                    'description' => __('admin.webhook_call_ended_desc')
                ],
                [
                    'event' => 'call_missed',
                    'name' => __('admin.webhook_call_missed_name'),
                    'description' => __('admin.webhook_call_missed_desc')
                ],
            ]
        ],
        [
            'name' => __('admin.webhook_category_presence'),
            'icon' => 'fa-user-circle',
            'color' => '#95E1D3',
            'webhooks' => [
                [
                    'event' => 'user_online',
                    'name' => __('admin.webhook_user_online_name'),
                    'description' => __('admin.webhook_user_online_desc')
                ],
                [
                    'event' => 'user_offline',
                    'name' => __('admin.webhook_user_offline_name'),
                    'description' => __('admin.webhook_user_offline_desc')
                ],
            ]
        ],
        [
            'name' => __('admin.webhook_category_messaging'),
            'icon' => 'fa-comments',
            'color' => '#F38181',
            'webhooks' => [
                [
                    'event' => 'message_sent',
                    'name' => __('admin.webhook_message_sent_name'),
                    'description' => __('admin.webhook_message_sent_desc')
                ],
            ]
        ]
    ];

    $totalEvents = array_sum(array_map(function($cat) { return count($cat['webhooks']); }, $webhookCategories));
@endphp

<div id="utdStreamWebhooks" class="settings-section">
    <div class="form">
        <div class="card shadow-sm" style="background-color: var(--box-background-color, #f8f9fa); border: none; border-radius: 12px; overflow: hidden;">
            <!-- Header -->
            <div class="card-header" style="background: linear-gradient(135deg, var(--primary-color, #FF9428) 0%, #c88213 100%); padding: 25px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-webhook" style="font-size: 30px; color: white;"></i>
                    </div>
                    <div style="flex: 1;">
                        <h3 class="mb-0" style="color: white; font-weight: bold; font-size: 24px;">
                            {{ __('admin.UTD Stream Webhooks') }}
                        </h3>
                        <p class="mb-0 mt-2" style="color: rgba(255,255,255,0.9); font-size: 14px;">
                            Configure these webhook URLs in your UTD-STREAM dashboard to receive real-time events
                        </p>
                    </div>
                    <div style="background: rgba(255,255,255,0.15); padding: 15px 20px; border-radius: 10px; text-align: center;">
                        <div style="color: rgba(255,255,255,0.8); font-size: 12px; margin-bottom: 5px;">{{ __('admin.Total Events') }}</div>
                        <div style="color: white; font-size: 28px; font-weight: bold;">{{ $totalEvents }}</div>
                    </div>
                </div>
            </div>

            <div class="card-body" style="padding: 30px;">
                <!-- Base URL Section -->
                <div class="alert" style="background: linear-gradient(135deg, rgba(var(--primary-color-rgb, 255,148,40), 0.1) 0%, rgba(var(--primary-color-rgb, 255,148,40), 0.05) 100%); border: 2px solid var(--primary-color, #FF9428); border-radius: 10px; padding: 20px; margin-bottom: 30px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                        <div style="background: var(--primary-color, #FF9428); width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-link" style="color: white; font-size: 18px;"></i>
                        </div>
                        <strong style="color: var(--text-primary-color, #333); font-size: 18px;">{{ __('admin.Base Webhook URL') }}</strong>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text"
                               value="{{ $baseUrl }}"
                               class="form-control"
                               readonly
                               style="background-color: var(--table-background-color, #fff); border: 2px solid var(--primary-color, #FF9428); color: var(--primary-color, #FF9428); font-family: 'Courier New', monospace; font-size: 14px; font-weight: bold; flex: 1; padding: 12px;">
                        <button class="btn"
                                onclick="copyToClipboard('{{ $baseUrl }}', event, 'base')"
                                style="background: var(--primary-color, #FF9428); color: white; padding: 12px 25px; border: none; border-radius: 8px; min-width: 120px; font-weight: bold; transition: all 0.3s; box-shadow: 0 4px 12px rgba(255,148,40,0.3);">
                            <i class="fa fa-copy"></i> <span class="copy-text">{{ __('admin.Copy') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Webhook Categories -->
                @foreach($webhookCategories as $index => $category)
                <div class="webhook-category mb-4" style="background-color: var(--box-background-color, #f8f9fa); border-radius: 12px; padding: 25px; border-left: 5px solid {{ $category['color'] }}; box-shadow: 0 2px 8px rgba(0,0,0,0.08); animation: fadeIn 0.5s ease-in {{$index * 0.1}}s backwards;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                        <h4 style="color: {{ $category['color'] }}; margin: 0; font-weight: bold; font-size: 20px; display: flex; align-items: center; gap: 10px;">
                            <div style="background: {{ $category['color'] }}; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa {{ $category['icon'] }}" style="color: white; font-size: 22px;"></i>
                            </div>
                            {{ $category['name'] }}
                        </h4>
                        <span style="background: {{ $category['color'] }}; color: white; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: bold;">
                            {{ count($category['webhooks']) }} {{ __('admin.events') }}
                        </span>
                    </div>

                    <div class="webhooks-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                        @foreach($category['webhooks'] as $webhook)
                        <div class="webhook-item" style="background-color: var(--table-background-color, #fff); border-radius: 10px; padding: 20px; transition: all 0.3s; border: 2px solid transparent; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <div style="margin-bottom: 15px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                    <div style="width: 8px; height: 8px; background: {{ $category['color'] }}; border-radius: 50%;"></div>
                                    <div style="color: {{ $category['color'] }}; font-weight: bold; font-size: 16px; font-family: 'Courier New', monospace;">
                                        {{ $webhook['name'] }}
                                    </div>
                                </div>
                                <div style="color: var(--text-secondary-color, #666); font-size: 13px; line-height: 1.6; padding-left: 16px;">
                                    {{ $webhook['description'] }}
                                </div>
                            </div>

                            <div style="background: var(--box-background-color, #f8f9fa); padding: 10px; border-radius: 6px; margin-bottom: 12px;">
                                <div style="font-size: 11px; color: var(--text-secondary-color, #666); margin-bottom: 5px; text-transform: uppercase; font-weight: bold;">Event Type:</div>
                                <code style="color: {{ $category['color'] }}; font-size: 12px; background: transparent;">{{ $webhook['event'] }}</code>
                            </div>

                            <div style="display: flex; gap: 8px; align-items: center;">
                                <input type="text"
                                       value="{{ $baseUrl }}/{{ $webhook['event'] }}"
                                       class="form-control"
                                       readonly
                                       style="background-color: var(--box-background-color, #f8f9fa); border: 1px solid #ddd; color: var(--text-primary-color, #333); font-family: 'Courier New', monospace; font-size: 11px; flex: 1; padding: 10px;">
                                <button class="btn btn-sm"
                                        onclick="copyToClipboard('{{ $baseUrl }}/{{ $webhook['event'] }}', event, '{{ $webhook['event'] }}')"
                                        style="background-color: {{ $category['color'] }}; color: white; padding: 10px 15px; border: none; border-radius: 6px; transition: all 0.3s; white-space: nowrap; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
.webhook-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
    border-color: var(--primary-color, #FF9428) !important;
}

.webhook-category {
    transition: all 0.3s ease;
}

.webhook-category:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.btn:hover {
    opacity: 0.85;
    transform: scale(1.05);
}

.btn:active {
    transform: scale(0.98);
}

/* Dark mode support */
.dark-mode .webhook-item {
    background-color: var(--table-background-color, #2a2a2a) !important;
}

.dark-mode .webhook-category {
    background-color: var(--box-background-color, #1a1a1a) !important;
}

@media (max-width: 768px) {
    .webhooks-grid {
        grid-template-columns: 1fr !important;
    }

    .card-header > div {
        flex-direction: column !important;
        text-align: center !important;
    }
}
</style>

<script>
function copyToClipboard(text, event, identifier) {
    const btn = event.currentTarget;
    const originalHTML = btn.innerHTML;

    // Create temporary input
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    tempInput.setSelectionRange(0, 99999);

    try {
        document.execCommand('copy');
        document.body.removeChild(tempInput);

        // Success feedback
        btn.innerHTML = '<i class="fa fa-check"></i> {{ __("admin.Copied!") }}';
        const originalBg = btn.style.backgroundColor;
        btn.style.backgroundColor = '#4CAF50';

        setTimeout(function() {
            btn.innerHTML = originalHTML;
            btn.style.backgroundColor = originalBg;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
        document.body.removeChild(tempInput);

        // Error feedback
        btn.innerHTML = '<i class="fa fa-times"></i> {{ __("admin.Failed") }}';
        const originalBg = btn.style.backgroundColor;
        btn.style.backgroundColor = '#f44336';

        setTimeout(function() {
            btn.innerHTML = originalHTML;
            btn.style.backgroundColor = originalBg;
        }, 2000);
    }
}
</script>

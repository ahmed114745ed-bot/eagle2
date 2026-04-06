<div id="chargeSettings" class="settings-section">
    <div class="box-body">
        <div class="section-header mb-4">
            <h4>{{ __('Charge settings') }}</h4>
            <p class="text-muted">{{ __('Control charging permissions for different transfer scenarios') }}</p>
        </div>

        <form action="{{ route('admin.app.settings.update') }}" method="POST" class="settings-form">
            @csrf
            <input type="hidden" name="current_tab" value="chargeTransferSettings">

            <div class="charge-transfer-grid">
                <!-- User to User Transfer -->
                <div class="charge-transfer-card">
                    <div class="charge-transfer-header">
                        <div class="charge-transfer-icon user-to-user">
                            <i class="fas fa-user"></i>
                            <i class="fas fa-arrow-right arrow-icon"></i>
                            <i class="fas fa-user"></i>
                        </div>
                        <h5>{{ __('User to User Transfer') }}</h5>
                    </div>
                    <div class="charge-transfer-body">
                        <div class="form-group switch-group">
                            <label class="switch-label">{{ __('Allow Charging') }}</label>
                            <label class="switch">
                                <input type="hidden" name="charge_user_to_user" value="0">
                                <input type="checkbox" name="charge_user_to_user" value="1"
                                    {{ ($settings['charge_user_to_user'] ?? 1) == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                            <small class="text-muted d-block">{{ __('When enabled, users can charge other users') }}</small>
                        </div>
                    </div>
                </div>

                <!-- User to Charging Agent Transfer -->
                <div class="charge-transfer-card">
                    <div class="charge-transfer-header">
                        <div class="charge-transfer-icon user-to-agent">
                            <i class="fas fa-user"></i>
                            <i class="fas fa-arrow-right arrow-icon"></i>
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5>{{ __('User to Charging Agent Transfer') }}</h5>
                    </div>
                    <div class="charge-transfer-body">
                        <div class="form-group switch-group">
                            <label class="switch-label">{{ __('Allow Charging') }}</label>
                            <label class="switch">
                                <input type="hidden" name="charge_user_to_agent" value="0">
                                <input type="checkbox" name="charge_user_to_agent" value="1"
                                    {{ ($settings['charge_user_to_agent'] ?? 1) == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                            <small class="text-muted d-block">{{ __('When enabled, users can charge charging agents') }}</small>
                        </div>
                    </div>
                </div>

                <!-- User to Self Transfer -->
                <div class="charge-transfer-card">
                    <div class="charge-transfer-header">
                        <div class="charge-transfer-icon user-to-self">
                            <i class="fas fa-user"></i>
                            <i class="fas fa-arrow-right arrow-icon"></i>
                            <i class="fas fa-user"></i>
                        </div>
                        <h5>{{ __('User to Self Transfer') }}</h5>
                    </div>
                    <div class="charge-transfer-body">
                        <div class="form-group switch-group">
                            <label class="switch-label">{{ __('Allow Charging') }}</label>
                            <label class="switch">
                                <input type="hidden" name="charge_user_to_self" value="0">
                                <input type="checkbox" name="charge_user_to_self" value="1"
                                    {{ ($settings['charge_user_to_self'] ?? 1) == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                            <small class="text-muted d-block">{{ __('When enabled, users can charge themselves') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Charging Agent to User Transfer -->
                <div class="charge-transfer-card">
                    <div class="charge-transfer-header">
                        <div class="charge-transfer-icon agent-to-user">
                            <i class="fas fa-bolt"></i>
                            <i class="fas fa-arrow-right arrow-icon"></i>
                            <i class="fas fa-user"></i>
                        </div>
                        <h5>{{ __('Charging Agent to User Transfer') }}</h5>
                    </div>
                    <div class="charge-transfer-body">
                        <div class="form-group switch-group">
                            <label class="switch-label">{{ __('Allow Charging') }}</label>
                            <label class="switch">
                                <input type="hidden" name="charge_agent_to_user" value="0">
                                <input type="checkbox" name="charge_agent_to_user" value="1"
                                    {{ ($settings['charge_agent_to_user'] ?? 1) == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                            <small class="text-muted d-block">{{ __('When enabled, charging agents can charge users') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Charging Agent to Charging Agent Transfer -->
                <div class="charge-transfer-card">
                    <div class="charge-transfer-header">
                        <div class="charge-transfer-icon agent-to-agent">
                            <i class="fas fa-bolt"></i>
                            <i class="fas fa-arrow-right arrow-icon"></i>
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5>{{ __('Charging Agent to Charging Agent Transfer') }}</h5>
                    </div>
                    <div class="charge-transfer-body">
                        <div class="form-group switch-group">
                            <label class="switch-label">{{ __('Allow Charging') }}</label>
                            <label class="switch">
                                <input type="hidden" name="charge_agent_to_agent" value="0">
                                <input type="checkbox" name="charge_agent_to_agent" value="1"
                                    {{ ($settings['charge_agent_to_agent'] ?? 1) == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                            <small class="text-muted d-block">{{ __('When enabled, charging agents can charge other agents') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary btn-save">
                    <i class="fas fa-save"></i> {{ __('Save Settings') }}
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.charge-transfer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.charge-transfer-card {
    background: #222;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #333;
    transition: all 0.3s ease;
}

.charge-transfer-card:hover {
    border-color: #ff9800;
}

.charge-transfer-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.charge-transfer-icon {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    border-radius: 8px;
    background: #333;
}

.charge-transfer-icon i {
    font-size: 18px;
}

.charge-transfer-icon .arrow-icon {
    font-size: 12px;
    color: #888;
}

.charge-transfer-icon.user-to-user i:first-child,
.charge-transfer-icon.user-to-user i:last-child {
    color: #3498db;
}

.charge-transfer-icon.user-to-agent i:first-child {
    color: #3498db;
}

.charge-transfer-icon.user-to-agent i:last-child {
    color: #ff9800;
}

.charge-transfer-icon.user-to-self i {
    color: #9b59b6;
}

.charge-transfer-icon.agent-to-user i:first-child {
    color: #ff9800;
}

.charge-transfer-icon.agent-to-user i:last-child {
    color: #3498db;
}

.charge-transfer-icon.agent-to-agent i {
    color: #ff9800;
}

.charge-transfer-header h5 {
    margin: 0;
    color: #fff;
    font-size: 14px;
}

.switch-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.switch-label {
    color: #ccc;
    font-weight: 500;
}

.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #ff9800;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}

.form-actions {
    text-align: center;
}

.btn-save {
    background: #ff9800;
    border: none;
    padding: 12px 30px;
    font-weight: bold;
}

.btn-save:hover {
    background: #e68900;
}

.section-header h4 {
    color: #ff9800;
    margin-bottom: 5px;
}

.section-header p {
    color: #888;
}

@media (max-width: 768px) {
    .charge-transfer-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div id="workSettings" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
            <i class="fas fa-briefcase"></i>
        </div>
        <div class="section-header-text">
            <h3>{{ __('Work Settings') }}</h3>
            <p>{{ __('Configure experience, charge and work-related settings') }}</p>
        </div>
    </div>

    <div class="box-body">
        <!-- Modern Pill Tab Navigation -->
        <div class="work-tab-nav">
            @php $chargeTabType = request()->get('type', 'Experience'); @endphp
            <button onclick="changeInnerTab('Experience')"
                    class="work-tab-pill {{ $chargeTabType == 'Experience' ? 'active' : '' }}">
                <span class="work-tab-icon"><i class="fas fa-star"></i></span>
                <span class="work-tab-label">{{ __('Experience settings') }}</span>
            </button>
            <button onclick="changeInnerTab('coin')"
                    class="work-tab-pill {{ $chargeTabType == 'coin' ? 'active' : '' }}">
                <span class="work-tab-icon"><i class="fas fa-coins"></i></span>
                <span class="work-tab-label">{{ __('coin exchange') }}</span>
            </button>
        </div>

        @php
            $oldExpData = cache('exp_percentages');
            use Modules\Vip\Entities\Vip;
        @endphp

        <!-- ═══════════════ Experience Tab ═══════════════ -->
        <div id="Experience_tab" class="inner-tab-content"
             style="display:{{ $chargeTabType == 'Experience' ? 'block' : 'none' }};">

            <div class="work-section-intro">
                <div class="work-intro-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h4>{{ __('Experience settings:') }}</h4>
                    <p>{{ __('Configure EXP rates for different activities') }}</p>
                </div>
            </div>

            <div class="work-cards-grid">

                {{-- ── Wealth Card ── --}}
                <div class="work-exp-card">
                    <form action="{{ route('admin.ovip-config') }}" method="POST" enctype="multipart/form-data"
                          class="work-card-form">
                        @csrf
                        <div class="work-card-top wealth-gradient">
                            <div class="work-card-icon-wrap">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h5>{{ __('wealth') }}</h5>
                            <span class="work-card-badge">EXP</span>
                        </div>

                        <div class="work-card-body">
                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-percentage"></i> {{ __('EXP Rate') }}
                                </label>
                                <div class="work-input-wrap">
                                    <input type="number" id="wealth_exp" name="exp_sender_percentage"
                                           value="{{ $oldExpData['exp_sender_percentage'] ?? '' }}"
                                           placeholder="{{ __('Enter Exp') }}" class="form-control work-input"
                                           step="any" required>
                                    <span class="work-input-suffix">EXP</span>
                                </div>
                                <small class="work-field-hint"><i class="fas fa-info-circle"></i> 1 coin = X EXP</small>
                            </div>

                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-gift"></i> {{ __('gift price') }}
                                </label>
                                <div class="input-with-result work-input-wrap">
                                    <input type="number" id="wealth_gift_price" name="wealth_gift_price" step="any"
                                           value="{{ $settings['wealth_gift_price'] ?? '' }}"
                                           placeholder="{{ __('Enter price') }}" class="form-control work-input">
                                    <span id="wealth_exp_result" class="result-badge work-result-badge"></span>
                                </div>
                            </div>

                            @php $sender = Vip::where('type',2)->count(); @endphp
                            @if ($sender == 0)
                                <div class="work-alert-banner">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>{{ __('No VIP levels configured') }}</span>
                                    <a href="/admin/vips" class="work-alert-link">
                                        <i class="fas fa-arrow-right"></i> {{ __('Go to Settings') }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="work-card-footer">
                            <button type="submit" class="btn btn-primary btn-save work-save-btn">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ── Attraction Card ── --}}
                <div class="work-exp-card">
                    <form action="{{ route('admin.ovip-config') }}" method="POST" enctype="multipart/form-data"
                          class="work-card-form">
                        @csrf
                        <div class="work-card-top attraction-gradient">
                            <div class="work-card-icon-wrap">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h5>{{ __('attraction') }}</h5>
                            <span class="work-card-badge">EXP</span>
                        </div>

                        <div class="work-card-body">
                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-percentage"></i> {{ __('EXP Rate') }}
                                </label>
                                <div class="work-input-wrap">
                                    <input type="number" id="attraction_exp" name="exp_received_percentage"
                                           value="{{ $oldExpData['exp_received_percentage'] ?? '' }}"
                                           placeholder="{{ __('Enter Exp') }}" class="form-control work-input"
                                           step="any" required>
                                    <span class="work-input-suffix">EXP</span>
                                </div>
                                <small class="work-field-hint"><i class="fas fa-info-circle"></i> 1 Diamond = X EXP</small>
                            </div>

                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-gift"></i> {{ __('gift price') }}
                                </label>
                                <div class="input-with-result work-input-wrap">
                                    <input type="number" id="attraction_gift_price" name="attraction_gift_price"
                                           step="any"
                                           value="{{ $settings['attraction_gift_price'] ?? '' }}"
                                           placeholder="{{ __('Enter price') }}" class="form-control work-input">
                                    <span id="attraction_exp_result" class="result-badge work-result-badge"></span>
                                </div>
                            </div>

                            @php $receiver = Vip::where('type',1)->count(); @endphp
                            @if ($receiver == 0)
                                <div class="work-alert-banner">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>{{ __('No VIP levels configured') }}</span>
                                    <a href="/admin/vips" class="work-alert-link">
                                        <i class="fas fa-arrow-right"></i> {{ __('Go to Settings') }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="work-card-footer">
                            <button type="submit" class="btn btn-primary btn-save work-save-btn">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ── Charge Card ── --}}
                <div class="work-exp-card">
                    <form action="{{ route('admin.ovip-config') }}" method="POST" class="work-card-form">
                        @csrf
                        <div class="work-card-top charge-gradient">
                            <div class="work-card-icon-wrap">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h5>{{ __('charge') }}</h5>
                            <span class="work-card-badge">EXP</span>
                        </div>

                        <div class="work-card-body">
                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-percentage"></i> {{ __('EXP Rate') }}
                                </label>
                                <div class="work-input-wrap">
                                    <input type="number" id="charge_exp" name="exp_charge_percentage"
                                           value="{{ $oldExpData['exp_charge_percentage'] ?? '' }}"
                                           placeholder="{{ __('Enter Exp') }}" class="form-control work-input"
                                           step="any" required>
                                    <span class="work-input-suffix">EXP</span>
                                </div>
                                <small class="work-field-hint"><i class="fas fa-info-circle"></i> 1 charge = X EXP</small>
                            </div>

                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-coins"></i> {{ __('coins') }}
                                </label>
                                <div class="input-with-result work-input-wrap">
                                    <input type="number" id="charge_gift_price" name="charge_gift_price" step="any"
                                           value="{{ $settings['charge_gift_price'] ?? '' }}"
                                           placeholder="{{ __('Enter amount') }}" class="form-control work-input">
                                    <span id="charge_exp_result" class="result-badge work-result-badge"></span>
                                </div>
                            </div>

                            @php $charger = Vip::where('type',5)->count(); @endphp
                            @if ($charger == 0)
                                <div class="work-alert-banner">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>{{ __('No VIP levels configured') }}</span>
                                    <a href="/admin/vips" class="work-alert-link">
                                        <i class="fas fa-arrow-right"></i> {{ __('Go to Settings') }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="work-card-footer">
                            <button type="submit" class="btn btn-primary btn-save work-save-btn">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ── Rooms Card ── --}}
                <div class="work-exp-card">
                    <form action="{{ route('admin.ovip-config') }}" method="POST" class="work-card-form">
                        @csrf
                        <div class="work-card-top rooms-gradient">
                            <div class="work-card-icon-wrap">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <h5>{{ __('Rooms') }}</h5>
                            <span class="work-card-badge">EXP</span>
                        </div>

                        <div class="work-card-body">
                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-percentage"></i> {{ __('EXP Rate') }}
                                </label>
                                <div class="work-input-wrap">
                                    <input type="number" id="rooms_exp" name="exp_room_percentage"
                                           value="{{ $oldExpData['exp_room_percentage'] ?? '' }}"
                                           placeholder="{{ __('Enter Exp') }}" class="form-control work-input"
                                           step="any" required>
                                    <span class="work-input-suffix">EXP</span>
                                </div>
                                <small class="work-field-hint"><i class="fas fa-info-circle"></i> 1 room action = X EXP</small>
                            </div>

                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-gift"></i> {{ __('gift price') }}
                                </label>
                                <div class="input-with-result work-input-wrap">
                                    <input type="number" id="rooms_gift_price" name="rooms_gift_price" step="any"
                                           value="{{ $settings['rooms_gift_price'] ?? '' }}"
                                           placeholder="{{ __('Enter price') }}" class="form-control work-input">
                                    <span id="rooms_exp_result" class="result-badge work-result-badge"></span>
                                </div>
                            </div>

                            @php $rooms = Vip::where('type',4)->count(); @endphp
                            @if ($rooms == 0)
                                <div class="work-alert-banner">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>{{ __('No VIP levels configured') }}</span>
                                    <a href="/admin/vips" class="work-alert-link">
                                        <i class="fas fa-arrow-right"></i> {{ __('Go to Settings') }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="work-card-footer">
                            <button type="submit" class="btn btn-primary btn-save work-save-btn">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ── CP Card ── --}}
                <div class="work-exp-card">
                    <form action="{{ route('admin.ovip-config') }}" method="POST" class="work-card-form">
                        @csrf
                        <div class="work-card-top cp-gradient">
                            <div class="work-card-icon-wrap">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5>{{ __('cp') }}</h5>
                            <span class="work-card-badge">EXP</span>
                        </div>

                        <div class="work-card-body">
                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-percentage"></i> {{ __('EXP Rate') }}
                                </label>
                                <div class="work-input-wrap">
                                    <input type="number" id="cp_exp" name="exp_cp_percentage"
                                           value="{{ $oldExpData['exp_cp_percentage'] ?? '' }}"
                                           placeholder="{{ __('Enter Exp') }}" class="form-control work-input"
                                           step="any" required>
                                    <span class="work-input-suffix">EXP</span>
                                </div>
                                <small class="work-field-hint"><i class="fas fa-info-circle"></i> 1 CP action = X EXP</small>
                            </div>

                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-gift"></i> {{ __('gift price') }}
                                </label>
                                <div class="input-with-result work-input-wrap">
                                    <input type="number" id="cp_gift_price" name="cp_gift_price" step="any"
                                           value="{{ $settings['cp_gift_price'] ?? '' }}"
                                           placeholder="{{ __('Enter price') }}" class="form-control work-input">
                                    <span id="cp_exp_result" class="result-badge work-result-badge"></span>
                                </div>
                            </div>

                            @php $cp = Vip::where('type',3)->count(); @endphp
                            @if ($cp == 0)
                                <div class="work-alert-banner">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>{{ __('No VIP levels configured') }}</span>
                                    <a href="/admin/vips" class="work-alert-link">
                                        <i class="fas fa-arrow-right"></i> {{ __('Go to Settings') }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="work-card-footer">
                            <button type="submit" class="btn btn-primary btn-save work-save-btn">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <!-- ═══════════════ Coin Exchange Tab ═══════════════ -->
        <div id="coin_tab" class="inner-tab-content"
             style="display:{{ $chargeTabType == 'coin' ? 'block' : 'none' }};">

            <div class="work-section-intro">
                <div class="work-intro-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <h4>{{ __('coin exchange') }}</h4>
                    <p>{{ __('Configure diamond to coin exchange rate') }}</p>
                </div>
            </div>

            <div class="work-cards-grid work-single-card">
                <div class="work-exp-card work-exchange-card">
                    <form action="{{ route('admin.exchange-coins') }}" method="POST" enctype="multipart/form-data"
                          class="work-card-form">
                        @csrf
                        <div class="work-card-top diamond-gradient">
                            <div class="work-card-icon-wrap">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h5>{{ __('Diamond Exchange') }}</h5>
                            <span class="work-card-badge">RATE</span>
                        </div>

                        <div class="work-card-body">
                            <div class="work-exchange-visual">
                                <div class="work-exchange-from">
                                    <i class="fas fa-gem"></i>
                                    <span>1 {{ __('Diamond') }}</span>
                                </div>
                                <div class="work-exchange-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                <div class="work-exchange-to">
                                    <i class="fas fa-coins"></i>
                                    <span id="work-exchange-preview">{{ $settings['exchange_coin_percentage'] ?? 1 }}</span>
                                    <small>{{ __('Coins') }}</small>
                                </div>
                            </div>

                            <div class="work-field-group">
                                <label class="work-field-label">
                                    <i class="fas fa-sliders-h"></i> {{ __('Exchange Rate') }}
                                </label>
                                <div class="work-input-wrap">
                                    <input type="number" id="coin_exp" name="exchange_coin_percentage"
                                           value="{{ $settings['exchange_coin_percentage'] ?? 1 }}"
                                           placeholder="{{ __('Enter rate') }}" class="form-control work-input"
                                           step="any" required
                                           oninput="document.getElementById('work-exchange-preview').textContent = this.value || '0'">
                                    <span class="work-input-suffix">×</span>
                                </div>
                                <small class="work-field-hint"><i class="fas fa-info-circle"></i> 1 diamond = X coin</small>
                            </div>

                            <div class="work-calculator-box">
                                <div class="work-calculator-header">
                                    <i class="fas fa-calculator"></i>
                                    <span>{{ __('Calculator') }}</span>
                                </div>
                                <div class="work-calculator-body">
                                    <div class="work-field-group" style="margin-bottom: 0;">
                                        <div class="input-with-result work-input-wrap">
                                            <input type="number" class="user_coin_input form-control work-input"
                                                   step="any"
                                                   placeholder="{{ __('Enter diamond amount') }}">
                                            <input type="hidden" class="exchange_rate"
                                                   value="{{ $settings['exchange_coin_percentage'] ?? 1 }}">
                                            <span class="exp_result result-badge work-result-badge"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="work-card-footer">
                            <button type="submit" class="btn btn-primary btn-save work-save-btn">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     WORK SETTINGS — Scoped Styles
     ═══════════════════════════════════════════════════ --}}
<style>
    /* ── Tab Navigation ── */
    .work-tab-nav {
        display: flex;
        gap: 8px;
        padding: 6px;
        background: var(--secondary-color, #f1f5f9);
        border-radius: 14px;
        margin-bottom: 28px;
        width: fit-content;
        border: 1px solid rgba(0,0,0,0.06);
    }
    .dark-mode .work-tab-nav {
        background: rgba(255,255,255,0.04);
        border-color: rgba(255,255,255,0.06);
    }
    .work-tab-pill {
        display: inline-flex !important;
        align-items: center;
        gap: 10px;
        padding: 11px 22px !important;
        border: none !important;
        border-radius: 10px !important;
        background: transparent !important;
        color: var(--text-secondary-color, #64748b) !important;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.25s ease;
        width: auto !important;
        white-space: nowrap;
    }
    .work-tab-pill:hover {
        background: rgba(99,102,241,0.06) !important;
        color: #6366f1 !important;
    }
    .work-tab-pill.active {
        background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
        color: #fff !important;
        box-shadow: 0 4px 14px rgba(99,102,241,0.35);
    }
    .work-tab-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        font-size: 13px;
    }
    .work-tab-pill.active .work-tab-icon {
        background: rgba(255,255,255,0.2);
    }
    .work-tab-pill:not(.active) .work-tab-icon {
        background: rgba(99,102,241,0.08);
        color: #6366f1;
    }

    /* ── Section Intro ── */
    .work-section-intro {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 28px;
        padding: 18px 22px;
        background: var(--white, #fff);
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    }
    .dark-mode .work-section-intro {
        background: var(--dark-secondry-color, #1e293b);
        border-color: rgba(255,255,255,0.06);
    }
    .work-intro-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(99,102,241,0.25);
    }
    .work-section-intro h4 {
        margin: 0 0 3px;
        font-size: 17px;
        font-weight: 700;
        color: var(--text-secondary-color, #1e293b);
    }
    .dark-mode .work-section-intro h4 { color: #f1f5f9; }
    .work-section-intro p {
        margin: 0;
        font-size: 13px;
        color: #94a3b8;
    }

    /* ── Cards Grid ── */
    .work-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 22px;
    }
    .work-cards-grid.work-single-card {
        grid-template-columns: minmax(340px, 460px);
    }

    /* ── Individual Card ── */
    .work-exp-card {
        border-radius: 18px;
        overflow: hidden;
        background: var(--white, #fff);
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dark-mode .work-exp-card {
        background: var(--dark-secondry-color, #1e293b);
        border-color: rgba(255,255,255,0.06);
    }
    .work-exp-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 36px rgba(0,0,0,0.1);
        border-color: rgba(99,102,241,0.2);
    }
    .dark-mode .work-exp-card:hover {
        box-shadow: 0 12px 36px rgba(0,0,0,0.3);
        border-color: rgba(99,102,241,0.3);
    }

    /* ── Card Form Reset ── */
    .work-card-form {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        border-radius: 0 !important;
    }

    /* ── Card Top / Header with Gradient ── */
    .work-card-top {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px 22px;
        position: relative;
        overflow: hidden;
    }
    .work-card-top::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .work-card-top::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .work-card-top h5 {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        color: #fff;
        flex: 1;
        text-transform: capitalize;
        position: relative;
        z-index: 1;
    }
    .work-card-icon-wrap {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #fff;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }
    .work-card-badge {
        padding: 4px 12px;
        border-radius: 20px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
        position: relative;
        z-index: 1;
    }

    /* Gradient Themes */
    .wealth-gradient    { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .attraction-gradient { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .charge-gradient    { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .rooms-gradient     { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .cp-gradient        { background: linear-gradient(135deg, #10b981, #059669); }
    .diamond-gradient   { background: linear-gradient(135deg, #06b6d4, #0891b2); }

    /* ── Card Body ── */
    .work-card-body {
        padding: 22px;
    }

    /* ── Field Group ── */
    .work-field-group {
        margin-bottom: 18px;
    }
    .work-field-group:last-child {
        margin-bottom: 0;
    }
    .work-field-label {
        display: flex !important;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary-color, #475569);
        margin-bottom: 8px !important;
        text-transform: capitalize;
    }
    .dark-mode .work-field-label {
        color: #cbd5e1;
    }
    .work-field-label i {
        font-size: 12px;
        color: #94a3b8;
    }
    .work-field-hint {
        display: flex !important;
        align-items: center;
        gap: 5px;
        margin-top: 7px;
        font-size: 12px;
        color: #94a3b8;
    }
    .work-field-hint i {
        font-size: 11px;
    }

    /* ── Input Styling ── */
    .work-input-wrap {
        position: relative;
    }
    .work-input {
        background: #f8fafc !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 11px 50px 11px 14px !important;
        font-size: 14px;
        transition: all 0.2s ease;
        width: 100% !important;
    }
    .dark-mode .work-input {
        background: rgba(255,255,255,0.04) !important;
        border-color: rgba(255,255,255,0.1) !important;
        color: #f1f5f9 !important;
    }
    .work-input:focus {
        background: #fff !important;
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1) !important;
        outline: none !important;
    }
    .dark-mode .work-input:focus {
        background: rgba(255,255,255,0.06) !important;
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15) !important;
    }
    .work-input-suffix {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        font-weight: 700;
        color: #94a3b8;
        pointer-events: none;
    }
    .rtl .work-input-suffix {
        right: auto;
        left: 14px;
    }
    .rtl .work-input {
        padding: 11px 14px 11px 50px !important;
    }

    /* ── Result Badge ── */
    .work-result-badge {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
        color: #fff;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(99,102,241,0.3);
    }
    .rtl .work-result-badge {
        right: auto;
        left: 12px;
    }

    /* ── Alert Banner ── */
    .work-alert-banner {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 10px;
        background: #fef3c7;
        border: 1px solid #fcd34d;
        margin-top: 14px;
        font-size: 13px;
        color: #92400e;
    }
    .dark-mode .work-alert-banner {
        background: rgba(251,191,36,0.1);
        border-color: rgba(251,191,36,0.2);
        color: #fbbf24;
    }
    .work-alert-banner > i {
        color: #f59e0b;
        font-size: 15px;
        flex-shrink: 0;
    }
    .work-alert-banner span {
        flex: 1;
        font-weight: 500;
    }
    .work-alert-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 6px;
        background: #f59e0b;
        color: #fff !important;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none !important;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    .work-alert-link:hover {
        background: #d97706;
        transform: translateX(2px);
    }

    /* ── Card Footer ── */
    .work-card-footer {
        padding: 16px 22px;
        border-top: 1px solid #f1f5f9;
    }
    .dark-mode .work-card-footer {
        border-top-color: rgba(255,255,255,0.06);
    }
    .work-save-btn {
        width: 100% !important;
        padding: 12px 24px !important;
        border-radius: 10px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
        border: none !important;
        color: #fff !important;
        transition: all 0.25s ease !important;
        box-shadow: 0 2px 8px rgba(99,102,241,0.25) !important;
    }
    .work-save-btn:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(99,102,241,0.35) !important;
    }
    .work-save-btn:active {
        transform: translateY(0) !important;
    }

    /* ── Exchange Visual ── */
    .work-exchange-visual {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        padding: 22px;
        margin-bottom: 22px;
        background: linear-gradient(135deg, rgba(6,182,212,0.06), rgba(8,145,178,0.03));
        border-radius: 14px;
        border: 1px dashed rgba(6,182,212,0.2);
    }
    .dark-mode .work-exchange-visual {
        background: linear-gradient(135deg, rgba(6,182,212,0.08), rgba(8,145,178,0.04));
        border-color: rgba(6,182,212,0.15);
    }
    .work-exchange-from,
    .work-exchange-to {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .work-exchange-from i {
        font-size: 28px;
        color: #06b6d4;
    }
    .work-exchange-from span {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary-color, #475569);
    }
    .dark-mode .work-exchange-from span,
    .dark-mode .work-exchange-to small { color: #94a3b8; }
    .work-exchange-arrow {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        box-shadow: 0 3px 10px rgba(6,182,212,0.3);
    }
    .work-exchange-to span {
        font-size: 32px;
        font-weight: 800;
        color: #0891b2;
        line-height: 1;
    }
    .dark-mode .work-exchange-to span { color: #22d3ee; }
    .work-exchange-to small {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .work-exchange-to i {
        font-size: 28px;
        color: #f59e0b;
    }

    /* ── Calculator Box ── */
    .work-calculator-box {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-top: 18px;
    }
    .dark-mode .work-calculator-box {
        border-color: rgba(255,255,255,0.08);
    }
    .work-calculator-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary-color, #475569);
    }
    .dark-mode .work-calculator-header {
        background: rgba(255,255,255,0.03);
        border-bottom-color: rgba(255,255,255,0.06);
        color: #cbd5e1;
    }
    .work-calculator-header i {
        color: #6366f1;
    }
    .work-calculator-body {
        padding: 16px;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .work-tab-nav {
            width: 100%;
        }
        .work-tab-pill {
            flex: 1;
            justify-content: center;
            padding: 10px 14px !important;
            font-size: 13px;
        }
        .work-cards-grid {
            grid-template-columns: 1fr;
        }
        .work-section-intro {
            padding: 14px 16px;
        }
        .work-exchange-visual {
            padding: 16px;
            gap: 14px;
        }
        .work-exchange-to span {
            font-size: 26px;
        }
    }
</style>

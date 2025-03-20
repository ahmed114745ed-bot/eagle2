<div class="box grid-box">
    
<div class="card mb-5 mb-xl-10 p-4 shadow-lg">
    <div class="row align-items-center">
        <!-- Left Column: Avatar -->
        <div class="col-md-1 text-center">
            <div class="position-relative d-inline-block">
                @php
                    $defaultImage = asset("images/businessman-icon.jpg");
                    $avatarPath = @$user->avatar;    
                    $avatar = getImagePath($avatarPath) ?? $defaultImage;
        
                    if (!isImageExists($avatar)) {
                        $avatar = $defaultImage;
                    }
                @endphp
                <div class="mb-3">
                    <img src="{{ $avatar }}" alt="Profile Picture" 
                         class="border shadow" width="100" height="100" 
                         style="border-radius: 20px; object-fit: cover;">
                </div>
                
                <!-- Online Status -->
                <span class="position-absolute bottom-0 end-0 bg-success border border-light rounded-circle" 
                      style="width: 16px; height: 16px;"></span>
            </div>
        </div>
        

        <!-- Right Column: User Info -->
        <div class="col-md-9">
            <!-- User Name & Badge -->
            <h4 class="fw-bold mb-1 d-flex align-items-center" style="color: rgb(235, 141, 8);">
                {{ $user->name ?? '' }}
                <i class="ki-duotone ki-verify fs-5 text-primary ms-2"></i>
            </h4>

            <!-- UUID and Email in Two Columns -->
            <div class="row text-muted fw-semibold">
                <!-- UUID -->
                <div class="col-md-2 d-flex align-items-center">
                    <img src="{{ asset('images/uuid.jpg') }}" alt="UUID" width="20" class="me-1">
                    <span>{{ $user->uuid ?? 'N/A' }}</span>
                </div>

                <!-- Email -->
                <div class="col-md-2 d-flex align-items-center">
                    <img src="{{ asset('images/email.jpg') }}" alt="Email" width="20" class="me-1">
                    <a href="mailto:{{ $user->email ?? '' }}" class="text-decoration-none text-muted">{{ $user->email ?? 'N/A' }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<br>

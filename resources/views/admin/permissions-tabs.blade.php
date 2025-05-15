

 @php
 use Illuminate\Support\Str;
 $grouped = $permissions->groupBy('category');
 $selected = $selectedPermissions ?? [];
@endphp

<!-- Hidden field to pass permissions -->
<input type="hidden" name="permissions_all" id="permissions_all">

<!-- Category Tabs -->
<ul class="nav nav-tabs mb-3" role="tablist" id="permission-tabs">
 @foreach($grouped as $category => $perms)
     <li class="nav-item">
         <a class="nav-link {{ $loop->first ? 'active' : '' }}"
            data-category="{{ __($category) }}"
            href="#">
             {{ $category }}
         </a>
     </li>
 @endforeach
</ul>

<!-- Permission Display Container -->
<div id="permissions-container">
 {{-- First tab content loaded on page --}}
 @php
     $firstCategory = $grouped->keys()->first();
     $firstPermissions = $grouped[$firstCategory];
     $chunked = $firstPermissions->chunk(ceil($firstPermissions->count() / 3));
 @endphp
 <div class="row">
     @foreach($chunked as $chunk)
         <div class="col-md-4">
             @foreach($chunk as $perm)
                 <div class="form-check mb-2">
                     <input class="form-check-input permission-checkbox"
                            type="checkbox"
                            value="{{ $perm->id }}"
                            id="perm-{{ $perm->id }}"
                            {{ in_array($perm->id, $selected) ? 'checked' : '' }}>
                     <label class="form-check-label" for="perm-{{ $perm->id }}">
                         {{  __($perm->name) }}
                     </label>
                 </div>
             @endforeach
         </div>
     @endforeach
 </div>
</div>

<script>
 $(function () {
     let selectedPermissions = new Set(@json($selected ?? []));

     function updateHiddenInput() {
         $('#permissions_all').val([...selectedPermissions].join(','));
     }

     // Save selected values into Set
     function bindPermissionCheckboxes() {
         $('.permission-checkbox').on('change', function () {
             const id = parseInt($(this).val());
             if ($(this).is(':checked')) {
                 selectedPermissions.add(id);
             } else {
                 selectedPermissions.delete(id);
             }
             updateHiddenInput();
         });
     }

     // Initial bind
     bindPermissionCheckboxes();
     updateHiddenInput();

     // Load permissions for tab
     $('#permission-tabs .nav-link').on('click', function (e) {
         e.preventDefault();
         const category = $(this).data('category');
         $('#permission-tabs .nav-link').removeClass('active');
         $(this).addClass('active');

         $.get(`/admin/permissions/category/${encodeURIComponent(category)}`, function (response) {
             const perms = response.permissions;
             let html = '<div class="row">';
             const chunkSize = Math.ceil(perms.length / 3);
             for (let i = 0; i < 3; i++) {
                 html += '<div class="col-md-4">';
                 perms.slice(i * chunkSize, (i + 1) * chunkSize).forEach(perm => {
                     const checked = selectedPermissions.has(perm.id) ? 'checked' : '';
                     html += `
                         <div class="form-check mb-2">
                             <input class="form-check-input permission-checkbox"
                                    type="checkbox"
                                    value="${perm.id}"
                                    id="perm-${perm.id}"
                                    ${checked}>
                             <label class="form-check-label" for="perm-${perm.id}">
                                 ${ perm.name}
                             </label>
                         </div>
                     `;
                 });
                 html += '</div>';
             }
             html += '</div>';
             $('#permissions-container').html(html);
             bindPermissionCheckboxes();
             updateHiddenInput();
         });
     });
 });
</script>

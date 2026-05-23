<!-- Cleanup Duplicate Devices Modal -->
<div class="modal fade" id="cleanupDevicesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title">
                    <i class="fa fa-trash"></i>
                    تنظيف الحسابات المكررة على نفس الجهاز
                </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Preview Section -->
                <div id="cleanup-preview-section">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>معاينة:</strong> اضغط على "عرض المعاينة" لمشاهدة الحسابات التي سيتم حذفها.
                    </div>

                    <button type="button" class="btn btn-info btn-lg" id="load-preview-btn">
                        <i class="fa fa-eye"></i> عرض المعاينة
                    </button>

                    <div id="preview-loading" style="display:none; text-align:center; margin:20px;">
                        <i class="fa fa-spinner fa-spin fa-3x"></i>
                        <p>جاري التحميل...</p>
                    </div>

                    <div id="preview-results" style="display:none; margin-top:20px;">
                        <!-- Preview results will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-danger btn-lg" id="run-cleanup-btn" style="display:none;">
                    <i class="fa fa-trash"></i> تنفيذ الحذف
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let previewData = null;

    // Load Preview
    $('#load-preview-btn').click(function() {
        $('#preview-loading').show();
        $('#preview-results').hide();
        $('#run-cleanup-btn').hide();

        $.ajax({
            url: '/admin/cleanup-duplicate-devices/preview',
            type: 'GET',
            success: function(response) {
                $('#preview-loading').hide();

                if (response.status && response.data.devices.length > 0) {
                    previewData = response.data;
                    renderPreview(response.data);
                    $('#run-cleanup-btn').show();
                } else {
                    $('#preview-results').html(`
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i>
                            <strong>رائع!</strong> لا توجد حسابات زيادة تحتاج للحذف.
                        </div>
                    `).show();
                }
            },
            error: function(xhr) {
                $('#preview-loading').hide();
                let errorMsg = xhr.responseJSON?.message || 'حدث خطأ';
                $('#preview-results').html(`
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>خطأ:</strong> ${errorMsg}
                    </div>
                `).show();
            }
        });
    });

    // Render Preview
    function renderPreview(data) {
        let html = `
            <div class="alert alert-warning">
                <h4><i class="fa fa-exclamation-triangle"></i> ملخص المعاينة</h4>
                <ul>
                    <li><strong>عدد الأجهزة المتأثرة:</strong> ${data.total_devices_affected}</li>
                    <li><strong>إجمالي الحسابات التي سيتم حذفها:</strong> ${data.total_users_to_delete}</li>
                    <li><strong>الحد المسموح:</strong> ${data.allowed_accounts_per_device} حساب لكل جهاز</li>
                </ul>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary">
                        <tr>
                            <th>Device Token</th>
                            <th>إجمالي الحسابات</th>
                            <th>سيتم الحذف</th>
                            <th>الحسابات المحذوفة</th>
                            <th>الحسابات المتبقية</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.devices.forEach(device => {
            html += `
                <tr>
                    <td><code>${device.device_token.substring(0, 20)}...</code></td>
                    <td><span class="badge bg-blue">${device.total_accounts}</span></td>
                    <td><span class="badge bg-red">${device.to_delete_count}</span></td>
                    <td>
                        ${device.users_to_delete.map(u => `
                            <div style="margin:5px 0; padding:5px; background:#ffe6e6; border-left:3px solid red;">
                                <strong>${u.name || 'بدون اسم'}</strong><br>
                                <small>
                                    ID: ${u.id} | UUID: ${u.uuid}<br>
                                    ${u.phone || u.email || 'بدون بيانات'}<br>
                                    تاريخ الإنشاء: ${u.created_at}
                                    ${u.is_logout ? '<span class="label label-warning">Logout</span>' : '<span class="label label-success">Active</span>'}
                                </small>
                            </div>
                        `).join('')}
                    </td>
                    <td>
                        ${device.users_to_keep.map(u => `
                            <div style="margin:5px 0; padding:5px; background:#e6ffe6; border-left:3px solid green;">
                                <strong>${u.name || 'بدون اسم'}</strong><br>
                                <small>
                                    ID: ${u.id} | UUID: ${u.uuid}<br>
                                    ${u.phone || u.email || 'بدون بيانات'}<br>
                                    تاريخ الإنشاء: ${u.created_at}
                                </small>
                            </div>
                        `).join('')}
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        $('#preview-results').html(html).show();
    }

    // Run Cleanup
    $('#run-cleanup-btn').click(function() {
        if (!confirm('⚠️ هل أنت متأكد من حذف هذه الحسابات؟ هذا الإجراء لا يمكن التراجع عنه!')) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> جاري الحذف...');

        $.ajax({
            url: '/admin/cleanup-duplicate-devices/run',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status) {
                    toastr.success(`تم حذف ${response.data.total_users_deleted} حساب بنجاح!`);

                    // Show results
                    $('#preview-results').html(`
                        <div class="alert alert-success">
                            <h4><i class="fa fa-check-circle"></i> تم التنفيذ بنجاح!</h4>
                            <ul>
                                <li><strong>الأجهزة المعالجة:</strong> ${response.data.devices_processed}</li>
                                <li><strong>الحسابات المحذوفة:</strong> ${response.data.total_users_deleted}</li>
                                <li><strong>user_accounts المحذوفة:</strong> ${response.data.total_user_accounts_deleted}</li>
                                <li><strong>Tokens المحذوفة:</strong> ${response.data.total_tokens_deleted}</li>
                            </ul>
                        </div>
                    `);

                    btn.hide();

                    // Reload page after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    toastr.error(response.message || 'حدث خطأ');
                    btn.prop('disabled', false).html('<i class="fa fa-trash"></i> تنفيذ الحذف');
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.message || 'حدث خطأ';
                toastr.error(errorMsg);
                btn.prop('disabled', false).html('<i class="fa fa-trash"></i> تنفيذ الحذف');
            }
        });
    });
});
</script>

<style>
#cleanupDevicesModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}
#cleanupDevicesModal .table {
    font-size: 12px;
}
#cleanupDevicesModal .table td {
    vertical-align: top;
}
</style>

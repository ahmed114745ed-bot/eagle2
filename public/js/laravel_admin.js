$(document).ready(function () {
    $('.view-description').click(function (e) {
        e.preventDefault();

        var description = $(this).data('description');

        $('#modalDescriptionTitle').text("Full Description");
        $('#modalDescriptionContent').text(description);

        $('#descriptionModal').modal('show');
    });

    $('.view-image').click(function (e) {
        e.preventDefault();
        var imgSrc = $(this).data('img');
        $('#modalImageContent').attr('src', imgSrc);
        $('#imageModal').modal('show');
    });

    $(document).on('click', '[data-toggle="modal"]', function () {
       setTimeout(function () {
           $('html, body').animate({ scrollTop: 300 }, 300);
       }, 200);
    });
});

$(document).on('ajaxComplete', function (event, xhr, settings) {
    if (xhr.responseJSON && xhr.responseJSON.data) {
        let data = xhr.responseJSON.data;

        let btn = $('.grid-row-action[data-key="' + data.id + '"] a');
        if (btn.length) {
            btn.find('i').attr('class', data.icon);
            btn.find('span').text(data.label);
        }
    }
});



document.addEventListener('DOMContentLoaded', function () {

    function sendRequest(url) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': LA.token,
                'Accept': 'application/json',
            },
        }).then(res => res.json());
    }

    function handleAction(button, actionType) {
        button.addEventListener('click', function(e){
            e.preventDefault();

            // رسائل متعددة اللغات
            const messages = {
                approve: {
                    title: 'هل أنت متأكد من الموافقة على هذا الطلب؟',
                    confirm: 'نعم',
                    cancel: 'إلغاء',
                    color: '#28a745'
                },
                reject: {
                    title: 'هل أنت متأكد من الرفض على هذا الطلب؟',
                    confirm: 'نعم',
                    cancel: 'إلغاء',
                    color: '#dc3545'
                },
                success: {
                    en: 'Action completed successfully!',
                    ar: 'تمت العملية بنجاح!',
                    hi: 'क्रिया सफलतापूर्वक पूरी हुई!',
                    tr: 'İşlem başarıyla tamamlandı!'
                },
                error: {
                    en: 'An error occurred!',
                    ar: 'حدث خطأ أثناء العملية',
                    hi: 'एक त्रुटि हुई!',
                    tr: 'İşlem sırasında hata oluştu!'
                }
            };

            const locale = document.documentElement.lang || 'ar'; // افتراض لغة الموقع

            Swal.fire({
                title: messages[actionType].title,
                type: 'question',
                showCancelButton: true,
                confirmButtonText: messages[actionType].confirm,
                cancelButtonText: messages[actionType].cancel,
                confirmButtonColor: messages[actionType].color,
                cancelButtonColor: '#6c757d',
            }).then((result) => {

                if(result.value){
                    const url = button.dataset.url;
                    sendRequest(url).then(res => {
                        if(res.success){
                            Swal.fire({
                                title: res.message || messages.success[locale],
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            button.closest('tr').remove(); // إزالة الصف بعد العملية
                        } else {
                            Swal.fire('خطأ', res.message || messages.error[locale], 'error');
                        }
                    }).catch(() => {
                        Swal.fire('خطأ', messages.error[locale], 'error');
                    });
                }
            });
        });
    }

    document.querySelectorAll('.approve-btn').forEach(btn => handleAction(btn, 'approve'));
    document.querySelectorAll('.reject-btn').forEach(btn => handleAction(btn, 'reject'));
});



if (window.__countryMapInitialized) return;
window.__countryMapInitialized = true;

function loadScriptsSequentially(scripts, callback) {
    if (!scripts.length) return callback();
    const [first, ...rest] = scripts;
    $.getScript(first)
        .done(() => loadScriptsSequentially(rest, callback))
        .fail((xhr, status, error) => {
        
            console.error('[Map Error] فشل تحميل:', first, error);
            window.location.reload();
        });
}

const scripts = [
    'https://cdn.jsdelivr.net/npm/jvectormap-next/jquery-jvectormap.min.js',
    'https://cdn.jsdelivr.net/npm/jvectormap-content/world-mill.js'
];

loadScriptsSequentially(scripts, initWorldMap);
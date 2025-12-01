<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>اختبار إرسال الهدايا</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: Tahoma; background:#f7f7f7; padding:30px }
.card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1) }
pre { background:#222; color:#0f0; padding:15px; border-radius:8px; white-space:pre-wrap; max-height:400px; overflow:auto }
.success { color:green; font-weight:bold }
.fail { color:red; font-weight:bold }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2 class="mb-4">🚀 اختبار إرسال الهدايا (Load Test)</h2>

        <form action="{{ route('gift.test.run') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>رابط API</label>
                <input type="text" name="url" class="form-control" placeholder="https://eagle.utdsoftware.com" value="https://eagle.utdsoftware.com" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>عدد الطلبات</label>
                    <input type="number" name="count" class="form-control" value="100" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>عدد المتزامنين (Concurrency)</label>
                    <input type="number" name="concurrency" class="form-control" value="10" required>
                </div>
            </div>

            <div class="mb-3">
                <label>Authorization Token</label>
                <input type="text" name="token" class="form-control" placeholder="Bearer xxxxxxxx" required>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>ID</label>
                    <input type="number" name="id" class="form-control" value="445" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Owner ID</label>
                    <input type="number" name="owner_id" class="form-control" value="303" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>toUid</label>
                    <input type="number" name="toUid" class="form-control" value="303" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>num</label>
                    <input type="number" name="num" class="form-control" value="1" required>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">ابدأ الاختبار 🚀</button>
        </form>
    </div>

    @if(isset($results))
    <div class="card mt-4">
        <h3>📊 نتائج الاختبار</h3>

        <p class="success">الناجحة: {{ $results['success'] }}</p>
        <p class="fail">الفاشلة: {{ $results['failed'] }}</p>

        <h4>📥 تفاصيل الريسبونس</h4>
        <pre>{{ json_encode($results['errors'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif
</div>

</body>
</html>

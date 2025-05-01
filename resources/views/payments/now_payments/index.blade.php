
      
 

@section('content')
<style>
    body {
        font-family: 'Cairo', sans-serif;
        background: #f4f6f9;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 500px;
        margin: 50px auto;
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }

    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #333;
    }

    label {
        font-weight: bold;
        margin-bottom: 8px;
        display: block;
        color: #555;
    }

    input[type="number"],
    select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        margin-bottom: 20px;
        transition: border-color 0.3s;
        font-size: 16px;
    }

    input[type="number"]:focus,
    select:focus {
        border-color: #007bff;
        outline: none;
    }

    button {
        width: 100%;
        padding: 14px;
        border: none;
        background-color: #007bff;
        color: #fff;
        font-size: 17px;
        border-radius: 10px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #0056b3;
    }

    option {
        font-size: 15px;
    }
</style>

<div class="container">
    <h2>إجراء الدفع</h2>

    <form action="{{ route('now_payment_create') }}" method="POST">
        @csrf

        <label for="amount">المبلغ (بالدولار الأمريكي)</label>
        <input type="number" name="amount" id="amount" required min="1" step="0.01">

        <label for="currency">اختر العملة الرقمية</label>
        <select name="currency" id="currency" required>
            <option value="">-- اختر عملة --</option>
            @foreach($currencies as $currency)
                <option value="{{ $currency['currency'] }}">
                    {{ strtoupper($currency['currency']) }} (Min: {{ $currency['min_amount'] }}, Max: {{ $currency['max_amount'] }})
                </option>
            @endforeach
        </select>

        <button type="submit">إنشاء الدفع</button>
    </form>
</div>

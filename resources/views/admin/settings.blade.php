
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')
        @php
            $selectedTimeZone = App\Models\Setting::where('key','timezone')->first();
        @endphp
        <label for="">TimeZone</label>
        <select name="timezone" id="">
                @foreach ($timezones as $timezone)
                <option value="{{ $timezone->name }}" {{ $timezone->name == $selectedTimeZone->value? 'selected' : '' }}>
                    {{ $timezone->name }}</option>
                    @endforeach
            </select>

        <button type="submit">Update</button>
    </form>
</body>
</html>

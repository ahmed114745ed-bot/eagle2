<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: white;
            display: flex;
        }
        .settings-sidebar {
            width: 250px;
            background: #222;
            min-height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
        }
        .settings-sidebar h2 {
            text-align: center;
            color: #ff9800;
        }
        .settings-content {
            flex-grow: 1;
            width: 1200px;
            padding: 20px;
        }
        form {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 900px;
            margin: auto;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            background: #333;
            border: 1px solid #444;
            color: white;
        }
        button {
            padding: 10px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
        }

        .avatar {
            text-align: center;
            margin-bottom: 20px;
        }
        .avatar img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid #ff9800;
            cursor: pointer;
        }
        .avatar input {
            display: none;
        }
    </style>
</head>
<body>
    <div class="settings-content">
        <form action="{{ '/auth/setting' }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="avatar">
                <label for="avatarInput">
                    <img id="avatarPreview" src="{{ asset(Auth::user()->avatar) }}" alt="Avatar">
                </label>
                <input type="file" id="avatarInput" name="avatar" accept="image/*" onchange="previewAvatar(event)">
            </div>
            <label for="name">{{ __('admin.username') }}</label>
            <input type="text" id="name" name="username" value="{{ old('username', Auth::user()->username) }}" required>
            <label for="name">{{ __('Name') }}</label>
            <input type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
            <label for="password">{{ __('Password') }}</label>
            <input type="password" id="password" name="password" value="{{ Auth::user()->password }}">
            <label for="password_confirmation">{{ __('Confirm Password') }}</label>
            <input type="password" id="password_confirmation" name="password_confirmation" value="{{ Auth::user()->password }}">
            <button type="submit">{{ __('admin.save') }}</button>
        </form>
    </div>
    <script>
        function previewAvatar(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('avatarPreview').src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>

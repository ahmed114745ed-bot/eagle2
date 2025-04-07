<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Profile</title>
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
            padding: 20px;
            display: flex;
            width: 1200px;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 800px;
            text-align: center;
        }
        .avatar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #ff9800;
            margin-bottom: 15px;
        }
        .details {
            text-align: left;
            margin-top: 10px;
        }
        .details p {
            margin: 5px 0;
            font-size: 16px;
        }
        .details strong {
            color: #ff9800;
        }
        button {
            background: #ff9800;
            padding: 10px;
            border: none;
            cursor: pointer;
            color: black;
            font-weight: bold;
            width: 100%;
            margin-top: 15px;
        }
        button:hover {
            background: #e68900;
        }
    </style>
</head>
<body>
    <div class="settings-content">
        <div class="container">
            <div class="avatar">
                <img src="{{ asset(@$agency->img) }}" alt="Agency Logo">
            </div>
            <h2>{{ $agency->name }}</h2>
            <div class="details">
                <p><strong>{{__("ID")}}:</strong> {{ $agency->id }}</p>
                <p><strong>{{__("Phone")}}:</strong> {{ @$agency->phone ?? '' }}</p>
                <p><strong>{{__("Owner")}}:</strong> {{ @$agency?->owner?->name ?? '' }}, UUID: {{ @$agency?->owner?->uuid ?? '' }}</p>
                <p><strong>{{__("Notice")}}:</strong> {{ @$agency->notice ?? '' }}</p>
            </div>
            <button onclick="window.history.back()">Go Back</button>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: white;
            display: flex;
            flex-direction: column;
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
        .main-content {
            display: flex;
            flex-direction: column;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        .agency-container, .charge-container {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto 20px;
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
        
        /* Table styles */
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #444;
        }
        th {
            background-color: #333;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .settings-sidebar {
                width: 100%;
                min-height: auto;
            }
            .container, .agency-container, .charge-container {
                padding: 15px;
            }
            .avatar img {
                width: 80px;
                height: 80px;
            }
            table {
                font-size: 14px;
            }
            th, td {
                padding: 6px 4px;
            }
        }
        
        @media (max-width: 480px) {
            .details p {
                font-size: 14px;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 4px 2px;
            }
        }

        .privileges-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.privilege-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.privilege-item img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 8px;
}

.privilege-name {
    text-align: center;
    font-size: 14px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .privileges-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .privileges-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .privileges-grid {
        grid-template-columns: 1fr;
    }
}
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <div class="avatar-wrapper">
                @php
                    $url = getImagePath($oVip->img);
                @endphp
                {!! handleShowImageWithTypes($oVip->id, $url, 300, 300) !!}
            </div>

            <button onclick="window.history.back()">{{ __("Go Back") }}</button>
        </div>


        <div class="agency-container">
            <div class="card">
                <div class="privileges-grid">
                    @foreach($oVip->privilegs as $privilege)
                        <div class="privilege-item">
                            <img src="{{ asset($privilege->image) }}" alt="{{ $privilege->name }}">
                            <div class="privilege-name">{{ $privilege->name }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        
    </div>
</body>
</html>

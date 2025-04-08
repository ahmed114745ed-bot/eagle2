<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        canvas {
            background-color: transparent !important;
            transform: none !important;
        }
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
            width: 90%;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            height: 80%;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        .agency-container, .charge-container {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 200%;
            max-width: 1300px;
            margin: 0 auto 20px;
            text-align: center;
        }
        .avatar img {
            width: 200px;
            height: 200px;
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

        /* Main Container */
/* .agency-container {
    width: 100%;
    padding: 20px;
} */

/* Card Styling */
.card {
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(187, 109, 7, 0.1);
    padding: 25px;
    max-width: 1200px;
    margin: 0 auto;
}

.card-title {
    text-align: center;
    margin-bottom: 25px;
    font-size: 3rem;
    color: #da7116;
}

.privileges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px; /* Increased gap between items */
    justify-items: center;
}

.privilege-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    gap: 15px; /* This creates consistent space between image and name */
}

.image-container {
    width: 150px; /* Fixed size for circular container */
    height: 150px;
    position: relative;
    overflow: hidden;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.privilege-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border: 3px solid #ff9800;
    transition: transform 0.3s ease;
}

.privilege-item:hover .privilege-img {
    transform: scale(1.05);
}

.privilege-name {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 500;
    color: #c87121;
    width: 100%;
    margin-top: 10px; /* Additional spacing control */
    padding: 0 10px; /* Prevents text from touching edges */
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .privileges-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }
    .image-container {
        width: 130px;
        height: 130px;
    }
}

@media (max-width: 768px) {
    .privileges-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 25px;
    }
    .image-container {
        width: 120px;
        height: 120px;
    }
    .privilege-name {
        font-size: 1.3rem;
    }
}

@media (max-width: 576px) {
    .privileges-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .card {
        padding: 15px;
    }
    .image-container {
        width: 100px;
        height: 100px;
    }
    .privilege-name {
        font-size: 1.1rem;
    }
}
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <div class="avatar">
                @php
                    $url = getImagePath($oVip->img);
                @endphp
                {!! handleShowImageWithTypes($oVip->id, $url, 300, 300) !!}
            </div>

            {{-- <button onclick="window.history.back()">{{ __("Go Back") }}</button> --}}
        </div>
     <br>

        <div class="agency-container">
            <div class="card">
                <h4 class="card-title text-center">{{ __('ovip Privileges') }}</h4>
                <div class="privileges-grid">
                    @foreach($oVip->privilegs as $privilege)
                        <div class="privilege-item">
                            <div class="image-container">
                                <img src="{{ getImagePath($privilege->img1) }}"  class="privilege-img">
                            </div>
                            <div class="privilege-name text-center">{{ $privilege->name }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        
    </div>
</body>
</html>

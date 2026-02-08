<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Agency Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .field {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            color: #666;
        }
        .field-value {
            color: #333;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Agency Registration</h1>
    </div>
    
    <div class="content">
        <h2>Agency Information</h2>
        
        @if(is_object($agency))
            <div class="field">
                <span class="field-label">Agency Name:</span>
                <span class="field-value">{{ $agency->name ?? 'N/A' }}</span>
            </div>
            
            <div class="field">
                <span class="field-label">Phone:</span>
                <span class="field-value">{{ $agency->phone ?? 'N/A' }}</span>
            </div>
            
            <div class="field">
                <span class="field-label">Email:</span>
                <span class="field-value">{{ $agency->email ?? 'N/A' }}</span>
            </div>
            
            <div class="field">
                <span class="field-label">Country:</span>
                <span class="field-value">{{ $agency->country->name ?? 'N/A' }}</span>
            </div>
            
            <div class="field">
                <span class="field-label">Salary:</span>
                <span class="field-value">{{ $agency->salary ?? 'N/A' }}</span>
            </div>
            
            <div class="field">
                <span class="field-label">Host Count:</span>
                <span class="field-value">{{ $agency->host ?? 'N/A' }}</span>
            </div>
            
            <div class="field">
                <span class="field-label">Registration Date:</span>
                <span class="field-value">{{ $agency->created_at ?? now() }}</span>
            </div>
        @elseif(is_array($agency))
            <div class="field">
                <span class="field-label">Agency Name:</span>
                <span class="field-value">{{ $agency['name'] ?? 'N/A' }}</span>
            </div>
            
            <div class="field">
                <span class="field-label">Phone:</span>
                <span class="field-value">{{ $agency['phone'] ?? 'N/A' }}</span>
            </div>
            
            <div class="field">
                <span class="field-label">Email:</span>
                <span class="field-value">{{ $agency['email'] ?? 'N/A' }}</span>
            </div>
        @endif
        
        @if($additionalInfo)
            <h3>Additional Information</h3>
            @if(is_object($additionalInfo))
                <div class="field">
                    <span class="field-label">Face Image:</span>
                    <span class="field-value">Uploaded</span>
                </div>
                
                <div class="field">
                    <span class="field-label">Back Image:</span>
                    <span class="field-value">Uploaded</span>
                </div>
            @endif
        @endif
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply.</p>
        <p>&copy; {{ date('Y') }} Agency Management System</p>
    </div>
</body>
</html>

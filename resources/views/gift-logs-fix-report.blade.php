<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gift Logs Fix Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .stat-card h3 {
            font-size: 0.9em;
            opacity: 0.9;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .value {
            font-size: 2.5em;
            font-weight: 700;
        }

        .stat-card.total-diff {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .table-section {
            margin-bottom: 40px;
        }

        .table-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #667eea;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            color: #555;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .user-id {
            font-weight: 600;
            color: #667eea;
        }

        .number {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }

        .diff-positive {
            color: #28a745;
            font-weight: 600;
        }

        .diff-negative {
            color: #dc3545;
            font-weight: 600;
        }

        .diff-zero {
            color: #999;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .pagination button {
            padding: 10px 15px;
            border: 1px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination button:hover {
            background: #667eea;
            color: white;
        }

        .pagination button.active {
            background: #667eea;
            color: white;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px 40px;
            text-align: center;
            color: #666;
            border-top: 1px solid #e9ecef;
        }

        .footer p {
            margin: 5px 0;
            font-size: 0.9em;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 1em;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #e9ecef;
            color: #333;
        }

        .btn-secondary:hover {
            background: #dee2e6;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-info {
            background: #e7f3ff;
            border-color: #667eea;
            color: #004085;
        }

        .alert-warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .no-data p {
            font-size: 1.1em;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 1.8em;
            }

            .content {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 0.9em;
            }

            th, td {
                padding: 10px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
        }

        .search-box button {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .search-box button:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎁 Gift Logs Fix Report</h1>
            <p>Diamond Send vs Gift Logs Discrepancy Analysis</p>
        </div>

        <div class="content">
            <div class="alert alert-info">
                <strong>ℹ️ Dry-Run Mode:</strong> This is a preview of the discrepancies found. No changes have been made to the database.
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users Analyzed</h3>
                    <div class="value">{{ $total_users }}</div>
                </div>
                <div class="stat-card">
                    <h3>Users with Discrepancies</h3>
                    <div class="value">{{ count($users) }}</div>
                </div>
                <div class="stat-card total-diff">
                    <h3>Total Difference (Diamonds)</h3>
                    <div class="value">{{ number_format($total_diff, 0) }}</div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="/gift-logs-fix-total-diff/run" class="btn btn-primary">▶️ Execute Fix</a>
                <a href="/gift-logs-fix-total-diff/preview" class="btn btn-secondary">🔄 Refresh Report</a>
            </div>

            @if(count($users) > 0)
                <div class="table-section">
                    <h2>Discrepancy Details</h2>
                    
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search by User ID...">
                        <button onclick="filterTable()">Search</button>
                    </div>

                    <div class="table-wrapper">
                        <table id="dataTable">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th class="number">Total Diamond Send</th>
                                    <th class="number">Gift Logs Sum</th>
                                    <th class="number">Difference</th>
                                    <th class="number">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td class="user-id">{{ $user['user_id'] }}</td>
                                        <td class="number">{{ number_format($user['total_diamond_send'], 2) }}</td>
                                        <td class="number">{{ number_format($user['gift_logs_sum'], 2) }}</td>
                                        <td class="number">
                                            @if($user['diff'] > 0)
                                                <span class="diff-positive">+{{ number_format($user['diff'], 2) }}</span>
                                            @elseif($user['diff'] < 0)
                                                <span class="diff-negative">{{ number_format($user['diff'], 2) }}</span>
                                            @else
                                                <span class="diff-zero">0.00</span>
                                            @endif
                                        </td>
                                        <td class="number">
                                            @if($user['total_diamond_send'] > 0)
                                                {{ number_format(($user['diff'] / $user['total_diamond_send']) * 100, 2) }}%
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="table-section">
                    <h2>Raw Command Output</h2>
                    <pre style="background: #f8f9fa; padding: 20px; border-radius: 8px; overflow-x: auto; border: 1px solid #e9ecef;">{{ $output }}</pre>
                </div>
            @else
                <div class="no-data">
                    <p>✅ No discrepancies found!</p>
                    <p>All users have matching total_diamond_send and gift_logs totals.</p>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>📊 Report Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
            <p>🔗 <a href="https://eagle.utdsoftware.com/gift-logs-fix-total-diff/preview" style="color: #667eea; text-decoration: none;">View Live Report</a></p>
        </div>
    </div>

    <script>
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('dataTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const userId = rows[i].getElementsByClassName('user-id')[0];
                if (userId) {
                    const txtValue = userId.textContent || userId.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        }

        // Allow Enter key to trigger search
        document.getElementById('searchInput').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                filterTable();
            }
        });
    </script>
</body>
</html>

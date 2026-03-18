<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\File;

class TestLuckyGiftConcurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:lucky-gift-concurrency {--count=10 : Number of concurrent requests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test send-lucky-gift-combo API concurrency and generate HTML report';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = (int)$this->option('count');
        $this->info("🚀 Starting $count concurrent requests to Send Lucky Gift Combo...");

        $url = "https://eagle.utdsoftware.com/api/gifts/v2/send-lucky-gift-combo";
        $authToken = "11240|WEJCUAnswBbDPkdfmu2fa7otBeFgHHgTgNLanjuTc09cc74f";
        $requestBody = [
            "room_id" => "19",
            "id" => "384",
            "toUid" => "1174",
            "num" => "1",
            "count" => "5"
        ];

        $client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $authToken,
                'Accept' => 'application/json',
            ],
            'verify' => false, // Disable SSL verification for testing if needed
        ]);

        $promises = [];
        $startTime = microtime(true);

        for ($i = 0; $i < $count; $i++) {
            $promises[$i] = $client->postAsync($url, [
                'json' => $requestBody
            ]);
        }

        $this->info("⏳ Waiting for responses...");
        $results = Promise\Utils::settle($promises)->wait();
        $endTime = microtime(true);
        $totalDuration = ($endTime - $startTime) * 1000;

        $reports = [];
        $successCount = 0;
        $errorCount = 0;
        $lockCount = 0;

        foreach ($results as $index => $result) {
            $data = [
                'id' => $index + 1,
                'status' => 'unknown',
                'response_code' => 0,
                'response_body' => '',
                'message' => '',
                'is_lock_error' => false,
                'time' => date('H:i:s.u'),
            ];

            if ($result['state'] === 'fulfilled') {
                $response = $result['value'];
                $data['response_code'] = $response->getStatusCode();
                $body = (string)$response->getBody();
                $data['response_body'] = $body;
                $json = json_decode($body, true);

                if (isset($json['success']) && $json['success']) {
                    $data['status'] = 'success';
                    $data['message'] = $json['message'] ?? 'Done';
                    $successCount++;
                } else {
                    $data['status'] = 'error';
                    $data['message'] = $json['message'] ?? 'Failed';
                    if (strpos($data['message'], 'api_responses.try_again') !== false || 
                        strpos($data['message'], 'try again') !== false || 
                        strpos($data['message'], 'الرجاء المحاولة مرة أخرى') !== false) {
                        $data['is_lock_error'] = true;
                        $lockCount++;
                    } else {
                        $errorCount++;
                    }
                }
            } else {
                $data['status'] = 'exception';
                $exception = $result['reason'];
                if ($exception instanceof RequestException && $exception->hasResponse()) {
                    $response = $exception->getResponse();
                    $data['response_code'] = $response->getStatusCode();
                    $body = (string)$response->getBody();
                    $data['response_body'] = $body;
                    $json = json_decode($body, true);
                    $data['message'] = $json['message'] ?? $exception->getMessage();
                    
                    if (strpos($data['message'], 'api_responses.try_again') !== false || 
                        strpos($data['message'], 'try again') !== false || 
                        strpos($data['message'], 'الرجاء المحاولة مرة أخرى') !== false) {
                        $data['is_lock_error'] = true;
                        $lockCount++;
                    } else {
                        $errorCount++;
                    }
                } else {
                    $data['message'] = $exception->getMessage();
                    $errorCount++;
                }
            }
            $reports[] = $data;
        }

        $this->info("📊 Results: Success: $successCount, Lock Errors: $lockCount, Other Errors: $errorCount");
        
        $reportPath = $this->generateHtmlReport($reports, $successCount, $lockCount, $errorCount, $totalDuration);
        $this->info("📄 Report generated at: $reportPath");

        return 0;
    }

    private function generateHtmlReport($reports, $successCount, $lockCount, $errorCount, $totalDuration)
    {
        $rows = "";
        foreach ($reports as $report) {
            $statusClass = $report['status'] === 'success' ? 'bg-success text-white' : ($report['is_lock_error'] ? 'bg-warning text-dark' : 'bg-danger text-white');
            $rows .= "<tr>
                <td>{$report['id']}</td>
                <td>{$report['time']}</td>
                <td><span class='badge {$statusClass}'>{$report['status']}</span></td>
                <td>{$report['response_code']}</td>
                <td>{$report['message']}</td>
                <td><pre style='max-height: 100px; overflow: auto; font-size: 10px;'>" . htmlspecialchars($report['response_body']) . "</pre></td>
            </tr>";
        }

        $html = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Concurrency Test Report</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .card { margin-bottom: 20px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success-bg { background-color: #d1e7dd; }
        .warning-bg { background-color: #fff3cd; }
        .danger-bg { background-color: #f8d7da; }
        h1 { color: #333; font-weight: 700; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 Concurrency Test Report</h1>
        
        <div class='row'>
            <div class='col-md-3'>
                <div class='card text-center p-3 success-bg'>
                    <h3>Success</h3>
                    <p class='display-6'>$successCount</p>
                </div>
            </div>
            <div class='col-md-3'>
                <div class='card text-center p-3 warning-bg'>
                    <h3>Lock Errors</h3>
                    <p class='display-6'>$lockCount</p>
                    <small>Database Locking Failure</small>
                </div>
            </div>
            <div class='col-md-3'>
                <div class='card text-center p-3 danger-bg'>
                    <h3>Other Errors</h3>
                    <p class='display-6'>$errorCount</p>
                </div>
            </div>
            <div class='col-md-3'>
                <div class='card text-center p-3 bg-info text-white'>
                    <h3>Duration</h3>
                    <p class='display-6'>" . round($totalDuration, 2) . "ms</p>
                </div>
            </div>
        </div>

        <div class='card'>
            <div class='card-header bg-dark text-white'>
                Detailed Request Log
            </div>
            <div class='card-body p-0'>
                <table class='table table-hover table-striped mb-0'>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>HTTP</th>
                            <th>Message</th>
                            <th>Response Body</th>
                        </tr>
                    </thead>
                    <tbody>
                        $rows
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>";

        $fileName = "concurrency_report_" . date('Y-m-d_H-i-s') . ".html";
        $directory = public_path('reports');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;
        File::put($filePath, $html);

        return $filePath;
    }
}

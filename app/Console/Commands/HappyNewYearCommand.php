<?php

namespace App\Console\Commands;

use App\Jobs\AllOpeningRoomsZegoRequest;
use Illuminate\Console\Command;

class HappyNewYearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:happy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this for push map to all oping room';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $d     = [
            "messageContent" => [
                "message"     => "HappyNewYearVideo",
            ]
        ];
        $json  = json_encode($d);

        dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, 1, 1, false ), 'heavyProcessing');
    }
}

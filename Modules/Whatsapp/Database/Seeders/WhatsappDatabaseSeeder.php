<?php

namespace Modules\Whatsapp\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Whatsapp\Entities\WhatsappApp;

class WhatsappDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        WhatsappApp::query()->create([
                                         'uuid'        => uuid_create(),
                                         'username'    => 'TopStarTet',
                                         'password'    => '123',
                                         'webhook_url' => 'https://test.2opstar.com/api/whatsapp-webhook'
                                     ]);

        WhatsappApp::query()->create([
                                         'uuid'        => uuid_create(),
                                         'username'    => 'soulfnaTest',
                                         'password'    => '123',
                                         'webhook_url' => 'https://test.soalafna.com/api/whatsapp-webhook'
                                     ]);
        // $this->call("OthersTableSeeder");
    }
}

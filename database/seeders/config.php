<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class config extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            $configs = array(
                array('id' => '1','name' => 'min_tx_num','value' => '1000','desc' => 'الحد الادنى للسحب لليوزر العادي','created_at' => '2022-11-26 23:57:24','updated_at' => '2023-01-10 14:41:59'),
                array('id' => '8','name' => 'login_from_only_one_device','value' => 'yes','desc' => 'الدخول من هاتف واحد','created_at' => '2023-03-05 12:10:19','updated_at' => '2023-03-05 12:10:19'),
                array('id' => '9','name' => 'one_usd_value_in_coins','value' => '800','desc' => 'واحد دولار يساوي كم كوين','created_at' => '2023-04-06 13:28:54','updated_at' => '2023-05-24 17:55:49'),
                array('id' => '10','name' => 'zego_server_secret','value' => '5b00cbb22911ff114a940f6c0a4bf044','desc' => NULL,'created_at' => '2023-04-18 14:37:24','updated_at' => '2023-05-30 10:29:30'),
                array('id' => '11','name' => 'zego_app_id','value' => '1992574259','desc' => NULL,'created_at' => '2023-04-18 14:38:11','updated_at' => '2023-05-30 10:29:43'),
                array('id' => '12','name' => 'twilio_sid','value' => 'AC13580ffac6b187e3c7a264c860c222b2','desc' => NULL,'created_at' => '2023-04-18 14:40:19','updated_at' => '2023-04-26 12:29:18'),
                array('id' => '13','name' => 'twilio_api_key','value' => 'c5b894f6933d6b784102ac0c02a55d75','desc' => NULL,'created_at' => '2023-04-18 14:40:35','updated_at' => '2023-04-26 12:32:01'),
                array('id' => '14','name' => 'twilio_from','value' => '+16812215865','desc' => NULL,'created_at' => '2023-04-18 14:40:51','updated_at' => '2023-04-26 12:33:41'),
                array('id' => '15','name' => 'twilio_service','value' => 'MGa33bd5d7ef2be67e94cfde5df01c84fe','desc' => NULL,'created_at' => '2023-05-01 17:54:18','updated_at' => '2023-05-01 17:54:18'),
                array('id' => '16','name' => 'boss_id','value' => '1912','desc' => 'ايدي الرئيسي','created_at' => '2023-05-24 13:39:28','updated_at' => '2023-05-24 17:57:37'),
                array('id' => '17','name' => 'all_target_or_nothing','value' => 'true','desc' => 'لا يتم احتساب التارجيت اذا لم يكمل وقت البث','created_at' => '2023-05-24 17:55:51','updated_at' => '2023-05-24 17:58:28'),
                array('id' => '18','name' => 'group_chat','value' => '1','desc' => NULL,'created_at' => '2023-05-25 13:56:29','updated_at' => '2023-05-25 14:01:55'),
                array('id' => '19','name' => 'all_target_or_nothing','value' => 'true','desc' => NULL,'created_at' => '2023-06-12 15:45:32','updated_at' => '2023-06-12 15:45:32'),
                array('id' => '20','name' => 'room_rule','value' => 'الرجاء من المستخدمين الكرام التحلي بالاخلاق مع الاخرين شاكرين تفهمكم, اهلا وسهلا بكم','desc' => NULL,'created_at' => '2023-06-13 16:58:12','updated_at' => '2023-06-13 16:58:12'),
                array('id' => '21','name' => 'cost_request_backround','value' => '2000','desc' => NULL,'created_at' => '2023-06-17 15:13:47','updated_at' => '2023-06-17 15:13:47'),
                array('id' => '43','name' => 'max_room_admin','value' => '100','desc' => 'الحد الأقصي لعدد المشرفين','created_at' => '2023-06-17 15:13:47','updated_at' => '2023-06-17 15:13:47')
              );
        
        DB::table ('configs')->insert ($configs);
    }
}

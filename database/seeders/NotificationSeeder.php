<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\NotificationTranslation;
use Cache;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('notifications')->truncate();
        DB::table('notification_translations')->truncate();

        // Insert notification

        /* ----------------------------------------sender level --------------------------------------------- */
        $sender_level = DB::table('notifications')->insertGetId([
            'key' => 'sender_level'
        ]);

        // Insert translations in batch
        DB::table('notification_translations')->insert([
            [
                'notification_id' => $sender_level,
                'title' => 'Sender level upgraded',
                'message' => 'Congratulations you reach sender level {level}',
                'language' => 'en'
            ],
            [
                'notification_id' => $sender_level,
                'title' => 'ترقية مستوى المرسل',
                'message' => 'تهانينا لقد وصلت لمستوى المرسل {level}',
                'language' => 'ar'
            ],
            [
                'notification_id' => $sender_level,
                'title' => 'प्रेषक स्तर उन्नत किया गया',
                'message' => 'बधाई हो आप प्रेषक स्तर पर पहुंच गए हैं {level}',
                'language' => 'hi'
            ],
            [
                'notification_id' => $sender_level,
                'title' => 'Gönderici düzeyi yükseltildi',
                'message' => 'Tebrikler gönderici seviyesine ulaştınız {level}',
                'language' => 'tu' // Fixed language code
            ]
        ]);

        /* ---------------------------------------receiver level ----------------------------------------- */

        $receiver_level = DB::table('notifications')->insertGetId([
            'key' => 'receiver_level'
        ]);

        // Insert translations in batch
        DB::table('notification_translations')->insert([
            [
                'notification_id' => $receiver_level,
                'title' => 'Receiver level upgraded',
                'message' => 'Congratulations you reach receiver level {level}',
                'language' => 'en'
            ],
            [
                'notification_id' => $receiver_level,
                'title' => 'ترقية مستوى المتلقى',
                'message' => 'تهانينا لقد وصلت لمستوى المتلقى {level}',
                'language' => 'ar'
            ],
            [
                'notification_id' => $receiver_level,
                'title' => 'रिसीवर स्तर उन्नत किया गया',
                'message' => 'बधाई हो आप रिसीवर स्तर तक पहुंच गए हैं {level}',
                'language' => 'hi'
            ],
            [
                'notification_id' => $receiver_level,
                'title' => 'Alıcı seviyesi yükseltildi',
                'message' => 'Tebrikler alıcı seviyesine ulaştınız {level}',
                'language' => 'tu' // Fixed language code
            ]
        ]);

        /* ----------------------------------background request --------------------------------- */

        $background_request_accept = DB::table('notifications')->insertGetId([
            'key' => 'background_accept'
        ]);

        // Insert translations in batch
        DB::table('notification_translations')->insert([
            [
                'notification_id' => $background_request_accept,
                'title' => '',
                'message' => "Your background image request has been accepted.",
                'language' => 'en'
            ],
            [
                'notification_id' => $background_request_accept,
                'title' => '',
                'message' => "تم قبول طلب صوره الخلفية",
                'language' => 'ar'
            ],
            [
                'notification_id' => $background_request_accept,
                'title' => '',
                'message' => "आपका पृष्ठभूमि छवि अनुरोध स्वीकार कर लिया गया है।",
                'language' => 'hi'
            ],
            [
                'notification_id' => $background_request_accept,
                'title' => '',
                'message' => "Arka plan resmi isteğiniz kabul edildi.",
                'language' => 'tu' // Fixed language code
            ]
        ]);



        $background_request_refuse = DB::table('notifications')->insertGetId([
            'key' => 'background_refuse'
        ]);

        // Insert translations in batch
        DB::table('notification_translations')->insert([
            [
                'notification_id' => $background_request_refuse,
                'title' => '',
                'message' => "background photo request was denied.",
                'language' => 'en'
            ],
            [
                'notification_id' => $background_request_refuse,
                'title' => '',
                'message' => "تم رفض طلب صوره الخلفية",
                'language' => 'ar'
            ],
            [
                'notification_id' => $background_request_refuse,
                'title' => '',
                'message' => "पृष्ठभूमि फोटो अनुरोध अस्वीकार कर दिया गया",
                'language' => 'hi'
            ],
            [
                'notification_id' => $background_request_refuse,
                'title' => '',
                'message' => "arka plan fotoğrafı isteği reddedildi",
                'language' => 'tu' // Fixed language code
            ]
        ]);



        /* ----------------------------------target ---------------------------------------------------- */

        $target = DB::table('notifications')->insertGetId([
            'key' => 'target'
        ]);

        // Insert translations in batch
        DB::table('notification_translations')->insert([
            [
                'notification_id' => $target,
                'title' => 'New Target',
                'message' => "Congrats! you achieve new target in {agency} your salary now is {salary}",
                'language' => 'en'
            ],
            [
                'notification_id' => $target,
                'title' => 'هدف جديد',
                'message' => "مبروك! لقد حققت هدفًا جديدًا في {agency} راتبك الآن هو {salary}",
                'language' => 'ar'
            ],
            [
                'notification_id' => $target,
                'title' => 'नया लक्ष्य',
                'message' => "बधाई हो! आपने {agency} में नया लक्ष्य हासिल कर लिया है, अब आपका वेतन {salary} है",
                'language' => 'hi'
            ],
            [
                'notification_id' => $target,
                'title' => 'Yeni Hedef',
                'message' => "Tebrikler! {agency} şirketinde yeni bir hedefe ulaştınız. Maaşınız artık {salary}",
                'language' => 'tu'
            ]
        ]);


        /* ------------------------------------- comment moment ----------------------------------------------- */

        $comment_moment = DB::table('notifications')->insertGetId([
            'key' => 'comment_moment'
        ]);

        // Insert translations in batch
        DB::table('notification_translations')->insert([
            [
                'notification_id' => $comment_moment,
                'title' => 'moment Comment',
                'message' => "{user_name} commented on your moment",
                'language' => 'en'
            ],
            [
                'notification_id' => $comment_moment,
                'title' => "لحظة التعليق",
                'message' => ' قام {user_name} بالتفاعل على اللحظة الخاصة بك',
                'language' => 'ar'
            ],
            [
                'notification_id' => $comment_moment,
                'title' => 'क्षण टिप्पणी',
                'message' =>"{user_name} ने आपके पल पर टिप्पणी की",
                'language' => 'hi'
            ],
            [
                'notification_id' => $comment_moment,
                'title' => 'an Yorum',
                'message' => "{user_name} anınıza yorum yaptı",
                'language' => 'tu'
            ]
        ]);


        /* ----------------------------------- like real ------------------------------------ */

        $like_real = DB::table('notifications')->insertGetId([
            'key' => 'like_real'
        ]);

        // Insert translations in batch
        DB::table('notification_translations')->insert([
            [
                'notification_id' => $like_real,
                'title' => 'like real',
                'message' => '{user_name} reacted your real',
                'language' => 'en'
            ],
            [
                'notification_id' => $like_real,
                'title' => "الاعجاب بفيديو",
                'message' => 'قام {user_name} بالتفاعل على الفيديو الخاصة بك',
                'language' => 'ar'
            ],
            [
                'notification_id' => $like_real,
                'title' => 'असली जैसा',
                'message' =>'{user_name} ने आपकी वास्तविक प्रतिक्रिया दी',
                'language' => 'hi'
            ],
            [
                'notification_id' => $like_real,
                'title' => 'gerçek gibi',
                'message' => '{user_name} gerçek tepkinizi verdi',
                'language' => 'tu'
            ]
        ]);


        /* ------------------------------- comment_real ----------------------------------- */

        $comment_real = DB::table('notifications')->insertGetId([
            'key' => 'comment_real'
        ]);

        // Insert translations in batch
        DB::table('notification_translations')->insert([
            [
                'notification_id' => $comment_real,
                'title' => 'like real',
                'message' => '{user_name} commented on your real',
                'language' => 'en'
            ],
            [
                'notification_id' => $comment_real,
                'title' => "الاعجاب بفيديو",
                'message' => ' قام {user_name} بالتفاعل على الفيديو الخاصة بك',
                'language' => 'ar'
            ],
            [
                'notification_id' => $comment_real,
                'title' => 'असली जैसा',
                'message' =>'{user_name} ने आपके असली नाम पर टिप्पणी की है',
                'language' => 'hi'
            ],
            [
                'notification_id' => $comment_real,
                'title' => 'gerçek gibi',
                'message' => '{user_name} sizin gerçek fotoğrafınıza yorum yaptı',
                'language' => 'tu'
            ]
        ]);

        /* ----------------------------------- Like Moment ---------------------------------------- */

        $like_moment = DB::table('notifications')->insertGetId([
            'key' => 'like_moment'
        ]);

        // Insert translations in batch
        DB::table('notification_translations')->insert([
            [
                'notification_id' => $like_moment,
                'title' => 'like moment',
                'message' =>'{user_name} reacted your moment',
                'language' => 'en'
            ],
            [
                'notification_id' => $like_moment,
                'title' => "الاعجاب بلحظة",
                'message' => '  قام {user_name} بالتفاعل على اللحظة الخاصة بك',
                'language' => 'ar'
            ],
            [
                'notification_id' => $like_moment,
                'title' => 'जैसे पल',
                'message' =>'{user_name} ने आपके पल पर प्रतिक्रिया दी',
                'language' => 'hi'
            ],
            [
                'notification_id' => $like_moment,
                'title' => 'an gibi',
                'message' => '{user_name} anınıza tepki gösterdi',
                'language' => 'tu'
            ]
        ]);

        /* -------------------------------- accept agency ------------------------------------ */

        $accept_agency = DB::table('notifications')->insertGetId([
            'key' => 'accept_agency'
        ]);

        // Insert translations in batch
        DB::table('notification_translations')->insert([
            [
                'notification_id' => $accept_agency,
                'title' => '',
                'message' =>'Congrats! Your request to join {agency_name} agency is accepted',
                'language' => 'en'
            ],
            [
                'notification_id' => $accept_agency,
                'title' => '',
                'message' =>  'مبروك لقد تم قبول طلب الانضمام وكاله {agency_name}',
                'language' => 'ar'
            ],
            [
                'notification_id' => $accept_agency,
                'title' => '',
                'message' =>'बधाई हो! {agency_name} एजेंसी में शामिल होने का आपका अनुरोध स्वीकार कर लिया गया है',
                'language' => 'hi'
            ],
            [
                'notification_id' => $accept_agency,
                'title' => '',
                'message' => 'Tebrikler! {agency_name} ajansına katılma isteğiniz kabul edildi',
                'language' => 'tu'
            ]
        ]);

        $notifications = Notification::all();
        foreach($notifications as $n){
            Cache::forever($n->key, $n->translations->toArray());
        }
    }
}

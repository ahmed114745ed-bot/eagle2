<?php

namespace Database\Seeders;

use App\Models\Emoji;
use App\Models\EmojiCategory;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmojiCategorySeeder extends Seeder
{
    public function run(): void
    {

        $emojiCategory = EmojiCategory::create([
            'title' => [
                'en' => 'default',
                'ar' => 'افتراضي',
                'tr' => 'Varsayılan',
                'hi' => 'डिफ़ॉल्ट',
                'id' => 'default',
            ],
            'type' => 'default',
        ]);

        Emoji::whereNull('emoji_category_id')
            ->update(['emoji_category_id' => $emojiCategory->id]);
    }
}

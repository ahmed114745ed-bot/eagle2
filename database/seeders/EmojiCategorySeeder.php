<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryCategory;
use App\Models\Emoji;
use App\Models\EmojiCategory;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmojiCategorySeeder extends Seeder
{
    public function run(): void
    {
        $emojiCategory = EmojiCategory::where('type', 'default')->first();
        if (!$emojiCategory) {
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
        }


        Emoji::whereNull('emoji_category_id')
            ->update(['emoji_category_id' => $emojiCategory->id]);

        $emojiCategory = CountryCategory::where('type', 'default')->first();
        if (!$emojiCategory) {
            $emojiCategory = CountryCategory::create([
                'title' => [
                    'en' => 'default',
                    'ar' => 'افتراضي',
                    'tr' => 'Varsayılan',
                    'hi' => 'डिफ़ॉल्ट',
                    'id' => 'default',
                ],
                'type' => 'default',
            ]);
        }

        Country::whereNull('country_category_id')
            ->update(['country_category_id' => $emojiCategory->id]);
    }
}

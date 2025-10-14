<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanUpDuplicateCountriesSeeder extends Seeder
{
    public function run()
    {
        $this->command->info("=== Step 1: Cleaning up duplicates ===");

        $handledIds = [];

        // نجيب الدول كلها بالترتيب من الأقدم للأحدث
        $countries = Country::orderBy('id')->get();

        foreach ($countries as $base) {
            // لو الدولة دي اتعالجت قبل كده، نتخطاها
            if (in_array($base->id, $handledIds)) {
                continue;
            }

            // نجيب أي دولة تانية شبهها في الاسم الإنجليزي أو العربي
            $similarOnes = Country::where('id', '!=', $base->id)
                ->where(function ($query) use ($base) {
                    $query->where('e_name', 'like', '%' . $base->e_name . '%')
                        ->orWhere('name', 'like', '%' . $base->name . '%');
                })
                ->orderBy('id')
                ->get();

            if ($similarOnes->isEmpty()) {
                continue;
            }

            // نجهز المجموعة (الأقدم ثم الأحدث)
            $group = collect([$base])->merge($similarOnes)->sortBy('id');
            $keep = $group->first();       // الأقدم نحتفظ به
            $remove = $group->slice(1);    // الباقي نحذفه

            foreach ($remove as $country) {
                // محظور نحذف السجل الأساسي (احتياطي)
                if ($country->id == $keep->id) {
                    $this->command->error("⚠️ Tried to remove kept record ID {$keep->id}!");
                    continue;
                }

                // ممكن هنا تحدث السجل القديم ببيانات الجديد لو فيه نواقص
                $keep->iso        = $keep->iso ?: $country->iso;
                $keep->iso3       = $keep->iso3 ?: $country->iso3;
                $keep->name       = $keep->name ?: $country->name;
                $keep->e_name     = strlen($keep->e_name) < strlen($country->e_name)
                    ? $country->e_name : $keep->e_name;
                $keep->status     = $keep->status ?: $country->status;
                $keep->phone_code = $keep->phone_code ?: $country->phone_code;
                $keep->save();

                // نحذف الدولة المكررة (الأحدث)
                $country->delete();
                $this->command->warn("🗑️ Removed duplicate: {$country->e_name} (ID {$country->id})");

                // سجلنا إنها اتعالجت
                $handledIds[] = $country->id;
            }

            // سجلنا إن الأقدم اتعامل معاه بالفعل
            $handledIds[] = $keep->id;
            $this->command->info("✅ Kept oldest: {$keep->e_name} (ID {$keep->id})");
        }

        $this->command->info("🎉 Duplicate cleanup complete! All older records preserved.");

        $this->command->info("=== Step 2: Applying known fixes ===");

        $countries = array(
            array('iso' => 'AF', 'name' => 'Afghanistan', 'name_ar' => 'أفغانستان', 'iso3' => 'AFG', 'numcode' => '4', 'phonecode' => '93'),
            array('iso' => 'AL', 'name' => 'Albania', 'name_ar' => 'ألبانيا', 'iso3' => 'ALB', 'numcode' => '8', 'phonecode' => '355'),
            array('iso' => 'DZ', 'name' => 'Algeria', 'name_ar' => 'الجزائر', 'iso3' => 'DZA', 'numcode' => '12', 'phonecode' => '213'),
            array('iso' => 'AS', 'name' => 'American Samoa', 'name_ar' => 'ساموا الأمريكية', 'iso3' => 'ASM', 'numcode' => '16', 'phonecode' => '1684'),
            array('iso' => 'AD', 'name' => 'Andorra', 'name_ar' => 'أندورا', 'iso3' => 'AND', 'numcode' => '20', 'phonecode' => '376'),
            array('iso' => 'AO', 'name' => 'Angola', 'name_ar' => 'أنغولا', 'iso3' => 'AGO', 'numcode' => '24', 'phonecode' => '244'),
            array('iso' => 'AI', 'name' => 'Anguilla', 'name_ar' => 'أنغيلا', 'iso3' => 'AIA', 'numcode' => '660', 'phonecode' => '1264'),
            array('iso' => 'AQ', 'name' => 'Antarctica', 'name_ar' => 'أنتاركتيكا', 'iso3' => NULL, 'numcode' => NULL, 'phonecode' => '0'),
            array('iso' => 'AG', 'name' => 'Antigua and Barbuda', 'name_ar' => 'أنتيغوا وبربودا', 'iso3' => 'ATG', 'numcode' => '28', 'phonecode' => '1268'),
            array('iso' => 'AR', 'name' => 'Argentina', 'name_ar' => 'الأرجنتين', 'iso3' => 'ARG', 'numcode' => '32', 'phonecode' => '54'),
            array('iso' => 'AM', 'name' => 'Armenia', 'name_ar' => 'أرمينيا', 'iso3' => 'ARM', 'numcode' => '51', 'phonecode' => '374'),
            array('iso' => 'AW', 'name' => 'Aruba', 'name_ar' => 'أروبا', 'iso3' => 'ABW', 'numcode' => '533', 'phonecode' => '297'),
            array('iso' => 'AU', 'name' => 'Australia', 'name_ar' => 'أستراليا', 'iso3' => 'AUS', 'numcode' => '36', 'phonecode' => '61'),
            array('iso' => 'AT', 'name' => 'Austria', 'name_ar' => 'النمسا', 'iso3' => 'AUT', 'numcode' => '40', 'phonecode' => '43'),
            array('iso' => 'AZ', 'name' => 'Azerbaijan', 'name_ar' => 'أذربيجان', 'iso3' => 'AZE', 'numcode' => '31', 'phonecode' => '994'),
            array('iso' => 'BS', 'name' => 'Bahamas', 'name_ar' => 'جزر البهاما', 'iso3' => 'BHS', 'numcode' => '44', 'phonecode' => '1242'),
            array('iso' => 'BH', 'name' => 'Bahrain', 'name_ar' => 'البحرين', 'iso3' => 'BHR', 'numcode' => '48', 'phonecode' => '973'),
            array('iso' => 'BD', 'name' => 'Bangladesh', 'name_ar' => 'بنغلاديش', 'iso3' => 'BGD', 'numcode' => '50', 'phonecode' => '880'),
            array('iso' => 'BB', 'name' => 'Barbados', 'name_ar' => 'بربادوس', 'iso3' => 'BRB', 'numcode' => '52', 'phonecode' => '1246'),
            array('iso' => 'BY', 'name' => 'Belarus', 'name_ar' => 'بيلاروسيا', 'iso3' => 'BLR', 'numcode' => '112', 'phonecode' => '375'),
            array('iso' => 'BE', 'name' => 'Belgium', 'name_ar' => 'بلجيكا', 'iso3' => 'BEL', 'numcode' => '56', 'phonecode' => '32'),
            array('iso' => 'BZ', 'name' => 'Belize', 'name_ar' => 'بليز', 'iso3' => 'BLZ', 'numcode' => '84', 'phonecode' => '501'),
            array('iso' => 'BJ', 'name' => 'Benin', 'name_ar' => 'بنين', 'iso3' => 'BEN', 'numcode' => '204', 'phonecode' => '229'),
            array('iso' => 'BM', 'name' => 'Bermuda', 'name_ar' => 'برمودا', 'iso3' => 'BMU', 'numcode' => '60', 'phonecode' => '1441'),
            array('iso' => 'BT', 'name' => 'Bhutan', 'name_ar' => 'بوتان', 'iso3' => 'BTN', 'numcode' => '64', 'phonecode' => '975'),
            array('iso' => 'BO', 'name' => 'Bolivia', 'name_ar' => 'بوليفيا', 'iso3' => 'BOL', 'numcode' => '68', 'phonecode' => '591'),
            array('iso' => 'BA', 'name' => 'Bosnia and Herzegovina', 'name_ar' => 'البوسنة والهرسك', 'iso3' => 'BIH', 'numcode' => '70', 'phonecode' => '387'),
            array('iso' => 'BW', 'name' => 'Botswana', 'name_ar' => 'بوتسوانا', 'iso3' => 'BWA', 'numcode' => '72', 'phonecode' => '267'),
            array('iso' => 'BV', 'name' => 'Bouvet Island', 'name_ar' => 'جزيرة بوفيه', 'iso3' => NULL, 'numcode' => NULL, 'phonecode' => '0'),
            array('iso' => 'BR', 'name' => 'Brazil', 'name_ar' => 'البرازيل', 'iso3' => 'BRA', 'numcode' => '76', 'phonecode' => '55'),
            array('iso' => 'IO', 'name' => 'British Indian Ocean Territory', 'name_ar' => 'إقليم المحيط الهندي البريطاني', 'iso3' => NULL, 'numcode' => NULL, 'phonecode' => '246'),
            array('iso' => 'BN', 'name' => 'Brunei Darussalam', 'name_ar' => 'بروناي دار السلام', 'iso3' => 'BRN', 'numcode' => '96', 'phonecode' => '673'),
            array('iso' => 'BG', 'name' => 'Bulgaria', 'name_ar' => 'بلغاريا', 'iso3' => 'BGR', 'numcode' => '100', 'phonecode' => '359'),
            array('iso' => 'BF', 'name' => 'Burkina Faso', 'name_ar' => 'بوركينا فاسو', 'iso3' => 'BFA', 'numcode' => '854', 'phonecode' => '226'),
            array('iso' => 'BI', 'name' => 'Burundi', 'name_ar' => 'بوروندي', 'iso3' => 'BDI', 'numcode' => '108', 'phonecode' => '257'),
            array('iso' => 'KH', 'name' => 'Cambodia', 'name_ar' => 'كمبوديا', 'iso3' => 'KHM', 'numcode' => '116', 'phonecode' => '855'),
            array('iso' => 'CM', 'name' => 'Cameroon', 'name_ar' => 'الكاميرون', 'iso3' => 'CMR', 'numcode' => '120', 'phonecode' => '237'),
            array('iso' => 'CA', 'name' => 'Canada', 'name_ar' => 'كندا', 'iso3' => 'CAN', 'numcode' => '124', 'phonecode' => '1'),
            array('iso' => 'CV', 'name' => 'Cape Verde', 'name_ar' => 'الرأس الأخضر', 'iso3' => 'CPV', 'numcode' => '132', 'phonecode' => '238'),
            array('iso' => 'KY', 'name' => 'Cayman Islands', 'name_ar' => 'جزر كايمان', 'iso3' => 'CYM', 'numcode' => '136', 'phonecode' => '1345'),
            array('iso' => 'CF', 'name' => 'Central African Republic', 'name_ar' => 'جمهورية أفريقيا الوسطى', 'iso3' => 'CAF', 'numcode' => '140', 'phonecode' => '236'),
            array('iso' => 'TD', 'name' => 'Chad', 'name_ar' => 'تشاد', 'iso3' => 'TCD', 'numcode' => '148', 'phonecode' => '235'),
            array('iso' => 'CL', 'name' => 'Chile', 'name_ar' => 'تشيلي', 'iso3' => 'CHL', 'numcode' => '152', 'phonecode' => '56'),
            array('iso' => 'CN', 'name' => 'China', 'name_ar' => 'الصين', 'iso3' => 'CHN', 'numcode' => '156', 'phonecode' => '86'),
            array('iso' => 'CX', 'name' => 'Christmas Island', 'name_ar' => 'جزيرة كريسماس', 'iso3' => NULL, 'numcode' => NULL, 'phonecode' => '61'),
            array('iso' => 'CC', 'name' => 'Cocos (Keeling) Islands', 'name_ar' => 'جزر كوكوس (كيلينغ)', 'iso3' => NULL, 'numcode' => NULL, 'phonecode' => '672'),
            array('iso' => 'CO', 'name' => 'Colombia', 'name_ar' => 'كولومبيا', 'iso3' => 'COL', 'numcode' => '170', 'phonecode' => '57'),
            array('iso' => 'KM', 'name' => 'Comoros', 'name_ar' => 'جزر القمر', 'iso3' => 'COM', 'numcode' => '174', 'phonecode' => '269'),
            array('iso' => 'CG', 'name' => 'Congo', 'name_ar' => 'الكونغو', 'iso3' => 'COG', 'numcode' => '178', 'phonecode' => '242'),
            array('iso' => 'CD', 'name' => 'Congo, the Democratic Republic of the', 'name_ar' => 'جمهورية الكونغو الديمقراطية', 'iso3' => 'COD', 'numcode' => '180', 'phonecode' => '242'),
            array('iso' => 'CK', 'name' => 'Cook Islands', 'name_ar' => 'جزر كوك', 'iso3' => 'COK', 'numcode' => '184', 'phonecode' => '682'),
            array('iso' => 'CR', 'name' => 'Costa Rica', 'name_ar' => 'كوستاريكا', 'iso3' => 'CRI', 'numcode' => '188', 'phonecode' => '506'),
            array('iso' => 'CI', 'name' => 'Cote D\'Ivoire', 'name_ar' => 'ساحل العاج', 'iso3' => 'CIV', 'numcode' => '384', 'phonecode' => '225'),
            array('iso' => 'HR', 'name' => 'Croatia', 'name_ar' => 'كرواتيا', 'iso3' => 'HRV', 'numcode' => '191', 'phonecode' => '385'),
            array('iso' => 'CU', 'name' => 'Cuba', 'name_ar' => 'كوبا', 'iso3' => 'CUB', 'numcode' => '192', 'phonecode' => '53'),
            array('iso' => 'CY', 'name' => 'Cyprus', 'name_ar' => 'قبرص', 'iso3' => 'CYP', 'numcode' => '196', 'phonecode' => '357'),
            array('iso' => 'CZ', 'name' => 'Czech Republic', 'name_ar' => 'جمهورية التشيك', 'iso3' => 'CZE', 'numcode' => '203', 'phonecode' => '420'),
            array('iso' => 'DK', 'name' => 'Denmark', 'name_ar' => 'الدنمارك', 'iso3' => 'DNK', 'numcode' => '208', 'phonecode' => '45'),
            array('iso' => 'DJ', 'name' => 'Djibouti', 'name_ar' => 'جيبوتي', 'iso3' => 'DJI', 'numcode' => '262', 'phonecode' => '253'),
            array('iso' => 'DM', 'name' => 'Dominica', 'name_ar' => 'دومينيكا', 'iso3' => 'DMA', 'numcode' => '212', 'phonecode' => '1767'),
            array('iso' => 'DO', 'name' => 'Dominican Republic', 'name_ar' => 'جمهورية الدومينيكان', 'iso3' => 'DOM', 'numcode' => '214', 'phonecode' => '1809'),
            array('iso' => 'EC', 'name' => 'Ecuador', 'name_ar' => 'الإكوادور', 'iso3' => 'ECU', 'numcode' => '218', 'phonecode' => '593'),
            array('iso' => 'EG', 'name' => 'Egypt', 'name_ar' => 'مصر', 'iso3' => 'EGY', 'numcode' => '818', 'phonecode' => '20'),
            array('iso' => 'SV', 'name' => 'El Salvador', 'name_ar' => 'السلفادور', 'iso3' => 'SLV', 'numcode' => '222', 'phonecode' => '503'),
            array('iso' => 'GQ', 'name' => 'Equatorial Guinea', 'name_ar' => 'غينيا الاستوائية', 'iso3' => 'GNQ', 'numcode' => '226', 'phonecode' => '240'),
            array('iso' => 'ER', 'name' => 'Eritrea', 'name_ar' => 'إريتريا', 'iso3' => 'ERI', 'numcode' => '232', 'phonecode' => '291'),
            array('iso' => 'EE', 'name' => 'Estonia', 'name_ar' => 'إستونيا', 'iso3' => 'EST', 'numcode' => '233', 'phonecode' => '372'),
            array('iso' => 'ET', 'name' => 'Ethiopia', 'name_ar' => 'إثيوبيا', 'iso3' => 'ETH', 'numcode' => '231', 'phonecode' => '251'),
            array('iso' => 'FK', 'name' => 'Falkland Islands (Malvinas)', 'name_ar' => 'جزر فوكلاند', 'iso3' => 'FLK', 'numcode' => '238', 'phonecode' => '500'),
            array('iso' => 'FO', 'name' => 'Faroe Islands', 'name_ar' => 'جزر فارو', 'iso3' => 'FRO', 'numcode' => '234', 'phonecode' => '298'),
            array('iso' => 'FJ', 'name' => 'Fiji', 'name_ar' => 'فيجي', 'iso3' => 'FJI', 'numcode' => '242', 'phonecode' => '679'),
            array('iso' => 'FI', 'name' => 'Finland', 'name_ar' => 'فنلندا', 'iso3' => 'FIN', 'numcode' => '246', 'phonecode' => '358'),
            array('iso' => 'FR', 'name' => 'France', 'name_ar' => 'فرنسا', 'iso3' => 'FRA', 'numcode' => '250', 'phonecode' => '33'),
            array('iso' => 'GF', 'name' => 'French Guiana', 'name_ar' => 'غيانا الفرنسية', 'iso3' => 'GUF', 'numcode' => '254', 'phonecode' => '594'),
            array('iso' => 'PF', 'name' => 'French Polynesia', 'name_ar' => 'بولينيزيا الفرنسية', 'iso3' => 'PYF', 'numcode' => '258', 'phonecode' => '689'),
            array('iso' => 'TF', 'name' => 'French Southern Territories', 'name_ar' => 'الأقاليم الجنوبية الفرنسية', 'iso3' => NULL, 'numcode' => NULL, 'phonecode' => '0'),
            array('iso' => 'GA', 'name' => 'Gabon', 'name_ar' => 'الغابون', 'iso3' => 'GAB', 'numcode' => '266', 'phonecode' => '241'),
            array('iso' => 'GM', 'name' => 'Gambia', 'name_ar' => 'غامبيا', 'iso3' => 'GMB', 'numcode' => '270', 'phonecode' => '220'),
            array('iso' => 'GE', 'name' => 'Georgia', 'name_ar' => 'جورجيا', 'iso3' => 'GEO', 'numcode' => '268', 'phonecode' => '995'),
            array('iso' => 'DE', 'name' => 'Germany', 'name_ar' => 'ألمانيا', 'iso3' => 'DEU', 'numcode' => '276', 'phonecode' => '49'),
            array('iso' => 'GH', 'name' => 'Ghana', 'name_ar' => 'غانا', 'iso3' => 'GHA', 'numcode' => '288', 'phonecode' => '233'),
            array('iso' => 'GI', 'name' => 'Gibraltar', 'name_ar' => 'جبل طارق', 'iso3' => 'GIB', 'numcode' => '292', 'phonecode' => '350'),
            array('iso' => 'GR', 'name' => 'Greece', 'name_ar' => 'اليونان', 'iso3' => 'GRC', 'numcode' => '300', 'phonecode' => '30'),
            array('iso' => 'GL', 'name' => 'Greenland', 'name_ar' => 'جرينلاند', 'iso3' => 'GRL', 'numcode' => '304', 'phonecode' => '299'),
            array('iso' => 'GD', 'name' => 'Grenada', 'name_ar' => 'غرينادا', 'iso3' => 'GRD', 'numcode' => '308', 'phonecode' => '1473'),
            array('iso' => 'GP', 'name' => 'Guadeloupe', 'name_ar' => 'جوادلوب', 'iso3' => 'GLP', 'numcode' => '312', 'phonecode' => '590'),
            array('iso' => 'GU', 'name' => 'Guam', 'name_ar' => 'غوام', 'iso3' => 'GUM', 'numcode' => '316', 'phonecode' => '1671'),
            array('iso' => 'GT', 'name' => 'Guatemala', 'name_ar' => 'غواتيمالا', 'iso3' => 'GTM', 'numcode' => '320', 'phonecode' => '502'),
            array('iso' => 'GN', 'name' => 'Guinea', 'name_ar' => 'غينيا', 'iso3' => 'GIN', 'numcode' => '324', 'phonecode' => '224'),
            array('iso' => 'GW', 'name' => 'Guinea-Bissau', 'name_ar' => 'غينيا بيساو', 'iso3' => 'GNB', 'numcode' => '624', 'phonecode' => '245'),
            array('iso' => 'GY', 'name' => 'Guyana', 'name_ar' => 'غيانا', 'iso3' => 'GUY', 'numcode' => '328', 'phonecode' => '592'),
            array('iso' => 'HT', 'name' => 'Haiti', 'name_ar' => 'هايتي', 'iso3' => 'HTI', 'numcode' => '332', 'phonecode' => '509'),
            array('iso' => 'HM', 'name' => 'Heard Island and Mcdonald Islands', 'name_ar' => 'جزيرة هيرد وجزر ماكدونالد', 'iso3' => NULL, 'numcode' => NULL, 'phonecode' => '0'),
            array('iso' => 'VA', 'name' => 'Holy See (Vatican City State)', 'name_ar' => 'الفاتيكان', 'iso3' => 'VAT', 'numcode' => '336', 'phonecode' => '39'),
            array('iso' => 'HN', 'name' => 'Honduras', 'name_ar' => 'هندوراس', 'iso3' => 'HND', 'numcode' => '340', 'phonecode' => '504'),
            array('iso' => 'HK', 'name' => 'Hong Kong', 'name_ar' => 'هونغ كونغ', 'iso3' => 'HKG', 'numcode' => '344', 'phonecode' => '852'),
            array('iso' => 'HU', 'name' => 'Hungary', 'name_ar' => 'المجر', 'iso3' => 'HUN', 'numcode' => '348', 'phonecode' => '36'),
            array('iso' => 'IS', 'name' => 'Iceland', 'name_ar' => 'آيسلندا', 'iso3' => 'ISL', 'numcode' => '352', 'phonecode' => '354'),
            array('iso' => 'IN', 'name' => 'India', 'name_ar' => 'الهند', 'iso3' => 'IND', 'numcode' => '356', 'phonecode' => '91'),
            array('iso' => 'ID', 'name' => 'Indonesia', 'name_ar' => 'إندونيسيا', 'iso3' => 'IDN', 'numcode' => '360', 'phonecode' => '62'),
            array('iso' => 'IR', 'name' => 'Iran, Islamic Republic of', 'name_ar' => 'إيران', 'iso3' => 'IRN', 'numcode' => '364', 'phonecode' => '98'),
            array('iso' => 'IQ', 'name' => 'Iraq', 'name_ar' => 'العراق', 'iso3' => 'IRQ', 'numcode' => '368', 'phonecode' => '964'),
            array('iso' => 'IE', 'name' => 'Ireland', 'name_ar' => 'أيرلندا', 'iso3' => 'IRL', 'numcode' => '372', 'phonecode' => '353'),
            array('iso' => 'IL', 'name' => 'Israel', 'name_ar' => 'إسرائيل', 'iso3' => 'ISR', 'numcode' => '376', 'phonecode' => '972'),
            array('iso' => 'IT', 'name' => 'Italy', 'name_ar' => 'إيطاليا', 'iso3' => 'ITA', 'numcode' => '380', 'phonecode' => '39'),
            array('iso' => 'JM', 'name' => 'Jamaica', 'name_ar' => 'جامايكا', 'iso3' => 'JAM', 'numcode' => '388', 'phonecode' => '1876'),
            array('iso' => 'JP', 'name' => 'Japan', 'name_ar' => 'اليابان', 'iso3' => 'JPN', 'numcode' => '392', 'phonecode' => '81'),
            array('iso' => 'JO', 'name' => 'Jordan', 'name_ar' => 'الأردن', 'iso3' => 'JOR', 'numcode' => '400', 'phonecode' => '962'),
            array('iso' => 'KZ', 'name' => 'Kazakhstan', 'name_ar' => 'كازاخستان', 'iso3' => 'KAZ', 'numcode' => '398', 'phonecode' => '7'),
            array('iso' => 'KE', 'name' => 'Kenya', 'name_ar' => 'كينيا', 'iso3' => 'KEN', 'numcode' => '404', 'phonecode' => '254'),
            array('iso' => 'KI', 'name' => 'Kiribati', 'name_ar' => 'كيريباتي', 'iso3' => 'KIR', 'numcode' => '296', 'phonecode' => '686'),
            array('iso' => 'KP', 'name' => 'Korea, Democratic People\'s Republic of', 'name_ar' => 'كوريا الشمالية', 'iso3' => 'PRK', 'numcode' => '408', 'phonecode' => '850'),
            array('iso' => 'KR', 'name' => 'Korea, Republic of', 'name_ar' => 'كوريا الجنوبية', 'iso3' => 'KOR', 'numcode' => '410', 'phonecode' => '82'),
            array('iso' => 'KW', 'name' => 'Kuwait', 'name_ar' => 'الكويت', 'iso3' => 'KWT', 'numcode' => '414', 'phonecode' => '965'),
            array('iso' => 'KG', 'name' => 'Kyrgyzstan', 'name_ar' => 'قيرغيزستان', 'iso3' => 'KGZ', 'numcode' => '417', 'phonecode' => '996'),
            array('iso' => 'LA', 'name' => 'Lao People\'s Democratic Republic', 'name_ar' => 'لاوس', 'iso3' => 'LAO', 'numcode' => '418', 'phonecode' => '856'),
            array('iso' => 'LV', 'name' => 'Latvia', 'name_ar' => 'لاتفيا', 'iso3' => 'LVA', 'numcode' => '428', 'phonecode' => '371'),
            array('iso' => 'LB', 'name' => 'Lebanon', 'name_ar' => 'لبنان', 'iso3' => 'LBN', 'numcode' => '422', 'phonecode' => '961'),
            array('iso' => 'LS', 'name' => 'Lesotho', 'name_ar' => 'ليسوتو', 'iso3' => 'LSO', 'numcode' => '426', 'phonecode' => '266'),
            array('iso' => 'LR', 'name' => 'Liberia', 'name_ar' => 'ليبيريا', 'iso3' => 'LBR', 'numcode' => '430', 'phonecode' => '231'),
            array('iso' => 'LY', 'name' => 'Libyan Arab Jamahiriya', 'name_ar' => 'ليبيا', 'iso3' => 'LBY', 'numcode' => '434', 'phonecode' => '218'),
            array('iso' => 'LI', 'name' => 'Liechtenstein', 'name_ar' => 'ليختنشتاين', 'iso3' => 'LIE', 'numcode' => '438', 'phonecode' => '423'),
            array('iso' => 'LT', 'name' => 'Lithuania', 'name_ar' => 'ليتوانيا', 'iso3' => 'LTU', 'numcode' => '440', 'phonecode' => '370'),
            array('iso' => 'LU', 'name' => 'Luxembourg', 'name_ar' => 'لوكسمبورغ', 'iso3' => 'LUX', 'numcode' => '442', 'phonecode' => '352'),
            array('iso' => 'MO', 'name' => 'Macao', 'name_ar' => 'ماكاو', 'iso3' => 'MAC', 'numcode' => '446', 'phonecode' => '853'),
            array('iso' => 'MK', 'name' => 'Macedonia, the Former Yugoslav Republic of', 'name_ar' => 'مقدونيا الشمالية', 'iso3' => 'MKD', 'numcode' => '807', 'phonecode' => '389'),
            array('iso' => 'MG', 'name' => 'Madagascar', 'name_ar' => 'مدغشقر', 'iso3' => 'MDG', 'numcode' => '450', 'phonecode' => '261'),
            array('iso' => 'MW', 'name' => 'Malawi', 'name_ar' => 'مالاوي', 'iso3' => 'MWI', 'numcode' => '454', 'phonecode' => '265'),
            array('iso' => 'MY', 'name' => 'Malaysia', 'name_ar' => 'ماليزيا', 'iso3' => 'MYS', 'numcode' => '458', 'phonecode' => '60'),
            array('iso' => 'MV', 'name' => 'Maldives', 'name_ar' => 'المالديف', 'iso3' => 'MDV', 'numcode' => '462', 'phonecode' => '960'),
            array('iso' => 'ML', 'name' => 'Mali', 'name_ar' => 'مالي', 'iso3' => 'MLI', 'numcode' => '466', 'phonecode' => '223'),
            array('iso' => 'MT', 'name' => 'Malta', 'name_ar' => 'مالطا', 'iso3' => 'MLT', 'numcode' => '470', 'phonecode' => '356'),
            array('iso' => 'MH', 'name' => 'Marshall Islands', 'name_ar' => 'جزر مارشال', 'iso3' => 'MHL', 'numcode' => '584', 'phonecode' => '692'),
            array('iso' => 'MQ', 'name' => 'Martinique', 'name_ar' => 'مارتينيك', 'iso3' => 'MTQ', 'numcode' => '474', 'phonecode' => '596'),
            array('iso' => 'MR', 'name' => 'Mauritania', 'name_ar' => 'موريتانيا', 'iso3' => 'MRT', 'numcode' => '478', 'phonecode' => '222'),
            array('iso' => 'MU', 'name' => 'Mauritius', 'name_ar' => 'موريشيوس', 'iso3' => 'MUS', 'numcode' => '480', 'phonecode' => '230'),
            array('iso' => 'YT', 'name' => 'Mayotte', 'name_ar' => 'مايوت', 'iso3' => NULL, 'numcode' => NULL, 'phonecode' => '269'),
            array('iso' => 'MX', 'name' => 'Mexico', 'name_ar' => 'المكسيك', 'iso3' => 'MEX', 'numcode' => '484', 'phonecode' => '52'),
            array('iso' => 'FM', 'name' => 'Micronesia, Federated States of', 'name_ar' => 'ولايات ميكرونيزيا المتحدة', 'iso3' => 'FSM', 'numcode' => '583', 'phonecode' => '691'),
            array('iso' => 'MD', 'name' => 'Moldova, Republic of', 'name_ar' => 'مولدوفا', 'iso3' => 'MDA', 'numcode' => '498', 'phonecode' => '373'),
            array('iso' => 'MC', 'name' => 'Monaco', 'name_ar' => 'موناكو', 'iso3' => 'MCO', 'numcode' => '492', 'phonecode' => '377'),
            array('iso' => 'MN', 'name' => 'Mongolia', 'name_ar' => 'منغوليا', 'iso3' => 'MNG', 'numcode' => '496', 'phonecode' => '976'),
            array('iso' => 'MS', 'name' => 'Montserrat', 'name_ar' => 'مونتسيرات', 'iso3' => 'MSR', 'numcode' => '500', 'phonecode' => '1664'),
            array('iso' => 'MA', 'name' => 'Morocco', 'name_ar' => 'المغرب', 'iso3' => 'MAR', 'numcode' => '504', 'phonecode' => '212'),
            array('iso' => 'MZ', 'name' => 'Mozambique', 'name_ar' => 'موزمبيق', 'iso3' => 'MOZ', 'numcode' => '508', 'phonecode' => '258'),
            array('iso' => 'MM', 'name' => 'Myanmar', 'name_ar' => 'ميانمار', 'iso3' => 'MMR', 'numcode' => '104', 'phonecode' => '95'),
            array('iso' => 'NA', 'name' => 'Namibia', 'name_ar' => 'ناميبيا', 'iso3' => 'NAM', 'numcode' => '516', 'phonecode' => '264'),
            array('iso' => 'NR', 'name' => 'Nauru', 'name_ar' => 'ناورو', 'iso3' => 'NRU', 'numcode' => '520', 'phonecode' => '674'),
            array('iso' => 'NP', 'name' => 'Nepal', 'name_ar' => 'نيبال', 'iso3' => 'NPL', 'numcode' => '524', 'phonecode' => '977'),
            array('iso' => 'NL', 'name' => 'Netherlands', 'name_ar' => 'هولندا', 'iso3' => 'NLD', 'numcode' => '528', 'phonecode' => '31'),
            array('iso' => 'AN', 'name' => 'Netherlands Antilles', 'name_ar' => 'جزر الأنتيل الهولندية', 'iso3' => 'ANT', 'numcode' => '530', 'phonecode' => '599'),
            array('iso' => 'NC', 'name' => 'New Caledonia', 'name_ar' => 'كاليدونيا الجديدة', 'iso3' => 'NCL', 'numcode' => '540', 'phonecode' => '687'),
            array('iso' => 'NZ', 'name' => 'New Zealand', 'name_ar' => 'نيوزيلندا', 'iso3' => 'NZL', 'numcode' => '554', 'phonecode' => '64'),
            array('iso' => 'NI', 'name' => 'Nicaragua', 'name_ar' => 'نيكاراغوا', 'iso3' => 'NIC', 'numcode' => '558', 'phonecode' => '505'),
            array('iso' => 'NE', 'name' => 'Niger', 'name_ar' => 'النيجر', 'iso3' => 'NER', 'numcode' => '562', 'phonecode' => '227'),
            array('iso' => 'NG', 'name' => 'Nigeria', 'name_ar' => 'نيجيريا', 'iso3' => 'NGA', 'numcode' => '566', 'phonecode' => '234'),
            array('iso' => 'NU', 'name' => 'Niue', 'name_ar' => 'نيوي', 'iso3' => 'NIU', 'numcode' => '570', 'phonecode' => '683'),
            array('iso' => 'NF', 'name' => 'Norfolk Island', 'name_ar' => 'جزيرة نورفولك', 'iso3' => 'NFK', 'numcode' => '574', 'phonecode' => '672'),
            array('iso' => 'MP', 'name' => 'Northern Mariana Islands', 'name_ar' => 'جزر ماريانا الشمالية', 'iso3' => 'MNP', 'numcode' => '580', 'phonecode' => '1670'),
            array('iso' => 'NO', 'name' => 'Norway', 'name_ar' => 'النرويج', 'iso3' => 'NOR', 'numcode' => '578', 'phonecode' => '47'),
            array('iso' => 'OM', 'name' => 'Oman', 'name_ar' => 'عُمان', 'iso3' => 'OMN', 'numcode' => '512', 'phonecode' => '968'),
            array('iso' => 'PK', 'name' => 'Pakistan', 'name_ar' => 'باكستان', 'iso3' => 'PAK', 'numcode' => '586', 'phonecode' => '92'),
            array('iso' => 'PW', 'name' => 'Palau', 'name_ar' => 'بالاو', 'iso3' => 'PLW', 'numcode' => '585', 'phonecode' => '680'),
            array('iso' => 'PS', 'name' => 'Palestinian Territory, Occupied', 'name_ar' => 'فلسطين', 'iso3' => NULL,'numcode' => NULL,'phonecode' => '970'),
            array('iso' => 'PA', 'name' => 'Panama', 'name_ar' => 'بنما', 'iso3' => 'PAN', 'numcode' => '591', 'phonecode' => '507'),
            array('iso' => 'PG', 'name' => 'Papua New Guinea', 'name_ar' => 'بابوا غينيا الجديدة', 'iso3' => 'PNG', 'numcode' => '598', 'phonecode' => '675'),
            array('iso' => 'PY', 'name' => 'Paraguay', 'name_ar' => 'باراغواي', 'iso3' => 'PRY', 'numcode' => '600', 'phonecode' => '595'),
            array('iso' => 'PE', 'name' => 'Peru', 'name_ar' => 'بيرو', 'iso3' => 'PER', 'numcode' => '604', 'phonecode' => '51'),
            array('iso' => 'PH', 'name' => 'Philippines', 'name_ar' => 'الفلبين', 'iso3' => 'PHL', 'numcode' => '608', 'phonecode' => '63'),
            array('iso' => 'PN', 'name' => 'Pitcairn', 'name_ar' => 'بيتكيرن', 'iso3' => 'PCN', 'numcode' => '612', 'phonecode' => '0'),
            array('iso' => 'PL', 'name' => 'Poland', 'name_ar' => 'بولندا', 'iso3' => 'POL', 'numcode' => '616', 'phonecode' => '48'),
            array('iso' => 'PT', 'name' => 'Portugal', 'name_ar' => 'البرتغال', 'iso3' => 'PRT', 'numcode' => '620', 'phonecode' => '351'),
            array('iso' => 'PR', 'name' => 'Puerto Rico', 'name_ar' => 'بورتو ريكو', 'iso3' => 'PRI', 'numcode' => '630', 'phonecode' => '1787'),
            array('iso' => 'QA', 'name' => 'Qatar', 'name_ar' => 'قطر', 'iso3' => 'QAT', 'numcode' => '634', 'phonecode' => '974'),
            array('iso' => 'RE', 'name' => 'Reunion', 'name_ar' => 'ريونيون', 'iso3' => 'REU', 'numcode' => '638', 'phonecode' => '262'),
            array('iso' => 'RO', 'name' => 'Romania', 'name_ar' => 'رومانيا', 'iso3' => 'ROM', 'numcode' => '642', 'phonecode' => '40'),
            array('iso' => 'RU', 'name' => 'Russian Federation', 'name_ar' => 'روسيا', 'iso3' => 'RUS', 'numcode' => '643', 'phonecode' => '70'),
            array('iso' => 'RW', 'name' => 'Rwanda', 'name_ar' => 'رواندا', 'iso3' => 'RWA', 'numcode' => '646', 'phonecode' => '250'),
            array('iso' => 'SH', 'name' => 'Saint Helena', 'name_ar' => 'سانت هيلينا', 'iso3' => 'SHN', 'numcode' => '654', 'phonecode' => '290'),
            array('iso' => 'KN', 'name' => 'Saint Kitts and Nevis', 'name_ar' => 'سانت كيتس ونيفيس', 'iso3' => 'KNA', 'numcode' => '659', 'phonecode' => '1869'),
            array('iso' => 'LC', 'name' => 'Saint Lucia', 'name_ar' => 'سانت لوسيا', 'iso3' => 'LCA', 'numcode' => '662', 'phonecode' => '1758'),
            array('iso' => 'PM', 'name' => 'Saint Pierre and Miquelon', 'name_ar' => 'سان بيير وميكلون', 'iso3' => 'SPM', 'numcode' => '666', 'phonecode' => '508'),
            array('iso' => 'VC', 'name' => 'Saint Vincent and the Grenadines', 'name_ar' => 'سانت فنسنت والغرينادين', 'iso3' => 'VCT', 'numcode' => '670', 'phonecode' => '1784'),
            array('iso' => 'WS', 'name' => 'Samoa', 'name_ar' => 'ساموا', 'iso3' => 'WSM', 'numcode' => '882', 'phonecode' => '684'),
            array('iso' => 'SM', 'name' => 'San Marino', 'name_ar' => 'سان مارينو', 'iso3' => 'SMR', 'numcode' => '674', 'phonecode' => '378'),
            array('iso' => 'ST', 'name' => 'Sao Tome and Principe', 'name_ar' => 'ساو تومي وبرينسيبي', 'iso3' => 'STP', 'numcode' => '678', 'phonecode' => '239'),
            array('iso' => 'SA', 'name' => 'Saudi Arabia', 'name_ar' => 'السعودية', 'iso3' => 'SAU', 'numcode' => '682', 'phonecode' => '966'),
            array('iso' => 'SN', 'name' => 'Senegal', 'name_ar' => 'السنغال', 'iso3' => 'SEN', 'numcode' => '686', 'phonecode' => '221'),
            array('iso' => 'CS', 'name' => 'Serbia and Montenegro', 'name_ar' => 'صربيا والجبل الأسود', 'iso3' => NULL,'numcode' => NULL,'phonecode' => '381'),
            array('iso' => 'SC', 'name' => 'Seychelles', 'name_ar' => 'سيشل', 'iso3' => 'SYC', 'numcode' => '690', 'phonecode' => '248'),
            array('iso' => 'SL', 'name' => 'Sierra Leone', 'name_ar' => 'سيراليون', 'iso3' => 'SLE', 'numcode' => '694', 'phonecode' => '232'),
            array('iso' => 'SG', 'name' => 'Singapore', 'name_ar' => 'سنغافورة', 'iso3' => 'SGP', 'numcode' => '702', 'phonecode' => '65'),
            array('iso' => 'SK', 'name' => 'Slovakia', 'name_ar' => 'سلوفاكيا', 'iso3' => 'SVK', 'numcode' => '703', 'phonecode' => '421'),
            array('iso' => 'SI', 'name' => 'Slovenia', 'name_ar' => 'سلوفينيا', 'iso3' => 'SVN', 'numcode' => '705', 'phonecode' => '386'),
            array('iso' => 'SB', 'name' => 'Solomon Islands', 'name_ar' => 'جزر سليمان', 'iso3' => 'SLB', 'numcode' => '90', 'phonecode' => '677'),
            array('iso' => 'SO', 'name' => 'Somalia', 'name_ar' => 'الصومال', 'iso3' => 'SOM', 'numcode' => '706', 'phonecode' => '252'),
            array('iso' => 'ZA', 'name' => 'South Africa', 'name_ar' => 'جنوب أفريقيا', 'iso3' => 'ZAF', 'numcode' => '710', 'phonecode' => '27'),
            array('iso' => 'GS', 'name' => 'South Georgia and the South Sandwich Islands', 'name_ar' => 'جورجيا الجنوبية وجزر ساندويتش الجنوبية', 'iso3' => NULL,'numcode' => NULL,'phonecode' => '0'),
            array('iso' => 'ES', 'name' => 'Spain', 'name_ar' => 'إسبانيا', 'iso3' => 'ESP', 'numcode' => '724', 'phonecode' => '34'),
            array('iso' => 'LK', 'name' => 'Sri Lanka', 'name_ar' => 'سريلانكا', 'iso3' => 'LKA', 'numcode' => '144', 'phonecode' => '94'),
            array('iso' => 'SD', 'name' => 'Sudan', 'name_ar' => 'السودان', 'iso3' => 'SDN', 'numcode' => '736', 'phonecode' => '249'),
            array('iso' => 'SR', 'name' => 'Suriname', 'name_ar' => 'سورينام', 'iso3' => 'SUR', 'numcode' => '740', 'phonecode' => '597'),
            array('iso' => 'SJ', 'name' => 'Svalbard and Jan Mayen', 'name_ar' => 'سفالبارد ويان ماين', 'iso3' => 'SJM', 'numcode' => '744', 'phonecode' => '47'),
            array('iso' => 'SZ', 'name' => 'Swaziland', 'name_ar' => 'إسواتيني', 'iso3' => 'SWZ', 'numcode' => '748', 'phonecode' => '268'),
            array('iso' => 'SE', 'name' => 'Sweden', 'name_ar' => 'السويد', 'iso3' => 'SWE', 'numcode' => '752', 'phonecode' => '46'),
            array('iso' => 'CH', 'name' => 'Switzerland', 'name_ar' => 'سويسرا', 'iso3' => 'CHE', 'numcode' => '756', 'phonecode' => '41'),
            array('iso' => 'SY', 'name' => 'Syrian Arab Republic', 'name_ar' => 'سوريا', 'iso3' => 'SYR', 'numcode' => '760', 'phonecode' => '963'),
            array('iso' => 'TW', 'name' => 'Taiwan, Province of China', 'name_ar' => 'تايوان', 'iso3' => 'TWN', 'numcode' => '158', 'phonecode' => '886'),
            array('iso' => 'TJ', 'name' => 'Tajikistan', 'name_ar' => 'طاجيكستان', 'iso3' => 'TJK', 'numcode' => '762', 'phonecode' => '992'),
            array('iso' => 'TZ', 'name' => 'Tanzania, United Republic of', 'name_ar' => 'تنزانيا', 'iso3' => 'TZA', 'numcode' => '834', 'phonecode' => '255'),
            array('iso' => 'TH', 'name' => 'Thailand', 'name_ar' => 'تايلاند', 'iso3' => 'THA', 'numcode' => '764', 'phonecode' => '66'),
            array('iso' => 'TL', 'name' => 'Timor-Leste', 'name_ar' => 'تيمور الشرقية', 'iso3' => NULL, 'numcode' => NULL, 'phonecode' => '670'),
            array('iso' => 'TG', 'name' => 'Togo', 'name_ar' => 'توغو', 'iso3' => 'TGO', 'numcode' => '768', 'phonecode' => '228'),
            array('iso' => 'TK', 'name' => 'Tokelau', 'name_ar' => 'توكيلاو', 'iso3' => 'TKL', 'numcode' => '772', 'phonecode' => '690'),
            array('iso' => 'TO', 'name' => 'Tonga', 'name_ar' => 'تونغا', 'iso3' => 'TON', 'numcode' => '776', 'phonecode' => '676'),
            array('iso' => 'TT', 'name' => 'Trinidad and Tobago', 'name_ar' => 'ترينيداد وتوباغو', 'iso3' => 'TTO', 'numcode' => '780', 'phonecode' => '1868'),
            array('iso' => 'TN', 'name' => 'Tunisia', 'name_ar' => 'تونس', 'iso3' => 'TUN', 'numcode' => '788', 'phonecode' => '216'),
            array('iso' => 'TR', 'name' => 'Turkey', 'name_ar' => 'تركيا', 'iso3' => 'TUR', 'numcode' => '792', 'phonecode' => '90'),
            array('iso' => 'TM', 'name' => 'Turkmenistan', 'name_ar' => 'تركمانستان', 'iso3' => 'TKM', 'numcode' => '795', 'phonecode' => '7370'),
            array('iso' => 'TC', 'name' => 'Turks and Caicos Islands', 'name_ar' => 'جزر توركس وكايكوس', 'iso3' => 'TCA', 'numcode' => '796', 'phonecode' => '1649'),
            array('iso' => 'TV', 'name' => 'Tuvalu', 'name_ar' => 'توفالو', 'iso3' => 'TUV', 'numcode' => '798', 'phonecode' => '688'),
            array('iso' => 'UG', 'name' => 'Uganda', 'name_ar' => 'أوغندا', 'iso3' => 'UGA', 'numcode' => '800', 'phonecode' => '256'),
            array('iso' => 'UA', 'name' => 'Ukraine', 'name_ar' => 'أوكرانيا', 'iso3' => 'UKR', 'numcode' => '804', 'phonecode' => '380'),
            array('iso' => 'AE', 'name' => 'United Arab Emirates', 'name_ar' => 'الإمارات العربية المتحدة', 'iso3' => 'ARE', 'numcode' => '784', 'phonecode' => '971'),
            array('iso' => 'GB', 'name' => 'United Kingdom', 'name_ar' => 'المملكة المتحدة', 'iso3' => 'GBR', 'numcode' => '826', 'phonecode' => '44'),
            array('iso' => 'US', 'name' => 'United States', 'name_ar' => 'الولايات المتحدة', 'iso3' => 'USA', 'numcode' => '840', 'phonecode' => '1'),
            array('iso' => 'UM', 'name' => 'United States Minor Outlying Islands', 'name_ar' => 'الجزر الصغيرة النائية التابعة للولايات المتحدة', 'iso3' => NULL, 'numcode' => NULL, 'phonecode' => '1'),
            array('iso' => 'UY', 'name' => 'Uruguay', 'name_ar' => 'أوروغواي', 'iso3' => 'URY', 'numcode' => '858', 'phonecode' => '598'),
            array('iso' => 'UZ', 'name' => 'Uzbekistan', 'name_ar' => 'أوزبكستان', 'iso3' => 'UZB', 'numcode' => '860', 'phonecode' => '998'),
            array('iso' => 'VU', 'name' => 'Vanuatu', 'name_ar' => 'فانواتو', 'iso3' => 'VUT', 'numcode' => '548', 'phonecode' => '678'),
            array('iso' => 'VE', 'name' => 'Venezuela', 'name_ar' => 'فنزويلا', 'iso3' => 'VEN', 'numcode' => '862', 'phonecode' => '58'),
            array('iso' => 'VN', 'name' => 'Viet Nam', 'name_ar' => 'فيتنام', 'iso3' => 'VNM', 'numcode' => '704', 'phonecode' => '84'),
            array('iso' => 'VG', 'name' => 'Virgin Islands, British', 'name_ar' => 'جزر فيرجن البريطانية', 'iso3' => 'VGB', 'numcode' => '92', 'phonecode' => '1284'),
            array('iso' => 'VI', 'name' => 'Virgin Islands, U.S.', 'name_ar' => 'جزر فيرجن الأمريكية', 'iso3' => 'VIR', 'numcode' => '850', 'phonecode' => '1340'),
            array('iso' => 'WF', 'name' => 'Wallis and Futuna', 'name_ar' => 'واليس وفوتونا', 'iso3' => 'WLF', 'numcode' => '876', 'phonecode' => '681'),
            array('iso' => 'EH', 'name' => 'Western Sahara', 'name_ar' => 'الصحراء الغربية', 'iso3' => 'ESH', 'numcode' => '732', 'phonecode' => '212'),
            array('iso' => 'YE', 'name' => 'Yemen', 'name_ar' => 'اليمن', 'iso3' => 'YEM', 'numcode' => '887', 'phonecode' => '967'),
            array('iso' => 'ZM', 'name' => 'Zambia', 'name_ar' => 'زامبيا', 'iso3' => 'ZMB', 'numcode' => '894', 'phonecode' => '260'),
            array('iso' => 'ZW', 'name' => 'Zimbabwe', 'name_ar' => 'زيمبابوي', 'iso3' => 'ZWE', 'numcode' => '716', 'phonecode' => '263'),
            array('iso' => 'RS', 'name' => 'Serbia', 'name_ar' => 'صربيا', 'iso3' => 'SRB', 'numcode' => '688', 'phonecode' => '381'),
            array('iso' => 'AP', 'name' => 'Asia / Pacific Region', 'name_ar' => 'منطقة آسيا والمحيط الهادئ', 'iso3' => '0', 'numcode' => '0', 'phonecode' => '0'),
            array('iso' => 'ME', 'name' => 'Montenegro', 'name_ar' => 'الجبل الأسود', 'iso3' => 'MNE', 'numcode' => '499', 'phonecode' => '382'),
            array('iso' => 'AX', 'name' => 'Aland Islands', 'name_ar' => 'جزر آلاند', 'iso3' => 'ALA', 'numcode' => '248', 'phonecode' => '358'),
            array('iso' => 'BQ', 'name' => 'Bonaire, Sint Eustatius and Saba', 'name_ar' => 'بونير وسينت أوستاتيوس وسابا', 'iso3' => 'BES', 'numcode' => '535', 'phonecode' => '599'),
            array('iso' => 'CW', 'name' => 'Curacao', 'name_ar' => 'كوراساو', 'iso3' => 'CUW', 'numcode' => '531', 'phonecode' => '599'),
            array('iso' => 'GG', 'name' => 'Guernsey', 'name_ar' => 'غيرنزي', 'iso3' => 'GGY', 'numcode' => '831', 'phonecode' => '44'),
            array('iso' => 'IM', 'name' => 'Isle of Man', 'name_ar' => 'جزيرة مان', 'iso3' => 'IMN', 'numcode' => '833', 'phonecode' => '44'),
            array('iso' => 'JE', 'name' => 'Jersey', 'name_ar' => 'جيرسي', 'iso3' => 'JEY', 'numcode' => '832', 'phonecode' => '44'),
            array('iso' => 'XK', 'name' => 'Kosovo', 'name_ar' => 'كوسوفو', 'iso3' => '---', 'numcode' => '0', 'phonecode' => '381'),
            array('iso' => 'BL', 'name' => 'Saint Barthelemy', 'name_ar' => 'سان بارتيلمي', 'iso3' => 'BLM', 'numcode' => '652', 'phonecode' => '590'),
            array('iso' => 'MF', 'name' => 'Saint Martin', 'name_ar' => 'سانت مارتن', 'iso3' => 'MAF', 'numcode' => '663', 'phonecode' => '590'),
            array('iso' => 'SX', 'name' => 'Sint Maarten', 'name_ar' => 'سينت مارتن', 'iso3' => 'SXM', 'numcode' => '534', 'phonecode' => '1'),
            array('iso' => 'SS', 'name' => 'South Sudan', 'name_ar' => 'جنوب السودان', 'iso3' => 'SSD', 'numcode' => '728', 'phonecode' => '211'),
        );

        foreach ($countries as $data) {
            $iso = trim($data['iso']);
            $iso3 = trim($data['iso3'] ?? '');
            $prettyName = trim($data['name']);
            $prettyArName = trim($data['name_ar']);
            $phone = '+' . trim($data['phonecode']);

            // البحث بمرونة عن تطابقات محتملة
            $country = Country::where('iso', $iso)
                ->orWhere('iso3', $iso3)
                ->orWhere('e_name', 'LIKE', "%{$prettyName}%")
                ->orWhere('name', 'LIKE', "%{$prettyName}%")
                ->orWhere('phone_code', $phone)
                ->first();

            if ($country) {
                // تحديث السجل القديم مع الحفاظ على البيانات القديمة إن وُجدت
                $country->update([
                    'e_name'     => $prettyName,
                    'name'       => $prettyArName,
                    'iso'        => $iso ?: $country->iso,
                    'iso3'       => $iso3 ?: $country->iso3,
                    'phone_code' => $phone ?: $country->phone_code,
                    'status'     => 1,
                ]);

                $this->command->info("🔄 Updated existing country: {$prettyName} (ID {$country->id})");
            } else {
                // لو الدولة مش موجودة نهائيًا نضيفها كجديدة
                $new = Country::create([
                    'e_name'     => $prettyName,
                    'name'       => $prettyName,
                    'iso'        => $iso,
                    'iso3'       => $iso3,
                    'phone_code' => $phone,
                    'status'     => 1,
                ]);

                $this->command->info("➕ Created new country: {$prettyName} (ID {$new->id})");
            }
        }

        $this->command->info('✅ Countries updated or inserted successfully!');
    }
}

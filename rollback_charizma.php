<?php
use Illuminate\Support\Facades\Schema;

Schema::table('rooms', function ($table) {
    if (Schema::hasColumn('rooms', 'charizma_status')) {
        $table->dropColumn('charizma_status');
    }
    if (Schema::hasColumn('rooms', 'charizma_timestamp')) {
        $table->dropColumn('charizma_timestamp');
    }
});
echo "Removed charizma columns from rooms table\n";

Schema::dropIfExists('extra_data_in_rooms');
echo "Dropped extra_data_in_rooms table\n";

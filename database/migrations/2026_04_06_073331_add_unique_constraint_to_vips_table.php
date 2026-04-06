<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The vips table has groups of consecutive levels sharing the same exp value
     * (e.g., levels 21-29 all have exp=83,100,000). This causes users to get
     * stuck at the first level in each group because the level-up query
     * (orderByDesc('exp')) can't distinguish between them.
     *
     * Fix: Interpolate distinct exp values within each group, evenly spaced
     * between the group's exp and the next tier's exp, then add a unique
     * constraint to prevent future duplicates.
     */
    public function up(): void
    {
        // Process each type independently
        foreach ([1, 2] as $type) {
            $levels = DB::table('vips')
                ->where('type', $type)
                ->orderBy('level')
                ->get(['id', 'level', 'exp']);

            if ($levels->isEmpty()) {
                continue;
            }

            // Group consecutive levels that share the same exp
            $groups = [];
            $currentGroup = [$levels[0]];

            for ($i = 1; $i < $levels->count(); $i++) {
                if ($levels[$i]->exp == $currentGroup[0]->exp) {
                    $currentGroup[] = $levels[$i];
                } else {
                    if (count($currentGroup) > 1) {
                        $groups[] = [
                            'items' => $currentGroup,
                            'next_exp' => $levels[$i]->exp,
                        ];
                    }
                    $currentGroup = [$levels[$i]];
                }
            }

            // Handle the last group (no next tier — use 2x the group exp)
            if (count($currentGroup) > 1) {
                $groups[] = [
                    'items' => $currentGroup,
                    'next_exp' => $currentGroup[0]->exp * 2,
                ];
            }

            // Interpolate distinct exp values within each group
            foreach ($groups as $group) {
                $items = $group['items'];
                $baseExp = $items[0]->exp;
                $nextExp = $group['next_exp'];
                $count = count($items);
                $step = intval(($nextExp - $baseExp) / ($count + 1));

                // Skip the first item in each group (keep its original exp)
                for ($i = 1; $i < $count; $i++) {
                    $newExp = $baseExp + ($step * $i);
                    DB::table('vips')
                        ->where('id', $items[$i]->id)
                        ->update(['exp' => $newExp]);
                }
            }
        }

        // Now all (type, exp) pairs are unique — safe to add the constraint
        Schema::table('vips', function (Blueprint $table) {
            $table->unique(['type', 'exp'], 'vips_type_exp_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vips', function (Blueprint $table) {
            $table->dropUnique('vips_type_exp_unique');
        });
    }
};

<?php

namespace App\Models;

/**
 * Class Alias for backward compatibility
 * This model now extends from the Gifts package
 * 
 * @deprecated Use Utd\Gifts\Entities\GiftCategory instead
 */
class_alias(\Utd\Gifts\Entities\GiftCategory::class, GiftCategory::class);

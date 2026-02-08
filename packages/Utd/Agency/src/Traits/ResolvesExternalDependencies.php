<?php

namespace Utd\Agency\Traits;

/**
 * Comprehensive trait that includes all external dependency resolution
 * Use this trait in controllers to safely access external models, services, helpers, and modules
 */
trait ResolvesExternalDependencies
{
    use ResolvesModels;
    use ResolvesServices;
    use ResolvesHelpers;
    use ResolvesModules;
}

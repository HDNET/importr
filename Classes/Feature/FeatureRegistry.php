<?php

declare(strict_types=1);
/**
 * FeatureRegistry.php
 */
namespace HDNET\Importr\Feature;

use HDNET\Importr\Service\Manager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FeatureRegistry
 */
class FeatureRegistry
{
    /**
     * @param string|array $names
     * @param string       $class
     */
    public static function enable($names, $class = Manager::class)
    {
        if (!\is_array($names)) {
            $names = [$names];
        }

        $trace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $caller = $trace[1]['class'];

        foreach ($names as $name) {
            // @todo migrate to events
            // $dispatcher->connect($class, $name, $caller, 'execute');
        }
    }
}

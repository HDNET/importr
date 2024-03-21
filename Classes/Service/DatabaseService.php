<?php

declare(strict_types=1);
/**
 * DatabaseService.php
 */
namespace HDNET\Importr\Service;

use HDNET\Importr\Migration\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * DatabaseService
 */
class DatabaseService
{
    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection|\HDNET\Importr\Migration\DatabaseConnectionMigrationInterface
     */
    public function getDatabaseConnection()
    {
        return GeneralUtility::makeInstance(DatabaseConnection::class);
    }
}

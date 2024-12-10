<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters;

/**
 * File names where upgrade temporary content is stored.
 */
class UpgradeFileNames
{
    /**
     * stateFilename contains all state specific to the autoupgrade module.
     *
     * @var string
     */
    const STATE_FILENAME = 'state.var';

    /**
     * configFilename contains all configuration specific to the autoupgrade module.
     *
     * @var string
     */
    const CONFIG_FILENAME = 'config.var';

    /**
     * during upgradeFiles process,
     * this files contains the list of queries left to upgrade in a serialized array.
     * (this file is deleted in init() method if you reload the page).
     *
     * @var string
     */
    const QUERIES_TO_UPGRADE_LIST = 'queriesToUpgrade.list';

    /**
     * during upgradeFiles process,
     * this files contains the list of files left to upgrade in a serialized array.
     * (this file is deleted in init() method if you reload the page).
     *
     * @var string
     */
    const FILES_TO_UPGRADE_LIST = 'filesToUpgrade.list';

    /**
     * during updateDatabase process,
     * this files contains the list of queries left to execute in a serialized array.
     * (this file is deleted in init() method if you reload the page).
     *
     * @var string
     */
    const SQL_TO_EXECUTE_LIST = 'sqlToExecute.list';

    /**
     * during upgradeModules process,
     * this files contains the list of modules left to upgrade in a serialized array.
     * (this file is deleted in init() method if you reload the page).
     *
     * @var string
     */
    const MODULES_TO_UPGRADE_LIST = 'modulesToUpgrade.list';

    /**
     * during backupFiles process,
     * this files contains the list of files left to save in a serialized array.
     * (this file is deleted in init() method if you reload the page).
     *
     * @var string
     */
    const FILES_TO_BACKUP_LIST = 'filesToBackup.list';

    /**
     * during backupDb process,
     * this files contains the list of tables left to save in a serialized array.
     * (this file is deleted in init() method if you reload the page).
     *
     * @var string
     */
    const DB_TABLES_TO_BACKUP_LIST = 'tablesToBackup.list';

    /**
     * during restoreDb process,
     * this file contains a serialized array of queries which left to execute for restoring database
     * (this file is deleted in init() method if you reload the page).
     *
     * @var string
     */
    const QUERIES_TO_RESTORE_LIST = 'queryToRestore.list';
    const DB_TABLES_TO_CLEAN_LIST = 'tableToClean.list';

    /**
     * during restoreFiles process,
     * this file contains difference between queryToRestore and queries present in a backupFiles archive
     * (this file is deleted in init() method if you reload the page).
     *
     * @var string
     */
    const FILES_TO_REMOVE_LIST = 'filesToRemove.list';

    /**
     * during restoreFiles process,
     * contains list of files present in backupFiles archive.
     *
     * @var string
     */
    const FILES_FROM_ARCHIVE_LIST = 'filesFromArchive.list';

    /**
     * Module Sources Providers are classes that fetching & compute data
     * from the filesystem or an external resource like an API.
     * Caching the data avoids this computation to happen again before each module.
     *
     * @var string
     */
    const MODULE_SOURCE_PROVIDER_CACHE_LOCAL = 'moduleSourcesLocal.cache';
    const MODULE_SOURCE_PROVIDER_CACHE_COMPOSER = 'moduleSourcesComposer.cache';
    const MODULE_SOURCE_PROVIDER_CACHE_MARKETPLACE_API = 'moduleSourcesMarketplace.cache';
    const MODULE_SOURCE_PROVIDER_CACHE_DISTRIBUTION_API = 'moduleSourcesDistribution.cache';

    /**
     * update_tmp_files contains an array of filename which will be removed
     * at the beginning of the upgrade process.
     *
     * @var array<string, string>
     */
    public static $update_tmp_files = [
        'STATE_FILENAME' => self::STATE_FILENAME,
        'QUERIES_TO_UPGRADE_LIST' => self::QUERIES_TO_UPGRADE_LIST, // used ?
        'FILES_TO_UPGRADE_LIST' => self::FILES_TO_UPGRADE_LIST,
        'DB_TABLES_TO_CLEAN_LIST' => self::DB_TABLES_TO_CLEAN_LIST,
        'FILES_TO_REMOVE_LIST' => self::FILES_TO_REMOVE_LIST,
        'MODULES_TO_UPGRADE_LIST' => self::MODULES_TO_UPGRADE_LIST,
        'MODULE_SOURCE_PROVIDER_CACHE_LOCAL' => self::MODULE_SOURCE_PROVIDER_CACHE_LOCAL,
        'MODULE_SOURCE_PROVIDER_CACHE_COMPOSER' => self::MODULE_SOURCE_PROVIDER_CACHE_COMPOSER,
        'MODULE_SOURCE_PROVIDER_CACHE_MARKETPLACE_API' => self::MODULE_SOURCE_PROVIDER_CACHE_MARKETPLACE_API,
        'MODULE_SOURCE_PROVIDER_CACHE_DISTRIBUTION_API' => self::MODULE_SOURCE_PROVIDER_CACHE_DISTRIBUTION_API,
        'SQL_TO_EXECUTE_LIST' => self::SQL_TO_EXECUTE_LIST,
    ];

    /**
     * @var array<string, string>
     */
    public static $backup_tmp_files = [
        // TODO: Needs the split of state files to avoid dropping data useful for the update on the web UI
        // 'STATE_FILENAME' => self::STATE_FILENAME,
        'FILES_TO_BACKUP_LIST' => self::FILES_TO_BACKUP_LIST,
        'DB_TABLES_TO_BACKUP_LIST' => self::DB_TABLES_TO_BACKUP_LIST,
    ];

    /**
     * @var array<string, string>
     */
    public static $restore_tmp_files = [
        'STATE_FILENAME' => self::STATE_FILENAME,
        'QUERIES_TO_RESTORE_LIST' => self::QUERIES_TO_RESTORE_LIST,
        'FILES_FROM_ARCHIVE_LIST' => self::FILES_FROM_ARCHIVE_LIST,
    ];
}

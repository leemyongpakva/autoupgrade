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

namespace PrestaShop\Module\AutoUpgrade;

use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;

/**
 * Class storing the temporary data to keep between 2 ajax requests.
 */
class State
{
    /**
     * @var string
     */
    private $currentVersion; // Origin version of PrestaShop
    /**
     * @var ?string
     */
    private $destinationVersion; // Destination version of PrestaShop
    /**
     * @var string
     */
    private $backupName;
    /**
     * @var string
     */
    private $backupFilesFilename;
    /**
     * @var string
     */
    private $backupDbFilename;
    /**
     * @var string
     */
    private $restoreName;
    /**
     * @var string
     */
    private $restoreFilesFilename;
    /**
     * @var string[]
     */
    private $restoreDbFilenames = [];

    // STEP BackupDb
    /**
     * @var string[]
     */
    private $backup_lines;
    /**
     * @var int
     */
    private $backup_loop_limit;
    /**
     * @var string the table being synchronized, in case mutiple requests are needed to sync the whole table
     */
    private $backup_table;

    /**
     * Int during BackupDb, allowing the script to increent the number of different file names
     * String during step RestoreDb, which contains the file to process (Data coming from toRestoreQueryList).
     *
     * @var int Contains the SQL progress
     */
    private $dbStep = 0;

    /**
     * installedLanguagesIso is an array of iso_code of each installed languages.
     *
     * @var string[]
     */
    private $installedLanguagesIso = [];

    /**
     * @var bool Determining if all steps went totally successfully
     */
    private $warning_exists = false;

    /** @var int */
    private $progressPercentage;

    /** @var ?string */
    private $processTimestamp;

    /** @var FileConfigurationStorage */
    private $fileConfigurationStorage;
    /** @var bool */
    private $disableSave = false;

    public function __construct(FileConfigurationStorage $fileConfigurationStorage)
    {
        $this->fileConfigurationStorage = $fileConfigurationStorage;
    }

    /**
     * @return void
     */
    public function load()
    {
        $state = $this->fileConfigurationStorage->load(UpgradeFileNames::STATE_FILENAME);

        $this->importFromArray($state);
    }

    /**
     * @param array<string, mixed> $savedState from another request
     */
    public function importFromArray(array $savedState): State
    {
        foreach ($savedState as $name => $value) {
            if (!empty($value) && property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }

        return $this;
    }

    public function importFromEncodedData(string $encodedData): State
    {
        $decodedData = json_decode(base64_decode($encodedData), true);
        if (empty($decodedData['nextParams'])) {
            return $this;
        }

        return $this->importFromArray($decodedData['nextParams']);
    }

    public function save(): bool
    {
        if (!$this->disableSave) {
            return $this->fileConfigurationStorage->save($this->export(), UpgradeFileNames::STATE_FILENAME);
        }

        return true;
    }

    /**
     * @return array<string, mixed> of class properties for export
     */
    public function export(): array
    {
        $state = get_object_vars($this);
        unset($state['fileConfigurationStorage']);

        return $state;
    }

    public function initDefault(string $currentVersion, ?string $destinationVersion): void
    {
        $this->disableSave = true;
        // installedLanguagesIso is used to merge translations files
        $installedLanguagesIso = array_map(
            function ($v) { return $v['iso_code']; },
            \Language::getIsoIds(false)
        );
        $this->setInstalledLanguagesIso($installedLanguagesIso);

        $rand = dechex(mt_rand(0, min(0xffffffff, mt_getrandmax())));
        $date = date('Ymd-His');
        $backupName = 'V' . $currentVersion . '_' . $date . '-' . $rand;
        // Todo: To be moved in state class? We could only require the backup name here
        // I.e = $this->upgradeContainer->getState()->setBackupName($backupName);, which triggers 2 other setters internally
        $this->setBackupName($backupName);
        $this->setCurrentVersion($currentVersion);
        $this->setDestinationVersion($destinationVersion);
        $this->disableSave = false;
        $this->save();
    }

    // GETTERS
    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    public function getDestinationVersion(): ?string
    {
        return $this->destinationVersion;
    }

    public function getBackupName(): string
    {
        return $this->backupName;
    }

    public function getBackupFilesFilename(): string
    {
        return $this->backupFilesFilename;
    }

    public function getBackupDbFilename(): string
    {
        return $this->backupDbFilename;
    }

    /**
     * @return string[]|null
     */
    public function getBackupLines(): ?array
    {
        return $this->backup_lines;
    }

    public function getBackupLoopLimit(): ?int
    {
        return $this->backup_loop_limit;
    }

    public function getBackupTable(): ?string
    {
        return $this->backup_table;
    }

    public function getDbStep(): int
    {
        return $this->dbStep;
    }

    public function getRestoreName(): string
    {
        return $this->restoreName;
    }

    public function getRestoreFilesFilename(): ?string
    {
        return $this->restoreFilesFilename;
    }

    /**
     * @return string[]
     */
    public function getRestoreDbFilenames(): array
    {
        return $this->restoreDbFilenames;
    }

    /** @return string[] */
    public function getInstalledLanguagesIso(): array
    {
        return $this->installedLanguagesIso;
    }

    public function getWarningExists(): bool
    {
        return $this->warning_exists;
    }

    public function getProgressPercentage(): ?int
    {
        return $this->progressPercentage;
    }

    public function getProcessTimestamp(): ?string
    {
        return $this->processTimestamp;
    }

    /**
     * Pick version from restoration file name in the format v[version]_[date]-[time]-[random]
     */
    public function getRestoreVersion(): ?string
    {
        $matches = [];
        preg_match(
            '/^V(?<version>[1-9\.]+)_/',
            $this->getRestoreName(),
            $matches
        );

        return $matches[1] ?? null;
    }

    // SETTERS
    public function setCurrentVersion(string $currentVersion): State
    {
        $this->currentVersion = $currentVersion;
        $this->save();

        return $this;
    }

    public function setDestinationVersion(?string $destinationVersion): State
    {
        $this->destinationVersion = $destinationVersion;
        $this->save();

        return $this;
    }

    public function setBackupName(string $backupName): State
    {
        $this->backupName = $backupName;
        $this->backupFilesFilename = BackupFinder::BACKUP_ZIP_NAME_PREFIX . $backupName . '.zip';
        $this->backupDbFilename = BackupFinder::BACKUP_DB_FOLDER_NAME_PREFIX . 'XXXXXX_' . $backupName . '.sql';

        $this->save();

        return $this;
    }

    /**
     * @param string[]|null $backup_lines
     */
    public function setBackupLines(?array $backup_lines): State
    {
        $this->backup_lines = $backup_lines;
        $this->save();

        return $this;
    }

    public function setBackupLoopLimit(?int $backup_loop_limit): State
    {
        $this->backup_loop_limit = $backup_loop_limit;
        $this->save();

        return $this;
    }

    public function setBackupTable(?string $backup_table): State
    {
        $this->backup_table = $backup_table;
        $this->save();

        return $this;
    }

    public function setDbStep(int $dbStep): State
    {
        $this->dbStep = $dbStep;
        $this->save();

        return $this;
    }

    public function setRestoreName(string $restoreName): State
    {
        $this->restoreName = $restoreName;
        $this->save();

        return $this;
    }

    public function setRestoreFilesFilename(string $restoreFilesFilename): State
    {
        $this->restoreFilesFilename = $restoreFilesFilename;
        $this->save();

        return $this;
    }

    /**
     * @param string[] $restoreDbFilenames
     */
    public function setRestoreDbFilenames(array $restoreDbFilenames): State
    {
        $this->restoreDbFilenames = $restoreDbFilenames;
        $this->save();

        return $this;
    }

    /**
     * @param string[] $installedLanguagesIso
     */
    public function setInstalledLanguagesIso(array $installedLanguagesIso): State
    {
        $this->installedLanguagesIso = $installedLanguagesIso;
        $this->save();

        return $this;
    }

    public function setWarningExists(bool $warning_exists): State
    {
        $this->warning_exists = $warning_exists;
        $this->save();

        return $this;
    }

    public function setProgressPercentage(int $progressPercentage): State
    {
        // Allow reset of percentage but not a decrease
        if ($progressPercentage && $progressPercentage < $this->progressPercentage) {
            throw new InvalidArgumentException('Updated progress percentage cannot be lower than the currently set one.');
        }

        $this->progressPercentage = $progressPercentage;
        $this->save();

        return $this;
    }

    public function setProcessTimestamp(string $processTimestamp): void
    {
        $this->processTimestamp = $processTimestamp;
        $this->save();
    }

    public function isInitialized(): bool
    {
        return $this->fileConfigurationStorage->exists(UpgradeFileNames::STATE_FILENAME);
    }
}

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

namespace PrestaShop\Module\AutoUpgrade\State;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;

class RestoreState extends AbstractState
{
    use ProgressTrait;

    /**
     * @var string
     */
    protected $restoreName;
    /**
     * @var string
     */
    protected $restoreFilesFilename;
    /**
     * @var string[]
     */
    protected $restoreDbFilenames = [];
    
    /**
     * Int during BackupDb, allowing the script to increent the number of different file names
     * String during step RestoreDb, which contains the file to process (Data coming from toRestoreQueryList).
     *
     * @var int Contains the SQL progress
     */
    protected $dbStep = 0;

    protected function getFileNameForPersistentStorage(): string
    {
        return UpgradeFileNames::STATE_RESTORE_FILENAME;
    }

    public function getDbStep(): int
    {
        return $this->dbStep;
    }

    public function setDbStep(int $dbStep): self
    {
        $this->dbStep = $dbStep;
        $this->save();

        return $this;
    }

    public function getRestoreName(): string
    {
        return $this->restoreName;
    }

    public function setRestoreName(string $restoreName): self
    {
        $this->restoreName = $restoreName;
        $this->save();

        return $this;
    }

    public function getRestoreFilesFilename(): ?string
    {
        return $this->restoreFilesFilename;
    }

    public function setRestoreFilesFilename(string $restoreFilesFilename): self
    {
        $this->restoreFilesFilename = $restoreFilesFilename;
        $this->save();

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRestoreDbFilenames(): array
    {
        return $this->restoreDbFilenames;
    }

    /**
     * @param string[] $restoreDbFilenames
     */
    public function setRestoreDbFilenames(array $restoreDbFilenames): self
    {
        $this->restoreDbFilenames = $restoreDbFilenames;
        $this->save();

        return $this;
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
}
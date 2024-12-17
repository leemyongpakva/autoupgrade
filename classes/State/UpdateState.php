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

class UpdateState extends AbstractState
{
    use ProgressTrait;

    /**
     * Origin version of PrestaShop
     * @var string
     */
    protected $currentVersion;
    /**
     * Destination version of PrestaShop
     * @var ?string
     */
    protected $destinationVersion;

    /**
     * installedLanguagesIso is an array of iso_code of each installed languages.
     *
     * @var string[]
     */
    protected $installedLanguagesIso = [];

    /**
     * @var bool Marks the backup done during the update configuration
     */
    protected $backupCompleted = false;

    /**
     * @var bool Determining if all steps went totally successfully
     * @deprecated
     */
    protected $warning_exists = false;

    protected function getFileNameForPersistentStorage(): string
    {
        return UpgradeFileNames::STATE_UPDATE_FILENAME;
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

        $this->setCurrentVersion($currentVersion);
        $this->setDestinationVersion($destinationVersion);
        $this->disableSave = false;
        $this->save();
    }

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    public function setCurrentVersion(string $currentVersion): self
    {
        $this->currentVersion = $currentVersion;
        $this->save();

        return $this;
    }

    public function getDestinationVersion(): ?string
    {
        return $this->destinationVersion;
    }

    public function setDestinationVersion(?string $destinationVersion): self
    {
        $this->destinationVersion = $destinationVersion;
        $this->save();

        return $this;
    }

    /** @return string[] */
    public function getInstalledLanguagesIso(): array
    {
        return $this->installedLanguagesIso;
    }

    /**
     * @param string[] $installedLanguagesIso
     */
    public function setInstalledLanguagesIso(array $installedLanguagesIso): self
    {
        $this->installedLanguagesIso = $installedLanguagesIso;
        $this->save();

        return $this;
    }

    public function isBackupCompleted(): bool
    {
        return $this->backupCompleted;
    }

    public function setBackupCompleted(bool $completed): self
    {
        $this->backupCompleted = $completed;
        $this->save();

        return $this;
    }

    /**
     * @deprecated Unused on the UIs from v7
     */
    public function getWarningExists(): bool
    {
        return $this->warning_exists;
    }

    /**
     * @deprecated Unused on the UIs from v7
     */
    public function setWarningExists(bool $warning_exists): self
    {
        $this->warning_exists = $warning_exists;
        $this->save();

        return $this;
    }    
}
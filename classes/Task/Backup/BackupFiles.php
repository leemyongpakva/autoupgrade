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

namespace PrestaShop\Module\AutoUpgrade\Task\Backup;

use Exception;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class BackupFiles extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_BACKUP;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $state = $this->container->getBackupState();
        $this->stepDone = false;
        $backupFilesFilename = $state->getBackupFilesFilename();
        if (empty($backupFilesFilename)) {
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();
            $this->logger->info($this->translator->trans('Error during backupFiles'));
            $this->logger->error($this->translator->trans('[ERROR] backupFiles filename has not been set'));

            return ExitCode::FAIL;
        }

        if (!$this->container->getFileStorage()->exists(UpgradeFileNames::FILES_TO_BACKUP_LIST)) {
            $state->setProgressPercentage(
                $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
            );

            /** @todo : only add files and dir listed in "originalPrestashopVersion" list */
            $filesToBackup = $this->container->getFilesystemAdapter()->listFilesInDir($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), 'backup', false);
            $nbFilesToBackup = count($filesToBackup);

            $backlog = new Backlog($filesToBackup, $nbFilesToBackup);
            if ($nbFilesToBackup) {
                $this->logger->debug($this->translator->trans('%s Files to backup.', [$nbFilesToBackup]));
            }

            // delete old backup, create new
            if (file_exists($this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $backupFilesFilename)) {
                unlink($this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $backupFilesFilename);
            }

            $this->logger->debug($this->translator->trans('Backup files initialized in %s', [$backupFilesFilename]));
        } else {
            $backlog = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::FILES_TO_BACKUP_LIST));
        }

        $this->next = TaskName::TASK_BACKUP_FILES;
        $remainingFiles = $backlog->getRemainingTotal();
        if ($remainingFiles) {
            $this->logger->info($this->translator->trans('Backup files in progress. %d files left', [$remainingFiles]));
            $this->stepDone = false;
            $res = $this->container->getZipAction()->compress($backlog, $this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $backupFilesFilename);
            if (!$res) {
                $this->next = TaskName::TASK_ERROR;
                $this->logger->info($this->translator->trans('Unable to open archive'));

                return ExitCode::FAIL;
            }
            $this->container->getFileStorage()->save($backlog->dump(), UpgradeFileNames::FILES_TO_BACKUP_LIST);
            $state->setProgressPercentage(
                $this->container->getCompletionCalculator()->computePercentage($backlog, self::class, BackupDatabase::class)
            );
        } else {
            $this->stepDone = true;
            $this->status = 'ok';
            $this->next = TaskName::TASK_BACKUP_DATABASE;
            $this->logger->debug($this->translator->trans('All files have been added to archive.'));
            $this->logger->info($this->translator->trans('All files saved. Now backing up database'));
        }

        return ExitCode::SUCCESS;
    }
}

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

namespace PrestaShop\Module\AutoUpgrade\Commands;

use DateTime;
use Exception;
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Backup\BackupManager;
use PrestaShop\Module\AutoUpgrade\Exceptions\BackupException;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

abstract class AbstractBackupCommand extends AbstractCommand
{
    /** @var BackupFinder */
    protected $backupFinder;

    /** @var BackupManager */
    protected $backupManager;

    protected function setupEnvironment(InputInterface $input, OutputInterface $output): void
    {
        parent::setupEnvironment($input, $output);
        $this->backupFinder = new BackupFinder($this->upgradeContainer->getProperty(UpgradeContainer::BACKUP_PATH));
        $this->backupManager = new BackupManager($this->backupFinder);
    }

    /**
     * @throws Exception
     */
    protected function selectBackupInteractive(InputInterface $input, OutputInterface $output): ?string
    {
        $backups = $this->backupFinder->getSortedAndFormatedAvailableBackups();

        if (empty($backups)) {
            $this->logger->info('No store backup files found in your dedicated directory');

            return null;
        }

        $rows = array_map(function ($backup) {
            return $this->formatBackupRow($backup);
        }, $backups);

        $exit = 'Exit the process';
        $rows[] = $exit;

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select your backup:',
            $rows
        );

        $answer = $helper->ask($input, $output, $question);

        if ($answer === $exit) {
            return null;
        }

        $key = array_search($answer, $rows);
        if ($key === false) {
            throw new BackupException('Invalid backup selection.');
        }

        return $formattedBackups[$key]['filename'];
    }

    /**
     * Formats a backup row for display in the selection prompt.
     *
     * @param array{datetime: string, version:string, filename: string} $backups
     *
     * @return string
     */
    private function formatBackupRow(array $backups): string
    {
        return sprintf('Date: %s, Version: %s, File name: %s', $backups['datetime'], $backups['version'], $backups['filename']);
    }
}

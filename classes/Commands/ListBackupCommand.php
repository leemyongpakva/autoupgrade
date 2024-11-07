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
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListBackupCommand extends AbstractCommand
{
    /** @var string */
    protected static $defaultName = 'backup:list';

    protected function configure(): void
    {
        $this
            ->setDescription('List all available backups.')
            ->setHelp('This command list all available backups for the store.')
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->setupContainer($input, $output);

            $backupPath = $this->upgradeContainer->getProperty(UpgradeContainer::BACKUP_PATH);
            $backups = (new BackupFinder($backupPath))->getAvailableBackups();

            if (empty($backups)) {
                $this->logger->info('No store backup files found in your dedicated directory');

                return ExitCode::SUCCESS;
            }

            $rows = $this->getRows($backups);
            $table = new Table($output);
            $table
                ->setHeaders(['Date', 'Version', 'File name'])
                ->setRows($rows)
            ;
            $table->render();

            return ExitCode::SUCCESS;
        } catch (Exception $e) {
            $this->logger->error('An error occurred during the backup process: ' . $e->getMessage());

            return ExitCode::FAIL;
        }
    }

    /**
     * @param string[] $backups
     *
     * @return array<int, array{datetime: string, version:string, filename: string}>
     */
    private function getRows(array $backups): array
    {
        $rows = [];
        foreach ($backups as $backup) {
            $filename = $backup;
            $pattern = '/V(\d+(\.\d+){1,3})_([0-9]{8})-([0-9]{6})/';
            if (preg_match($pattern, $filename, $matches)) {
                $version = $matches[1];
                $datePart = $matches[3];
                $timePart = $matches[4];

                $dateTime = DateTime::createFromFormat('Ymd His', $datePart . ' ' . $timePart);

                $rows[] =
                    [
                        'datetime' => $dateTime->getTimestamp(),
                        'version' => $version,
                        'filename' => $filename,
                    ];
            }
        }

        // Most recent first
        usort($rows, function ($a, $b) {
            return $b['datetime'] <=> $a['datetime'];
        });

        setlocale(LC_TIME, '');
        // Reassigning dates format
        foreach ($rows as $key => $row) {
            $formattedDateTime = strftime('%x %X', $row['datetime']);
            $rows[$key]['datetime'] = $formattedDateTime;
        }

        return $rows;
    }
}

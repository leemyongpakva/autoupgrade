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

namespace PrestaShop\Module\AutoUpgrade\Services;

use PrestaShop\Module\AutoUpgrade\State;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class LogsService
{
    /** @var State */
    private $state;

    /** @var Translator */
    private $translator;

    /** @var string */
    private $logsPath;

    public function __construct(State $state, Translator $translator, string $logsPath)
    {
        $this->state = $state;
        $this->translator = $translator;
        $this->logsPath = $logsPath;
    }

    /**
     * @param TaskType::TASK_TYPE_* $task
     *
     * @return array{'button_label': string, 'download_path': string, 'filename': string}
     */
    public function getDownloadLogsdData(string $task): array
    {
        $logsPath = $this->getDownloadLogsPath($task);
        return [
            'button_label' => $this->getDownloadLogsLabel($task),
            'download_path' => $logsPath,
            'filename' => basename($logsPath),
        ];
    }

    /**
     * @throws Exception
     *
     * @param TaskType::TASK_TYPE_* $task
     */
    public function getLogsPath(string $task): ?string
    {
        $logPath = null;
        if (is_writable($this->logsPath)) {
            $fileName = $this->state->getProcessTimestamp() . '-' . $task . '.txt';
            $logPath = $this->logsPath . DIRECTORY_SEPARATOR . $fileName;
        }

        return $logPath;
    }

    /**
     * @throws Exception
     *
     * @param TaskType::TASK_TYPE_* $task
     */
    public function getDownloadLogsPath(string $task): ?string
    {
        $logPath = $this->getLogsPath($task);
        $documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

        return str_replace($documentRoot, '', $logPath);
    }

    /**
     * @param TaskType::TASK_TYPE_* $taskType
     */
    private function getDownloadLogsLabel(string $taskType): string
    {
        if ($taskType === TaskType::TASK_TYPE_BACKUP) {
            return $this->translator->trans('Download backup logs');
        } 
        if ($taskType === TaskType::TASK_TYPE_RESTORE) {
            return $this->translator->trans('Download restore logs');
        } 
        if ($taskType === TaskType::TASK_TYPE_UPDATE) {
            return $this->translator->trans('Download update logs');
        } 
    }
}

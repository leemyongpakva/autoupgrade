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

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\Twig\UpdateSteps;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PrestaShop\Module\AutoUpgrade\Traits\displayErrorReportDialogTrait;
use PrestaShop\Module\AutoUpgrade\Twig\UpdateSteps;

class UpdatePageUpdateController extends AbstractPageWithStepController
{
    use displayErrorReportDialogTrait;

    const CURRENT_STEP = UpdateSteps::STEP_UPDATE;

    protected function getPageTemplate(): string
    {
        return 'update';
    }

    protected function getStepTemplate(): string
    {
        return self::CURRENT_STEP;
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::UPDATE_PAGE_UPDATE;
    }

    public function getDownloadLogsButton(): JsonResponse
    {
        try {
            $logsPath = $this->upgradeContainer->getDownloadLogsPath(TaskType::TASK_TYPE_UPDATE);
        } catch (\Exception $e) {
            return AjaxResponseBuilder::errorResponse('Impossible to retrieve logs path');
        }

        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::DOWNLOAD_LOGS_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/components/download_logs.html.twig',
                [
                    'button_label' => $this->upgradeContainer->getTranslator()->trans('Download update logs'),
                    'download_path' => $logsPath,
                    'filename' => basename($logsPath),
                ]
            )
        );
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateSteps = new UpdateSteps($this->upgradeContainer->getTranslator());
        $backupFinder = $this->upgradeContainer->getBackupFinder();

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'success_route' => Routes::UPDATE_STEP_POST_UPDATE,
                'download_logs_route' => Routes::UPDATE_STEP_UPDATE_DOWNLOAD_LOGS,
                'restore_route' => Routes::RESTORE_PAGE_BACKUP_SELECTION,
                'submit_error_report_route' => Routes::UPDATE_STEP_UPDATE_SUBMIT_ERROR_REPORT,
                'initial_process_action' => TaskName::TASK_UPDATE_INITIALIZATION,
                'backup_available' => !empty($backupFinder->getAvailableBackups()),
                'download_logs_parent_id' => PageSelectors::DOWNLOAD_LOGS_PARENT_ID,
            ]
        );
    }
}

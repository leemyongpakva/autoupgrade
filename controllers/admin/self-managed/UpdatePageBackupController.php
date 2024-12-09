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

class UpdatePageBackupController extends AbstractPageWithStepController
{
    const CURRENT_STEP = UpdateSteps::STEP_BACKUP;

    /**
     * @inheritdoc
     */
    public function index()
    {
        return $this->redirectTo(Routes::UPDATE_PAGE_BACKUP_OPTIONS);
    }

    protected function getPageTemplate(): string
    {
        return 'update';
    }

    protected function getStepTemplate(): string
    {
        // Different from self::CURRENT_STEP, because a refresh should return to the backup options.
        return 'backup';
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::UPDATE_PAGE_BACKUP;
    }

    public function getDownloadLogsButton(): JsonResponse
    {
        try {
            $logsPath = $this->upgradeContainer->getDownloadLogsPath(TaskType::TASK_TYPE_BACKUP);
        } catch (\Exception $e) {
            return AjaxResponseBuilder::errorResponse('Impossible to retrieve logs path');
        }

        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::DOWNLOAD_LOGS_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/components/download_logs.html.twig',
                [
                    'button_label' => $this->upgradeContainer->getTranslator()->trans('Download backup logs'),
                    'download_path' => $logsPath,
                    'filename' => basename($logsPath),
                ]
            )
        );
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateSteps = new UpdateSteps($this->upgradeContainer->getTranslator());

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'success_route' => Routes::UPDATE_PAGE_POST_BACKUP,
                'download_logs_route' => Routes::UPDATE_STEP_UPDATE_DOWNLOAD_LOGS,
                'initial_process_action' => TaskName::TASK_BACKUP_INITIALIZATION,
                'download_logs_parent_id' => PageSelectors::DOWNLOAD_LOGS_PARENT_ID,
            ]
        );
    }
}

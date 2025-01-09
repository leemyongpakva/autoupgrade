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

use PrestaShop\Module\AutoUpgrade\DocumentationLinks;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\RestoreSteps;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\Stepper;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class RestorePagePostRestoreController extends AbstractPageWithStepController
{
    const CURRENT_STEP = RestoreSteps::STEP_POST_RESTORE;

    protected function getPageTemplate(): string
    {
        return 'restore';
    }

    protected function getStepTemplate(): string
    {
        return self::CURRENT_STEP;
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::RESTORE_PAGE_POST_RESTORE;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateSteps = new Stepper($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_RESTORE);

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'exit_link' => DIRECTORY_SEPARATOR . $this->upgradeContainer->getProperty(UpgradeContainer::PS_ADMIN_SUBDIR) . DIRECTORY_SEPARATOR . 'index.php',
                'dev_doc_link' => DocumentationLinks::DEV_DOC_UPGRADE_POST_RESTORE_URL,
                'download_logs' => $this->upgradeContainer->getLogsService()->getDownloadLogsData(TaskType::TASK_TYPE_RESTORE),
            ]
        );
    }
}
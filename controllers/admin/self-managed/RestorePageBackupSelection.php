<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\RestoreSteps;
use PrestaShop\Module\AutoUpgrade\Twig\Steps;

class RestorePageBackupSelection extends AbstractPageWithStepController
{
    const CURRENT_STEP = RestoreSteps::STEP_BACKUP_SELECTION;

    protected function getPageTemplate(): string
    {
        return 'restore';
    }

    protected function getStepTemplate(): string
    {
        return 'backup-selection';
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::RESTORE_PAGE_BACKUP_SELECTION;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateSteps = new Steps($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_RESTORE);

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            []
        );
    }
}

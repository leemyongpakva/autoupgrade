<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\RestoreSteps;
use PrestaShop\Module\AutoUpgrade\Twig\Steps;
use Symfony\Component\HttpFoundation\JsonResponse;

class RestorePageBackupSelection extends AbstractPageWithStepController
{
    const CURRENT_STEP = RestoreSteps::STEP_BACKUP_SELECTION;
    const FORM_NAME = 'backup_choice';
    const FORM_FIELDS = [
        RestoreConfiguration::BACKUP_NAME => RestoreConfiguration::BACKUP_NAME
    ];

    public function index()
    {
        $backups = $this->upgradeContainer->getBackupFinder()->getAvailableBackups();

        if (!empty($backups)) {
            return parent::index();
        }

        return $this->redirectTo(Routes::HOME_PAGE);
    }

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
        return Routes::RESTORE_PAGE_BACKUP_SELECTION;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $restoreSteps = new Steps($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_RESTORE);

        $backupsAvailable = $this->upgradeContainer->getBackupFinder()->getSortedAndFormatedAvailableBackups();

        $currentConfiguration = new RestoreConfiguration($this->upgradeContainer->getFileStorage()->load(UpgradeFileNames::RESTORE_CONFIG_FILENAME));

        $currentBackup = $currentConfiguration->getBackupName() ?? $backupsAvailable[0]['filename'];

        return array_merge(
            $restoreSteps->getStepParams($this::CURRENT_STEP),
            [
                'form_backup_selection_name' => self::FORM_NAME,
                'form_route_to_save' => Routes::UPDATE_STEP_VERSION_CHOICE_SAVE_FORM,
                'form_route_to_submit' => Routes::UPDATE_STEP_VERSION_CHOICE_SUBMIT_FORM,
                'form_route_to_delete' => Routes::UPDATE_STEP_VERSION_CHOICE_SUBMIT_FORM,
                'form_fields' => self::FORM_FIELDS,
                'current_backup' => $currentBackup,
                'backups_available' => $backupsAvailable,
            ]
        );
    }

    public function delete(): JsonResponse
    {
        $backup = $this->request->get(self::FORM_FIELDS[RestoreConfiguration::BACKUP_NAME]);
        $this->upgradeContainer->getBackupManager()->deleteBackup($backup);

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_PAGE_RESTORE);
    }

    public function save(): JsonResponse
    {
        $request = $this->request->toArray();
        $this->saveBackupConfiguration($request);

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_STEP_BACKUP_SELECTION);
    }

    public function submit(): JsonResponse
    {
        $request = $this->request->toArray();
        $this->saveBackupConfiguration($request);

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_STEP_RESTORE);
    }

    private function saveBackupConfiguration(array $request)
    {
        $storage = $this->upgradeContainer->getFileStorage();
        $restoreConfiguration = new RestoreConfiguration($request);

        $storage->save($restoreConfiguration, UpgradeFileNames::RESTORE_CONFIG_FILENAME);
    }
}

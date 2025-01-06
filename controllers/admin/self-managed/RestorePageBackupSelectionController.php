<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\RestoreSteps;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\Steps;
use Symfony\Component\HttpFoundation\JsonResponse;

class RestorePageBackupSelectionController extends AbstractPageWithStepController
{
    const CURRENT_STEP = RestoreSteps::STEP_BACKUP_SELECTION;
    const FORM_NAME = 'backup_choice';
    const FORM_FIELDS = [
        RestoreConfiguration::BACKUP_NAME => RestoreConfiguration::BACKUP_NAME,
    ];

    public function index()
    {
        $backups = $this->upgradeContainer->getBackupFinder()->getAvailableBackups();

        if (!empty($backups)) {
            return parent::index();
        }

        return AjaxResponseBuilder::nextRouteResponse(Routes::HOME_PAGE);
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
                'form_route_to_save' => Routes::RESTORE_STEP_BACKUP_SELECTION_SAVE_FORM,
                'form_route_to_submit' => Routes::RESTORE_STEP_BACKUP_SELECTION_SUBMIT_FORM,
                'form_route_to_delete' => Routes::RESTORE_STEP_BACKUP_SELECTION_DELETE_FORM,
                'form_fields' => self::FORM_FIELDS,
                'current_backup' => $currentBackup,
                'backups_available' => $backupsAvailable,
            ]
        );
    }

    public function delete(): JsonResponse
    {
        $backup = $this->request->request->get(self::FORM_FIELDS[RestoreConfiguration::BACKUP_NAME]);
        $this->upgradeContainer->getBackupManager()->deleteBackup($backup);

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_PAGE_BACKUP_SELECTION);
    }

    /**
     * @throws \Exception
     */
    public function save(): JsonResponse
    {
        $this->saveBackupConfiguration();

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_STEP_BACKUP_SELECTION);
    }

    /**
     * @throws \Exception
     */
    public function submit(): JsonResponse
    {
        $this->saveBackupConfiguration();

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_STEP_RESTORE);
    }

    /**
     * @throws \Exception
     */
    private function saveBackupConfiguration(): void
    {
        $configurationStorage = $this->upgradeContainer->getConfigurationStorage();
        $restoreConfiguration = $configurationStorage->loadRestoreConfiguration();

        $config = [
            RestoreConfiguration::BACKUP_NAME => $this->request->request->get(RestoreConfiguration::BACKUP_NAME),
        ];

        $restoreConfiguration->merge($config);
        $configurationStorage->save($restoreConfiguration);
    }
}

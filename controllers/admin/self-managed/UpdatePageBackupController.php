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
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\Twig\UpdateSteps;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdatePageBackupController extends AbstractPageWithStepController
{
    const CURRENT_STEP = UpdateSteps::STEP_BACKUP;

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
        return Routes::UPDATE_PAGE_BACKUP;
    }

    public function submit(): JsonResponse
    {
        return $this->displayModal([
            'noBackUp' => (bool) rand(0, 1),

            // TODO: assets_base_path is provided by all controllers. What about a asset() twig function instead?
            'assets_base_path' => $this->upgradeContainer->getAssetsEnvironment()->getAssetsBaseUrl($this->request),
        ]);
    }

    public function saveOption(): JsonResponse
    {
        $upgradeConfiguration = $this->upgradeContainer->getUpgradeConfiguration();
        $upgradeConfigurationStorage = $this->upgradeContainer->getUpgradeConfigurationStorage();

        $name = $this->request->request->get('name');

        $config = [
            $name => $this->request->request->getBoolean('value'),
        ];

        // TODO: Remove after rebase
        $upgradeConfiguration->validate($config);
        $error = null;
        // TODO: Uncomment after rebase
        // $error = $this->upgradeContainer->getConfigurationValidator()->validate($config);

        if (empty($error)) {
            $upgradeConfiguration->merge($config);
            $upgradeConfigurationStorage->save($upgradeConfiguration, UpgradeFileNames::CONFIG_FILENAME);
        }

        return $this->getRefreshOfForm(array_merge($this->getParams(), ['error' => $error]));
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $upgradeConfiguration = $this->upgradeContainer->getUpgradeConfiguration();
        $updateSteps = new UpdateSteps($this->upgradeContainer->getTranslator());

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'form_route_to_save' => Routes::UPDATE_STEP_BACKUP_SAVE_OPTION,
                'form_route_to_submit' => Routes::UPDATE_STEP_BACKUP_SUBMIT_FORM,

                'form_fields' => [
                    'include_images' => [
                        'field' => 'PS_AUTOUP_KEEP_IMAGES',
                        'value' => $upgradeConfiguration->shouldBackupImages(),
                    ],
                ],
            ]
        );
    }

    private function getRefreshOfForm(array $params): JsonResponse
    {
        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::STEP_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/steps/' . $this->getStepTemplate() . '.html.twig',
                $params
            ),
            $this->displayRouteInUrl()
        );
    }

    private function displayModal(array $params): JsonResponse
    {
        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::MODAL_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/modals/modal-update.html.twig',
                $params
            )
        );
    }
}

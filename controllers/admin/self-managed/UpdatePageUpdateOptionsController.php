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
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Twig\UpdateSteps;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdatePageUpdateOptionsController extends AbstractPageWithStepController
{
    const CURRENT_STEP = UpdateSteps::STEP_UPDATE_OPTIONS;

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
        return Routes::UPDATE_PAGE_UPDATE_OPTIONS;
    }

    public function saveOption(): JsonResponse
    {
        $name = $this->request->request->get('name');
        if ($name === 'PS_DISABLE_OVERRIDES') {
            $this->upgradeContainer->initPrestaShopCore();
            UpgradeConfiguration::updatePSDisableOverrides($this->request->request->getBoolean('value'));
            return new JsonResponse(['success' => true]);
        }

        $upgradeConfiguration = $this->upgradeContainer->getUpgradeConfiguration();
        $upgradeConfigurationStorage = $this->upgradeContainer->getUpgradeConfigurationStorage();

        // TODO: Call the validator

        $upgradeConfiguration->merge([
            $name => $this->request->request->getBoolean('value'),
        ]);

        $success = $upgradeConfigurationStorage->save($upgradeConfiguration, UpgradeFileNames::CONFIG_FILENAME);
        return new JsonResponse(['success' => $success]);
    }

    public function submit(): JsonResponse
    {
        return AjaxResponseBuilder::nextRouteResponse(Routes::UPDATE_PAGE_BACKUP);
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $this->upgradeContainer->initPrestaShopCore();
        $upgradeConfiguration = $this->upgradeContainer->getUpgradeConfiguration();
        $updateSteps = new UpdateSteps($this->upgradeContainer->getTranslator());

        return array_merge(
            $updateSteps->getStepParams(self::CURRENT_STEP),
            [
                'form_route_to_save' => Routes::UPDATE_STEP_UPDATE_OPTIONS_SAVE_OPTION,
                'form_route_to_submit' => Routes::UPDATE_STEP_UPDATE_OPTIONS_SUBMIT_FORM,

                'default_deactive_non_native_modules' => $upgradeConfiguration->shouldDeactivateCustomModules(),
                'default_regenerate_email_templates' => $upgradeConfiguration->shouldRegenerateMailTemplates(),
                'disable_all_overrides' => !$upgradeConfiguration->isOverrideAllowed(),
            ]
        );
    }
}

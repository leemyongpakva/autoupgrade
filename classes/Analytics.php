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

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\Parameters\ConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\State\RestoreState;
use PrestaShop\Module\AutoUpgrade\State\UpdateState;

class Analytics
{
    const SEGMENT_CLIENT_KEY_PHP = 'NrWZk42rDrA56DkEt9Tj18DBirLoRLhj';
    const SEGMENT_CLIENT_KEY_JS = 'RM87m03McDSL4Fvm3GJ3piBPbAL3Fa2i';

    const WITH_COMMON_PROPERTIES = 0;
    const WITH_UPDATE_PROPERTIES = 1;
    const WITH_BACKUP_PROPERTIES = 2;
    const WITH_RESTORE_PROPERTIES = 3;

    // Reusing environment variable from Distribution API
    public const URL_TRACKING_ENV_NAME = 'PS_URL_TRACKING';

    /**
     * @var string
     */
    private $anonymousId;

    /**
     * @var array<int, array<string, mixed>>
     */
    private $properties;

    /**
     * @var ConfigurationStorage
     */
    private $configurationStorage;

    /**
     * @var array{'restore': RestoreState, 'update': UpdateState}
     */
    private $states;

    /**
     * @param array{'properties'?: array<int, array<string, mixed>>} $options
     * @param array{'restore': RestoreState, 'update': UpdateState} $states
     */
    public function __construct(
        ConfigurationStorage $configurationStorage,
        array                $states,
        string               $anonymousUserId,
        array                $options
    ) {
        $this->configurationStorage = $configurationStorage;
        $this->states = $states;

        $this->anonymousId = hash('sha256', $anonymousUserId);
        $this->properties = $options['properties'] ?? [];

        if ($this->hasOptedOut()) {
            return;
        }

        \Segment::init(self::SEGMENT_CLIENT_KEY_PHP);
    }

    /**
     * @param string $event
     * @param self::WITH_*_PROPERTIES $propertiesType
     */
    public function track(string $event, $propertiesType = self::WITH_COMMON_PROPERTIES): void
    {
        if ($this->hasOptedOut()) {
            return;
        }

        \Segment::track(array_merge(
            ['event' => '[SUE] ' . $event],
            $this->getProperties($propertiesType)
        ));
        \Segment::flush();
    }

    /**
     * @param self::WITH_*_PROPERTIES $type
     *
     * @return array<string, mixed>
     */
    public function getProperties($type): array
    {
        $updateConfiguration = $this->configurationStorage->loadUpdateConfiguration();

        switch ($type) {
            case self::WITH_BACKUP_PROPERTIES:
                $additionalProperties = [
                    'backup_files_and_databases' => $updateConfiguration->shouldBackupFilesAndDatabase(),
                    'backup_images' => $updateConfiguration->shouldBackupImages(),
                ];
                $upgradeProperties = $this->properties[self::WITH_BACKUP_PROPERTIES] ?? [];
                $additionalProperties = array_merge($upgradeProperties, $additionalProperties);
                break;
            case self::WITH_UPDATE_PROPERTIES:
                $additionalProperties = [
                    'from_ps_version' => $this->states['update']->getCurrentVersion(),
                    'to_ps_version' => $this->states['update']->getDestinationVersion(),
                    'upgrade_channel' => $updateConfiguration->getChannel(),
                    'disable_non_native_modules' => $updateConfiguration->shouldDeactivateCustomModules(),
                    'switch_to_default_theme' => $updateConfiguration->shouldSwitchToDefaultTheme(),
                    'regenerate_customized_email_templates' => $updateConfiguration->shouldRegenerateMailTemplates(),
                ];
                $upgradeProperties = $this->properties[self::WITH_UPDATE_PROPERTIES] ?? [];
                $additionalProperties = array_merge($upgradeProperties, $additionalProperties);
                break;
            case self::WITH_RESTORE_PROPERTIES:
                $additionalProperties = [
                    'from_ps_version' => $this->properties[self::WITH_COMMON_PROPERTIES]['ps_version'] ?? null,
                    'to_ps_version' => $this->states['restore']->getRestoreVersion(),
                ];
                $rollbackProperties = $this->properties[self::WITH_RESTORE_PROPERTIES] ?? [];
                $additionalProperties = array_merge($rollbackProperties, $additionalProperties);
                break;
            default:
                $additionalProperties = [];
        }

        $commonProperties = $this->properties[self::WITH_COMMON_PROPERTIES] ?? [];

        return [
            'anonymousId' => $this->anonymousId,
            'channel' => 'browser',
            'properties' => array_merge(
                $commonProperties,
                $additionalProperties,
                [
                    'module' => 'autoupgrade',
                ]
            ),
        ];
    }

    private function hasOptedOut(): bool
    {
        return isset($_SERVER[self::URL_TRACKING_ENV_NAME])
            && ((bool) $_SERVER[self::URL_TRACKING_ENV_NAME] === false || $_SERVER[self::URL_TRACKING_ENV_NAME] === 'false');
    }
}

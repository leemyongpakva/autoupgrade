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
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\State\RestoreState;
use PrestaShop\Module\AutoUpgrade\State\UpdateState;

class AnalyticsTest extends TestCase
{
    public function testProperties()
    {
        $fixturesDir = __DIR__ . '/../../fixtures/config/';
        $fileStorage = new FileStorage($fixturesDir);

        $restoreState = (new RestoreState($fileStorage))
            ->setRestoreName('V1.2.3_blablabla-🐶');
        $updateState = (new UpdateState($fileStorage))
            ->setCurrentVersion('8.8.8')
            ->setDestinationVersion('8.8.808');
        $states = [
            'restore' => $restoreState,
            'update' => $updateState,
        ];
        $upgradeConfiguration = (new UpgradeConfiguration([
            UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT => 0,
            UpgradeConfiguration::PS_AUTOUP_CHANGE_DEFAULT_THEME => 1,
            UpgradeConfiguration::PS_AUTOUP_REGEN_EMAIL => 1,
            UpgradeConfiguration::PS_AUTOUP_BACKUP => 1,
            UpgradeConfiguration::PS_AUTOUP_KEEP_IMAGES => 0,
            UpgradeConfiguration::CHANNEL => 'major',
            UpgradeConfiguration::ARCHIVE_ZIP => 'zip.zip',
        ]));

        $analytics = new Analytics(
            $upgradeConfiguration,
            $states,
            'somePathToAutoupgradeModule',
            [
                'properties' => [
                    Analytics::WITH_COMMON_PROPERTIES => [
                        'ps_version' => '8.8.8',
                        'php_version' => '6.0.8',
                        'autoupgrade_version' => '9.8.7',
                    ],
                    Analytics::WITH_UPDATE_PROPERTIES => [
                        'disable_all_overrides' => true,
                        'regenerate_rtl_stylesheet' => false,
                    ],
                ],
            ]
        );

        $this->assertEquals([
            'anonymousId' => '3cbc0821f904fd952a8526f17b9b92a8abde4b394a66c9171cf35c9beb2b4784',
            'channel' => 'browser',
            'properties' => [
                    'ps_version' => '8.8.8',
                    'php_version' => '6.0.8',
                    'autoupgrade_version' => '9.8.7',
                    'module' => 'autoupgrade',
                ],
            ],
            $analytics->getProperties(Analytics::WITH_COMMON_PROPERTIES)
        );

        $this->assertEquals([
            'anonymousId' => '3cbc0821f904fd952a8526f17b9b92a8abde4b394a66c9171cf35c9beb2b4784',
            'channel' => 'browser',
            'properties' => [
                    'ps_version' => '8.8.8',
                    'php_version' => '6.0.8',
                    'autoupgrade_version' => '9.8.7',
                    'disable_all_overrides' => true,
                    'module' => 'autoupgrade',

                    'from_ps_version' => '8.8.8',
                    'to_ps_version' => '8.8.808',
                    'upgrade_channel' => 'major',
                    'disable_non_native_modules' => false,
                    'switch_to_default_theme' => true,
                    'regenerate_customized_email_templates' => true,
                    'regenerate_rtl_stylesheet' => false,
                ],
            ],
            $analytics->getProperties(Analytics::WITH_UPDATE_PROPERTIES)
        );

        $this->assertEquals([
            'anonymousId' => '3cbc0821f904fd952a8526f17b9b92a8abde4b394a66c9171cf35c9beb2b4784',
            'channel' => 'browser',
            'properties' => [
                'ps_version' => '8.8.8',
                'php_version' => '6.0.8',
                'autoupgrade_version' => '9.8.7',
                'module' => 'autoupgrade',

                'backup_files_and_databases' => true,
                'backup_images' => false,
            ],
        ],
            $analytics->getProperties(Analytics::WITH_BACKUP_PROPERTIES)
        );

        $this->assertEquals([
            'anonymousId' => '3cbc0821f904fd952a8526f17b9b92a8abde4b394a66c9171cf35c9beb2b4784',
            'channel' => 'browser',
            'properties' => [
                    'ps_version' => '8.8.8',
                    'php_version' => '6.0.8',
                    'autoupgrade_version' => '9.8.7',
                    'module' => 'autoupgrade',

                    'from_ps_version' => '8.8.8',
                    'to_ps_version' => '1.2.3',
                ],
            ],
            $analytics->getProperties(Analytics::WITH_RESTORE_PROPERTIES)
        );
    }
}

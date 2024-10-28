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

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class ConfigurationValidator
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var LocalChannelConfigurationValidator
     */
    private $localChannelConfigurationValidator;

    public function __construct(Translator $translator, LocalChannelConfigurationValidator $localChannelConfigurationValidator)
    {
        $this->translator = $translator;
        $this->localChannelConfigurationValidator = $localChannelConfigurationValidator;
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<string, string>
     */
    public function validate(array $array = []): array
    {
        $isLocal = isset($array['channel']) && $array['channel'] === Upgrader::CHANNEL_LOCAL;

        foreach ($array as $key => $value) {
            switch ($key) {
                case 'channel':
                    $error = $this->validateChannel($value);
                    break;
                case 'archive_zip':
                    $error = $this->validateArchiveZip($value, $isLocal);
                    break;
                case 'archive_xml':
                    $error = $this->validateArchiveXml($value, $isLocal);
                    break;
                case 'PS_AUTOUP_CUSTOM_MOD_DESACT':
                case 'PS_AUTOUP_KEEP_MAILS':
                case 'PS_AUTOUP_KEEP_IMAGES':
                case 'PS_DISABLE_OVERRIDES':
                    $error = $this->validateBool($value, $key);
                    break;
            }

            if (isset($error)) {
                return [$key => $error];
            }
        }

        return [];
    }

    private function validateChannel(string $channel): ?string
    {
        if ($channel !== Upgrader::CHANNEL_LOCAL && $channel !== Upgrader::CHANNEL_ONLINE) {
            return $this->translator->trans('Unknown channel %s', [$channel]);
        }

        return null;
    }

    private function validateArchiveZip(string $zip, bool $isLocal): ?string
    {
        if ($isLocal && empty($zip)) {
            return $this->translator->trans('No zip archive provided');
        }

        return null;
    }

    private function validateArchiveXml(string $xml, bool $isLocal): ?string
    {
        if ($isLocal && empty($xml)) {
            return $this->translator->trans('No xml archive provided');
        }

        return null;
    }

    private function validateBool(string $boolValue, string $key): ?string
    {
        if ($boolValue === '' || filter_var($boolValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
            return $this->translator->trans('Value must be a boolean for %s', [$key]);
        }

        return null;
    }
}

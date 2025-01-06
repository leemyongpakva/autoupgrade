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

namespace PrestaShop\Module\AutoUpgrade\Twig\Steps;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class UpdateSteps implements StepsInterface
{
    const STEP_VERSION_CHOICE = 'version-choice';
    const STEP_UPDATE_OPTIONS = 'update-options';
    const STEP_BACKUP = 'backup';
    const STEP_UPDATE = 'update';
    const STEP_POST_UPDATE = 'post-update';

    /** @var Translator */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function getSteps(): array
    {
        return [
            self::STEP_VERSION_CHOICE => [
                'title' => $this->translator->trans('Version choice'),
            ],
            self::STEP_UPDATE_OPTIONS => [
                'title' => $this->translator->trans('Update options'),
            ],
            self::STEP_BACKUP => [
                'title' => $this->translator->trans('Backup'),
            ],
            self::STEP_UPDATE => [
                'title' => $this->translator->trans('Update'),
            ],
            self::STEP_POST_UPDATE => [
                'title' => $this->translator->trans('Post-update'),
            ],
        ];
    }
}

<?php

namespace PrestaShop\Module\AutoUpgrade\Twig;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class UpdateSteps
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

    /**
     * @return array<self::STEP_*, array<string,string>>
     */
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

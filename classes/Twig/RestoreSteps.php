<?php

namespace PrestaShop\Module\AutoUpgrade\Twig;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class RestoreSteps
{
    const STEP_BACKUP_SELECTION = 'backup-selection';
    const STEP_RESTORE = 'restore';
    const STEP_POST_RESTORE = 'post-restore';

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
            self::STEP_BACKUP_SELECTION => [
                'title' => $this->translator->trans('Backup selection'),
            ],
            self::STEP_RESTORE => [
                'title' => $this->translator->trans('Restore'),
            ],
            self::STEP_POST_RESTORE => [
                'title' => $this->translator->trans('Post-restore'),
            ],
        ];
    }
}

<?php

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Contains the restore configuration (form params).
 *
 * @extends ArrayCollection<string, mixed>
 */
class RestoreConfiguration extends ArrayCollection
{
    const BACKUP_NAME = 'BACKUP_NAME';

    const RESTORE_CONST_KEYS = [
        self::BACKUP_NAME,
    ];

    public function getBackupName(): ?string
    {
        return $this->get(self::BACKUP_NAME);
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return void
     */
    public function merge(array $array = []): void
    {
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
    }
}

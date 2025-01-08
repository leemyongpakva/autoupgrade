<?php

use PrestaShop\Module\AutoUpgrade\Log\Logger;

class NullLogger extends Logger
{
    public function __construct($fd)
    {
        $this->fd = $fd;
    }

    public function getLastInfo(): ?string
    {
        return null;
    }
}

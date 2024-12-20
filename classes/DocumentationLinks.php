<?php

namespace PrestaShop\Module\AutoUpgrade;

class DocumentationLinks
{
    public const DEV_DOC_URL = 'https://devdocs.prestashop-project.org/8';
    public const DEV_DOC_UP_TO_DATE_URL = self::DEV_DOC_URL . '/basics/keeping-up-to-date';
    public const DEV_DOC_UPGRADE_URL = self::DEV_DOC_UP_TO_DATE_URL . '/upgrade-module';
    public const DEV_DOC_UPGRADE_CLI_URL = self::DEV_DOC_UPGRADE_URL . '/upgrade-cli';
    public const DEV_DOC_UPGRADE_WEB_URL = self::DEV_DOC_UP_TO_DATE_URL . '/use-autoupgrade-module';
    public const PRESTASHOP_PROJECT_URL = 'https://www.prestashop-project.org';
    public const PRESTASHOP_PROJECT_DATA_TRANSPARENCY_URL = self::PRESTASHOP_PROJECT_URL . '/data-transparency';
}

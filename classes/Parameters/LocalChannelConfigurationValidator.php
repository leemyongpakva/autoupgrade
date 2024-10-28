<?php

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use PrestaShop\Module\AutoUpgrade\Services\PrestashopVersionService;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\ZipAction;
use PrestaShop\Module\AutoUpgrade\Exceptions\ZipActionException;
use Exception;

class LocalChannelConfigurationValidator
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var ZipAction
     */
    private $zipAction;

    /**
     * @var string
     */
    private $downloadPath;

    public function __construct(Translator $translator, ZipAction $zipAction, string $downloadPath)
    {
        $this->translator = $translator;
        $this->zipAction = $zipAction;
        $this->downloadPath = $downloadPath;
    }

    public function validate(array $array = []): array {
        $file = $array['archive_zip'];
        $fullFilePath = $this->downloadPath . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($fullFilePath)) {
            return ['archive_zip' => $this->translator->trans('File %s does not exist. Unable to select that channel.', [$file])];
        }

        try {
            $targetVersion = (new PrestashopVersionService($this->zipAction))->extractPrestashopVersionFromZip($fullFilePath);
        } catch (Exception $exception) {
            return ['archive_zip' => $this->translator->trans('We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.')];
        }

        $xmlFile = $array['archive_xml'];
        $fullXmlPath = $this->downloadPath . DIRECTORY_SEPARATOR . $xmlFile;
        if (!empty($xmlFile) && !file_exists($fullXmlPath)) {
            return ['archive_xml' => $this->translator->trans('File %s does not exist. Unable to select that channel.', [$xmlFile])];
        }

        try {
            $xmlVersion = (new PrestashopVersionService($this->zipAction))->extractPrestashopVersionFromXml($fullXmlPath);
        } catch (Exception $exception) {
            return ['archive_xml' => $this->translator->trans('We couldn\'t find a PrestaShop version in the XML file that was uploaded in your local archive. Please try again')];
        }

        if ($xmlVersion !== $targetVersion) {
            return ['global' => $this->translator->trans('The PrestaShop version in your archive doesn’t match the one in XML file. Please fix this issue and try again.')];
        }

        return [];
    }
}

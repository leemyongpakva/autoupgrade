<?php

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use Exception;
use PrestaShop\Module\AutoUpgrade\Services\PrestashopVersionService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class LocalChannelConfigurationValidator
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var PrestashopVersionService
     */
    private $versionService;

    /**
     * @var string
     */
    private $downloadPath;

    /**
     * @var string|null
     */
    private $targetVersion;

    /**
     * @var string|null
     */
    private $xmlVersion;

    public function __construct(Translator $translator, PrestashopVersionService $versionService, string $downloadPath)
    {
        $this->translator = $translator;
        $this->downloadPath = $downloadPath;
        $this->versionService = $versionService;
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<array<string, string>>
     */
    public function validate(array $array = []): array
    {
        // Reset variables to ensure they're empty foreach validation
        $this->targetVersion = null;
        $this->xmlVersion = null;

        $errors = [];

        $zipErrors = $this->validateZipFile($array['archive_zip']);
        if ($zipErrors) {
            $errors[] = $zipErrors;
        }

        $xmlErrors = $this->validateXmlFile($array['archive_xml']);
        if ($xmlErrors) {
            $errors[] = $xmlErrors;
        }

        if (empty($errors)) {
            $versionErrors = $this->validateVersionsMatch();
            if ($versionErrors) {
                $errors[] = $versionErrors;
            }
        }

        return $errors;
    }

    /**
     * @return array<string, string>|null
     */
    private function validateZipFile(string $file): ?array
    {
        $fullFilePath = $this->getFileFullPath($file);

        if (!file_exists($fullFilePath)) {
            return [
                'message' => $this->translator->trans('File %s does not exist. Unable to select that channel.', [$file]),
                'target' => 'archive_zip',
            ];
        }

        try {
            $this->targetVersion = $this->versionService->extractPrestashopVersionFromZip($fullFilePath);
        } catch (Exception $exception) {
            return [
                'message' => $this->translator->trans('We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.'),
                'target' => 'archive_zip',
            ];
        }

        return null;
    }

    /**
     * @return array<string, string>|null
     */
    private function validateXmlFile(string $file): ?array
    {
        $fullXmlPath = $this->getFileFullPath($file);

        if (!file_exists($fullXmlPath)) {
            return [
                'message' => $this->translator->trans('File %s does not exist. Unable to select that channel.', [$file]),
                'target' => 'archive_xml',
            ];
        }

        try {
            $this->xmlVersion = $this->versionService->extractPrestashopVersionFromXml($fullXmlPath);
        } catch (Exception $exception) {
            return [
                'message' => $this->translator->trans('We couldn\'t find a PrestaShop version in the XML file that was uploaded in your local archive. Please try again.'),
                'target' => 'archive_xml',
            ];
        }

        return null;
    }

    /**
     * @return array<string, string>|null
     */
    private function validateVersionsMatch(): ?array
    {
        if ($this->xmlVersion !== null && $this->xmlVersion !== $this->targetVersion) {
            return [
                'message' => $this->translator->trans('The PrestaShop version in your archive doesn’t match the one in XML file. Please fix this issue and try again.'),
            ];
        }

        return null;
    }

    private function getFileFullPath(string $file): string
    {
        return $this->downloadPath . DIRECTORY_SEPARATOR . $file;
    }
}

<?php

namespace PrestaShop\Module\AutoUpgrade\Services;

use PrestaShop\Module\AutoUpgrade\Exceptions\ZipActionException;
use PrestaShop\Module\AutoUpgrade\ZipAction;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;

class PrestashopVersionService
{
    /**
     * @var ZipAction
     */
    private $zipAction;

    public function __construct(ZipAction $zipAction)
    {
        $this->zipAction = $zipAction;
    }

    /**
     * @throws ZipActionException
     * @throws Exception
     */
    public function extractPrestashopVersionFromZip(string $zipFile): string
    {
        $internalZipFileName = 'prestashop.zip';
        $versionFile = 'install/install_version.php';

        if (!file_exists($zipFile)) {
            throw new FileNotFoundException("Unable to find $zipFile file");
        }
        $zip = $this->zipAction->open($zipFile);
        $internalZipContent = $this->zipAction->extractFileFromArchive($zip, $internalZipFileName);
        $zip->close();

        $tempInternalZipPath = $this->createTemporaryFile($internalZipContent);

        $internalZip = $this->zipAction->open($tempInternalZipPath);
        $fileContent = $this->zipAction->extractFileFromArchive($internalZip, $versionFile);
        $internalZip->close();

        @unlink($tempInternalZipPath);

        return $this->extractVersionFromContent($fileContent);
    }

    /**
     * @throws RuntimeException
     */
    public function extractPrestashopVersionFromXml(string $xmlPath): string
    {
        $xml = @simplexml_load_file($xmlPath);

        if (!isset($xml->ps_root_dir['version'])) {
            throw new RuntimeException('Prestashop version not found in file ' . $xmlPath);
        }

        return $xml->ps_root_dir['version'];
    }

    /**
     * @throws IOException
     */
    private function createTemporaryFile(string $content): string
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'internal_zip_');
        if (file_put_contents($tempFilePath, $content) === false) {
            throw new IOException('Unable to create temporary file');
        }

        return $tempFilePath;
    }

    /**
     * @throws RuntimeException
     */
    private function extractVersionFromContent(string $content): string
    {
        $pattern = "/define\\('_PS_INSTALL_VERSION_', '([\\d.]+)'\\);/";
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        } else {
            throw new RuntimeException('Unable to extract version from content');
        }
    }
}

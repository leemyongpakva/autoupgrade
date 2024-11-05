<?php

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\LocalChannelConfigurationValidator;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class LocalChannelConfigurationValidatorTest extends TestCase
{
    /**
     * @var LocalChannelConfigurationValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->container = new UpgradeContainer('/html', '/html/admin');

        $downloadPath = __DIR__ . '/../../fixtures/localChannel/';

        $this->validator = new LocalChannelConfigurationValidator(
            $this->container->getTranslator(),
            $this->container->getPrestashopVersionService(),
            $downloadPath
        );
    }

    public function testValidateReturnsErrorIfZipFileDoesNotExist()
    {
        $data = [UpgradeConfiguration::ARCHIVE_ZIP => 'non_existent.zip', UpgradeConfiguration::ARCHIVE_XML => 'versioned_8.1.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'File ' . $data[UpgradeConfiguration::ARCHIVE_ZIP] . ' does not exist. Unable to select that channel.',
            'target' => UpgradeConfiguration::ARCHIVE_ZIP,
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfNotVersionedZipFile()
    {
        $data = [UpgradeConfiguration::ARCHIVE_ZIP => 'not_versioned_8.2.0.zip', UpgradeConfiguration::ARCHIVE_XML => 'versioned_8.1.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.',
            'target' => UpgradeConfiguration::ARCHIVE_ZIP,
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfXmlFileDoesNotExist()
    {
        $data = [UpgradeConfiguration::ARCHIVE_ZIP => 'versioned_8.2.0.zip', UpgradeConfiguration::ARCHIVE_XML => 'non_existent.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'File ' . $data[UpgradeConfiguration::ARCHIVE_XML] . ' does not exist. Unable to select that channel.',
            'target' => UpgradeConfiguration::ARCHIVE_XML,
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfNotVersionedXmlFile()
    {
        $data = [UpgradeConfiguration::ARCHIVE_ZIP => 'versioned_8.2.0.zip', UpgradeConfiguration::ARCHIVE_XML => 'not_versioned_8.2.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'We couldn\'t find a PrestaShop version in the XML file that was uploaded in your local archive. Please try again.',
            'target' => UpgradeConfiguration::ARCHIVE_XML,
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfVersionsDoNotMatch()
    {
        $data = [UpgradeConfiguration::ARCHIVE_ZIP => 'versioned_8.2.0.zip', UpgradeConfiguration::ARCHIVE_XML => 'versioned_8.1.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'The PrestaShop version in your archive doesn’t match the one in XML file. Please fix this issue and try again.',
        ], $result[0]);
    }

    public function testValidatePassesWithValidFiles()
    {
        $data = [UpgradeConfiguration::ARCHIVE_ZIP => 'versioned_8.2.0.zip', UpgradeConfiguration::ARCHIVE_XML => 'versioned_8.2.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertEmpty($result);
    }
}

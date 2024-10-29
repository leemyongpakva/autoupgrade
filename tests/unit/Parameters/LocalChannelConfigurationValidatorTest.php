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
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

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
        $data = ['archive_zip' => 'non_existent.zip'];
        $result = $this->validator->validate($data);

        $this->assertSame(['archive_zip' => 'File ' . $data['archive_zip'] . ' does not exist. Unable to select that channel.'], $result);
    }

    public function testValidateReturnsErrorIfNotVersionedZipFile()
    {
        $data = ['archive_zip' => 'not_versioned_8.2.0.zip'];
        $result = $this->validator->validate($data);

        $this->assertSame(['archive_zip' => 'We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.'], $result);
    }

    public function testValidateReturnsErrorIfXmlFileDoesNotExist()
    {
        $data = ['archive_zip' => 'versioned_8.2.0.zip', 'archive_xml' => 'non_existent.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame(['archive_xml' => 'File ' . $data['archive_xml'] . ' does not exist. Unable to select that channel.'], $result);
    }

    public function testValidateReturnsErrorIfNotVersionedXmlFile()
    {
        $data = ['archive_zip' => 'versioned_8.2.0.zip', 'archive_xml' => 'not_versioned_8.2.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame(['archive_xml' => 'We couldn\'t find a PrestaShop version in the XML file that was uploaded in your local archive. Please try again.'], $result);
    }

    public function testValidateReturnsErrorIfVersionsDoNotMatch()
    {
        $data = ['archive_zip' => 'versioned_8.2.0.zip', 'archive_xml' => 'versioned_8.1.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame(['global' => 'The PrestaShop version in your archive doesn’t match the one in XML file. Please fix this issue and try again.'], $result);
    }

    public function testValidatePassesWithValidFiles()
    {
        $data = ['archive_zip' => 'versioned_8.2.0.zip', 'archive_xml' => 'versioned_8.2.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertEmpty($result);
    }
}

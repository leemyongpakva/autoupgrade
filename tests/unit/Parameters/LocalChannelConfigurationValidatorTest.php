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
        $data = ['archive_zip' => 'non_existent.zip', 'archive_xml' => 'versioned_8.1.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'File ' . $data['archive_zip'] . ' does not exist. Unable to select that channel.',
            'target' => 'archive_zip',
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfNotVersionedZipFile()
    {
        $data = ['archive_zip' => 'not_versioned_8.2.0.zip', 'archive_xml' => 'versioned_8.1.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.',
            'target' => 'archive_zip',
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfXmlFileDoesNotExist()
    {
        $data = ['archive_zip' => 'versioned_8.2.0.zip', 'archive_xml' => 'non_existent.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'File ' . $data['archive_xml'] . ' does not exist. Unable to select that channel.',
            'target' => 'archive_xml',
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfNotVersionedXmlFile()
    {
        $data = ['archive_zip' => 'versioned_8.2.0.zip', 'archive_xml' => 'not_versioned_8.2.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'We couldn\'t find a PrestaShop version in the XML file that was uploaded in your local archive. Please try again.',
            'target' => 'archive_xml',
        ], $result[0]);
    }

    public function testValidateReturnsErrorIfVersionsDoNotMatch()
    {
        $data = ['archive_zip' => 'versioned_8.2.0.zip', 'archive_xml' => 'versioned_8.1.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertSame([
            'message' => 'The PrestaShop version in your archive doesn’t match the one in XML file. Please fix this issue and try again.',
        ], $result[0]);
    }

    public function testValidatePassesWithValidFiles()
    {
        $data = ['archive_zip' => 'versioned_8.2.0.zip', 'archive_xml' => 'versioned_8.2.0.xml'];
        $result = $this->validator->validate($data);

        $this->assertEmpty($result);
    }
}

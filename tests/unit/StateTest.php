<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\State;

class StateTest extends TestCase
{
    /**
     * @var State
     */
    private $state;

    protected function setUp(): void
    {
        $fixturesDir = __DIR__ . '/../../fixtures/config/';
        $fileStorage = new FileConfigurationStorage($fixturesDir);

        $this->state = new State($fileStorage);

        array_map('unlink', glob($fixturesDir . '*.var'));
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob(__DIR__ . '/../../fixtures/config/*.var'));
    }

    public function testClassReceivesProperty()
    {
        $this->state->importFromArray(['backupName' => 'doge']);
        $exported = $this->state->export();

        $this->assertSame('doge', $this->state->getBackupName());
        $this->assertSame('doge', $exported['backupName']);
    }

    public function testClassIgnoresRandomData()
    {
        $this->state->importFromArray([
            'wow' => 'epic',
            'backupName' => 'doge',
        ]);
        $exported = $this->state->export();

        $this->assertArrayNotHasKey('wow', $exported);
        $this->assertSame('doge', $exported['backupName']);
    }

    // Tests with encoded data

    public function testClassReceivesPropertyFromEncodedData()
    {
        $modules = [
            22320 => 'ps_imageslider',
            22323 => 'ps_socialfollow',
        ];
        $data = [
            'nextParams' => [
                'backupName' => 'doge',
                'modules_addons' => $modules,
            ],
        ];
        $encodedData = base64_encode(json_encode($data));
        $this->state->importFromEncodedData($encodedData);
        $exported = $this->state->export();

        $this->assertSame('doge', $this->state->getBackupName());
        $this->assertSame('doge', $exported['backupName']);
    }

    public function testGetRestoreVersion()
    {
        $this->assertSame(
            '1.7.8.11',
            $this->state->setRestoreName('V1.7.8.11_20240604-170048-3ceb32b2')
                ->getRestoreVersion()
        );

        $this->assertSame(
            '8.1.6',
            $this->state->setRestoreName('V8.1.6_20240604-170048-3ceb32b2')
                ->getRestoreVersion()
        );
    }

    public function testProgressionValue()
    {
        $this->assertSame(null, $this->state->getProgressPercentage());

        $this->state->setProgressPercentage(0);
        $this->assertSame(0, $this->state->getProgressPercentage());

        $this->state->setProgressPercentage(55);
        $this->assertSame(55, $this->state->getProgressPercentage());

        // Percentage cannot go down, an exception will be thrown
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Updated progress percentage cannot be lower than the currently set one.');

        $this->state->setProgressPercentage(10);
    }
}

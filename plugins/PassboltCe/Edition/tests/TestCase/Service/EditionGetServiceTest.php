<?php
declare(strict_types=1);

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         5.13.0
 */
namespace Passbolt\Edition\Test\TestCase\Service;

use App\Test\Lib\AppTestCaseV5;
use Passbolt\Edition\Model\Dto\EditionDto;
use Passbolt\Edition\Service\EditionGetService;
use Passbolt\Edition\Test\Factory\EditionOrganizationSettingFactory;

/**
 * @covers \Passbolt\Edition\Service\EditionGetService
 */
class EditionGetServiceTest extends AppTestCaseV5
{
    private EditionGetService $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new EditionGetService();
    }

    public function tearDown(): void
    {
        unset($this->sut);
        parent::tearDown();
    }

    public function testEditionGetService_NoRow_ReturnsCeDefault(): void
    {
        $dto = $this->sut->get();

        $this->assertInstanceOf(EditionDto::class, $dto);
        $this->assertSame(EditionDto::EDITION_CE, $dto->getEdition());
        $this->assertTrue($dto->isCe());
        $this->assertFalse($dto->isPro());
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function editionGetServiceValidValuesProvider(): array
    {
        return [
            'ce literal' => [EditionDto::EDITION_CE, EditionDto::EDITION_CE],
            'pro literal' => [EditionDto::EDITION_PRO, EditionDto::EDITION_PRO],
        ];
    }

    /**
     * @dataProvider editionGetServiceValidValuesProvider
     * @param string $storedValue Value persisted in `organization_settings.value`.
     * @param string $expectedEdition Expected sanitised edition.
     * @return void
     */
    public function testEditionGetService_RowWith_ReturnsExpected(string $storedValue, string $expectedEdition): void
    {
        EditionOrganizationSettingFactory::make()->setField('value', $storedValue)->persist();
        $result = $this->sut->get()->getEdition();
        $this->assertSame($expectedEdition, $result);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function editionGetServiceSanitizationCasesValuesProvider(): array
    {
        return [
            'unknown literal falls back to ce' => ['unknown', EditionDto::EDITION_CE],
            'empty string falls back to ce' => ['', EditionDto::EDITION_CE],
            'uppercase pro normalises to pro' => ['PRO', EditionDto::EDITION_PRO],
            'whitespace-padded ce normalises to ce' => [" ce \n", EditionDto::EDITION_CE],
        ];
    }

    /**
     * @dataProvider editionGetServiceSanitizationCasesValuesProvider
     * @param string $storedValue Raw value persisted in the DB.
     * @param string $expectedEdition Expected sanitised edition.
     * @return void
     */
    public function testEditionGetService_RowWithUnknownValue_FallbacksToCe(
        string $storedValue,
        string $expectedEdition
    ): void {
        EditionOrganizationSettingFactory::make()->setField('value', $storedValue)->persist();
        $result = $this->sut->get()->getEdition();
        $this->assertSame($expectedEdition, $result);
    }
}

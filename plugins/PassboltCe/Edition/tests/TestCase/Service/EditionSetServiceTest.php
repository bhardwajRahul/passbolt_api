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
use Cake\Http\Exception\ForbiddenException;
use Passbolt\Edition\Model\Dto\EditionDto;
use Passbolt\Edition\Service\EditionSetService;
use Passbolt\Edition\Test\Factory\EditionOrganizationSettingFactory;

/**
 * @covers \Passbolt\Edition\Service\EditionSetService
 */
class EditionSetServiceTest extends AppTestCaseV5
{
    private EditionSetService $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new EditionSetService();
    }

    public function tearDown(): void
    {
        unset($this->sut);
        parent::tearDown();
    }

    public function testEditionSetService_setToPro_PersistsProValue(): void
    {
        $uac = $this->mockAdminAccessControl();
        $entity = $this->sut->setToPro($uac);

        $result = $entity->get('value');
        $this->assertSame(EditionDto::EDITION_PRO, $result);
        $this->assertSame(1, EditionOrganizationSettingFactory::count());
    }

    public function testEditionSetService_setToCe_PersistsCeValue(): void
    {
        $uac = $this->mockAdminAccessControl();
        $entity = $this->sut->setToCe($uac);

        $result = $entity->get('value');
        $this->assertSame(EditionDto::EDITION_CE, $result);
        $this->assertSame(1, EditionOrganizationSettingFactory::count());
    }

    public function testEditionSetService_setToPro_UpdatesExistingRow(): void
    {
        EditionOrganizationSettingFactory::make()->setField('value', EditionDto::EDITION_CE)->persist();
        $uac = $this->mockAdminAccessControl();

        $this->sut->setToPro($uac);

        $result = EditionOrganizationSettingFactory::firstOrFail()->get('value');
        $this->assertSame(EditionDto::EDITION_PRO, $result);
        $this->assertSame(1, EditionOrganizationSettingFactory::count());
    }

    public function testEditionSetService_setToCe_UpdatesExistingRow(): void
    {
        EditionOrganizationSettingFactory::make()->setField('value', EditionDto::EDITION_PRO)->persist();
        $uac = $this->mockAdminAccessControl();

        $this->sut->setToCe($uac);

        $result = EditionOrganizationSettingFactory::firstOrFail()->get('value');
        $this->assertSame(EditionDto::EDITION_CE, $result);
        $this->assertSame(1, EditionOrganizationSettingFactory::count());
    }

    public function testEditionSetService_setToPro_ThrowsForNonAdmin(): void
    {
        $uac = $this->mockUserAccessControl();
        $this->expectException(ForbiddenException::class);
        $this->sut->setToPro($uac);
    }

    public function testEditionSetService_setToCe_ThrowsForNonAdmin(): void
    {
        $uac = $this->mockUserAccessControl();
        $this->expectException(ForbiddenException::class);
        $this->sut->setToCe($uac);
    }
}

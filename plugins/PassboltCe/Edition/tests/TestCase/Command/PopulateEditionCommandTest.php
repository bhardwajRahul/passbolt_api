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
namespace Passbolt\Edition\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\Edition\Model\Dto\EditionDto;
use Passbolt\Edition\Model\Table\EditionOrganizationTable;
use Passbolt\Edition\Test\Factory\EditionOrganizationSettingFactory;

/**
 * @covers \Passbolt\Edition\Command\PopulateEditionCommand
 */
class PopulateEditionCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;
    use LocatorAwareTrait;
    use TruncateDirtyTables;

    public function testPopulateEditionCommand_Help(): void
    {
        $this->exec('passbolt populate_edition -h');
        $this->assertExitSuccess();
        $this->assertOutputContains('Populate the edition row in organization_settings if missing.');
    }

    public function testPopulateEditionCommand_Success_WritesCeRowOnFreshInstance(): void
    {
        $this->exec('passbolt populate_edition');

        $this->assertExitSuccess();
        $this->assertOutputContains('Edition row populated successfully.');
        $this->assertSame(EditionDto::EDITION_CE, $this->fetchEditionRow()->get('value'));
    }

    public function testPopulateEditionCommand_Success_NoOpWhenAlreadyPopulated(): void
    {
        EditionOrganizationSettingFactory::make()->setField('value', EditionDto::EDITION_PRO)->persist();

        $this->exec('passbolt populate_edition');

        $this->assertExitSuccess();
        // Pre-existing PRO row preserved verbatim (idempotence).
        $this->assertSame(EditionDto::EDITION_PRO, $this->fetchEditionRow()->get('value'));
        $this->assertSame(1, $this->countEditionRows());
    }

    private function fetchEditionRow(): EntityInterface
    {
        return $this->fetchTable('Passbolt/Edition.EditionOrganization')
            ->find()
            ->where(['property' => EditionOrganizationTable::PROPERTY_NAME])
            ->firstOrFail();
    }

    private function countEditionRows(): int
    {
        return $this->fetchTable('Passbolt/Edition.EditionOrganization')
            ->find()
            ->where(['property' => EditionOrganizationTable::PROPERTY_NAME])
            ->count();
    }
}

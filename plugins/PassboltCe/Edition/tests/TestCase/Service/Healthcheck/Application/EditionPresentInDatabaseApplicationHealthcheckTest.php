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
namespace Passbolt\Edition\Test\TestCase\Service\Healthcheck\Application;

use App\Service\Healthcheck\HealthcheckServiceCollector;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\Edition\Model\Dto\EditionDto;
use Passbolt\Edition\Service\Healthcheck\Application\EditionPresentInDatabaseApplicationHealthcheck;
use Passbolt\Edition\Test\Factory\EditionOrganizationSettingFactory;

/**
 * @covers \Passbolt\Edition\Service\Healthcheck\Application\EditionPresentInDatabaseApplicationHealthcheck
 */
class EditionPresentInDatabaseApplicationHealthcheckTest extends TestCase
{
    use TruncateDirtyTables;

    public function testEditionPresentInDatabaseApplicationHealthcheck_Check_PassesAndReportsPro(): void
    {
        EditionOrganizationSettingFactory::make()->setField('value', EditionDto::EDITION_PRO)->persist();

        $sut = (new EditionPresentInDatabaseApplicationHealthcheck())->check();

        $this->assertTrue($sut->isPassed());
        $this->assertStringContainsString('PRO', $sut->getSuccessMessage());
    }

    public function testEditionPresentInDatabaseApplicationHealthcheck_Check_PassesAndReportsCe(): void
    {
        EditionOrganizationSettingFactory::make()->setField('value', EditionDto::EDITION_CE)->persist();

        $sut = (new EditionPresentInDatabaseApplicationHealthcheck())->check();

        $this->assertTrue($sut->isPassed());
        $this->assertStringContainsString('CE', $sut->getSuccessMessage());
    }

    public function testEditionPresentInDatabaseApplicationHealthcheck_Check_FailsWithWarningWhenRowMissing(): void
    {
        // Empty organization_settings — no edition row.
        $sut = (new EditionPresentInDatabaseApplicationHealthcheck())->check();

        $this->assertFalse($sut->isPassed());
        $this->assertSame(HealthcheckServiceCollector::LEVEL_ERROR, $sut->level());
        $this->assertStringContainsString('missing', $sut->getFailureMessage());
    }

    public function testEditionPresentInDatabaseApplicationHealthcheck_Metadata(): void
    {
        $sut = new EditionPresentInDatabaseApplicationHealthcheck();

        $this->assertSame(HealthcheckServiceCollector::DOMAIN_APPLICATION, $sut->domain());
        $this->assertSame(HealthcheckServiceCollector::DOMAIN_APPLICATION, $sut->cliOption());
        $this->assertSame('editionFlag', $sut->getLegacyArrayKey());
        $this->assertNotEmpty($sut->getHelpMessage());
    }
}

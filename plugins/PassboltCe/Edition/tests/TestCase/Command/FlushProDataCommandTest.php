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

use App\Test\Lib\Utility\UserAccessControlTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\Edition\Service\EditionSetService;
use Passbolt\MfaPolicies\Test\Factory\MfaPoliciesSettingFactory;

/**
 * @covers \Passbolt\Edition\Command\FlushProDataCommand
 */
class FlushProDataCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;
    use LocatorAwareTrait;
    use TruncateDirtyTables;
    use UserAccessControlTrait;

    public function testFlushProDataCommand_Success_WipesResidualProData(): void
    {
        // CE instance with leftover PRO data — the scenario this command exists for.
        MfaPoliciesSettingFactory::make()->persist();

        $this->exec('passbolt flush_pro_data', ['y']);

        $this->assertExitSuccess();
        $this->assertOutputContains('Residual PRO data was flushed.');
        $this->assertSame(0, $this->fetchTable('Passbolt/MfaPolicies.MfaPoliciesSettings')->find()->count());
    }

    public function testFlushProDataCommand_AbortsWhenUserDeclinesConfirmation(): void
    {
        MfaPoliciesSettingFactory::make()->persist();

        $this->exec('passbolt flush_pro_data', ['n']);

        $this->assertExitSuccess();
        $this->assertOutputContains('Aborting...');
        // Data is untouched.
        $this->assertSame(1, $this->fetchTable('Passbolt/MfaPolicies.MfaPoliciesSettings')->find()->count());
    }

    public function testFlushProDataCommand_Error_RefusesOnProInstance(): void
    {
        (new EditionSetService())->setToPro($this->mockAdminAccessControl());
        MfaPoliciesSettingFactory::make()->persist();

        // 'y' is buffered in case the prompt is reached (it shouldn't be).
        $this->exec('passbolt flush_pro_data', ['y']);

        $this->assertExitError();
        $this->assertErrorContains('This command can only run on a CE instance.');
        // The PRO data was not touched.
        $this->assertSame(1, $this->fetchTable('Passbolt/MfaPolicies.MfaPoliciesSettings')->find()->count());
    }
}

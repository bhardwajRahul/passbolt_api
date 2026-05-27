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

namespace Passbolt\Scim\Test\TestCase\Service\Edition;

use App\Test\Factory\OrganizationSettingFactory;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\Scim\Service\Edition\ScimDowngradeCleanupService;
use Passbolt\Scim\Test\Factory\ScimEntryFactory;
use Passbolt\Scim\Test\Factory\ScimSettingFactory;

/**
 * @covers \Passbolt\Scim\Service\Edition\ScimDowngradeCleanupService
 */
class ScimDowngradeCleanupServiceTest extends TestCase
{
    use TruncateDirtyTables;

    public function testScimDowngradeCleanupService_Cleanup_TruncatesScimEntriesAndDeletesScimSetting(): void
    {
        ScimEntryFactory::make(3)->withUser()->persist();
        ScimSettingFactory::make()->default()->persist();

        (new ScimDowngradeCleanupService())->cleanup();

        $this->assertSame(0, ScimEntryFactory::count());
        $this->assertSame(0, ScimSettingFactory::count());
    }

    public function testScimDowngradeCleanupService_Cleanup_IsIdempotentWhenNoDataExists(): void
    {
        (new ScimDowngradeCleanupService())->cleanup();

        $this->assertSame(0, ScimEntryFactory::count());
        $this->assertSame(0, ScimSettingFactory::count());
    }

    public function testScimDowngradeCleanupService_Cleanup_DoesNotTouchUnrelatedOrgSettings(): void
    {
        ScimSettingFactory::make()->default()->persist();
        OrganizationSettingFactory::make()->setPropertyAndValue('unrelated', 'value')->persist();

        (new ScimDowngradeCleanupService())->cleanup();

        $this->assertSame(1, OrganizationSettingFactory::count());
        $this->assertSame('unrelated', OrganizationSettingFactory::firstOrFail()->get('property'));
    }
}

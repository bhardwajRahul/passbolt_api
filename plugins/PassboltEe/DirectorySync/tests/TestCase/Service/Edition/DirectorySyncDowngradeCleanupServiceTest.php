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

namespace Passbolt\DirectorySync\Test\TestCase\Service\Edition;

use App\Test\Factory\OrganizationSettingFactory;
use App\Utility\UuidFactory;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\DirectorySync\Service\Edition\DirectorySyncDowngradeCleanupService;
use Passbolt\DirectorySync\Test\Factory\DirectoryEntryFactory;
use Passbolt\DirectorySync\Test\Factory\DirectoryOrgSettingFactory;
use Passbolt\DirectorySync\Test\Factory\DirectoryRelationFactory;
use Passbolt\DirectorySync\Test\Factory\DirectoryReportFactory;
use Passbolt\DirectorySync\Test\Factory\DirectoryReportsItemFactory;

/**
 * @covers \Passbolt\DirectorySync\Service\Edition\DirectorySyncDowngradeCleanupService
 */
class DirectorySyncDowngradeCleanupServiceTest extends TestCase
{
    use TruncateDirtyTables;

    public function testDirectorySyncDowngradeCleanupService_Cleanup_TruncatesAllTablesAndDeletesSetting(): void
    {
        DirectoryEntryFactory::make(2)->withUser()->persist();
        DirectoryRelationFactory::make(2)->persist();
        DirectoryReportFactory::make(2)->persist();
        DirectoryReportsItemFactory::make(2)->setReportId(UuidFactory::uuid())->persist();
        DirectoryOrgSettingFactory::make()->default()->persist();

        (new DirectorySyncDowngradeCleanupService())->cleanup();

        $this->assertSame(0, DirectoryEntryFactory::count());
        $this->assertSame(0, DirectoryRelationFactory::count());
        $this->assertSame(0, DirectoryReportFactory::count());
        $this->assertSame(0, DirectoryReportsItemFactory::count());
        $this->assertSame(0, DirectoryOrgSettingFactory::count());
    }

    public function testDirectorySyncDowngradeCleanupService_Cleanup_IsIdempotentWhenNoDataExists(): void
    {
        (new DirectorySyncDowngradeCleanupService())->cleanup();

        $this->assertSame(0, DirectoryEntryFactory::count());
    }

    public function testDirectorySyncDowngradeCleanupService_Cleanup_DoesNotTouchUnrelatedOrgSettings(): void
    {
        DirectoryOrgSettingFactory::make()->default()->persist();
        OrganizationSettingFactory::make()->setPropertyAndValue('unrelated', 'value')->persist();

        (new DirectorySyncDowngradeCleanupService())->cleanup();

        $this->assertSame(1, OrganizationSettingFactory::count());
        $this->assertSame('unrelated', OrganizationSettingFactory::firstOrFail()->get('property'));
    }
}

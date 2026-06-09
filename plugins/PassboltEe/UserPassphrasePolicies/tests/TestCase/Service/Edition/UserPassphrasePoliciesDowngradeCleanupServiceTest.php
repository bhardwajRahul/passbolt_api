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

namespace Passbolt\UserPassphrasePolicies\Test\TestCase\Service\Edition;

use App\Test\Factory\OrganizationSettingFactory;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\UserPassphrasePolicies\Service\Edition\UserPassphrasePoliciesDowngradeCleanupService;
use Passbolt\UserPassphrasePolicies\Test\Factory\UserPassphrasePoliciesSettingFactory;

/**
 * @covers \Passbolt\UserPassphrasePolicies\Service\Edition\UserPassphrasePoliciesDowngradeCleanupService
 */
class UserPassphrasePoliciesDowngradeCleanupServiceTest extends TestCase
{
    use TruncateDirtyTables;

    public function testUserPassphrasePoliciesDowngradeCleanupService_Cleanup_DeletesTheSetting(): void
    {
        UserPassphrasePoliciesSettingFactory::make()->persist();

        (new UserPassphrasePoliciesDowngradeCleanupService())->cleanup();

        $this->assertSame(0, UserPassphrasePoliciesSettingFactory::count());
    }

    public function testUserPassphrasePoliciesDowngradeCleanupService_Cleanup_IsIdempotentWhenNoSettingExists(): void
    {
        (new UserPassphrasePoliciesDowngradeCleanupService())->cleanup();

        $this->assertSame(0, UserPassphrasePoliciesSettingFactory::count());
    }

    public function testUserPassphrasePoliciesDowngradeCleanupService_Cleanup_DoesNotTouchUnrelatedOrgSettings(): void
    {
        UserPassphrasePoliciesSettingFactory::make()->persist();
        OrganizationSettingFactory::make()->setPropertyAndValue('unrelated', 'value')->persist();

        (new UserPassphrasePoliciesDowngradeCleanupService())->cleanup();

        $this->assertSame(1, OrganizationSettingFactory::count());
        $this->assertSame('unrelated', OrganizationSettingFactory::firstOrFail()->get('property'));
    }
}

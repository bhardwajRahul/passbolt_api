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

namespace Passbolt\Subscription\Test\TestCase\Service\Subscriptions;

use App\Test\Factory\OrganizationSettingFactory;
use App\Test\Lib\Utility\UserAccessControlTrait;
use Cake\Http\Exception\ForbiddenException;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\Subscription\Service\Subscriptions\SubscriptionKeyDeleteService;
use Passbolt\Subscription\Test\SubscriptionFactory;

/**
 * @covers \Passbolt\Subscription\Service\Subscriptions\SubscriptionKeyDeleteService
 */
class SubscriptionKeyDeleteServiceTest extends TestCase
{
    use TruncateDirtyTables;
    use UserAccessControlTrait;

    public function testSubscriptionKeyDeleteService_Delete_RemovesTheSubscriptionRow(): void
    {
        SubscriptionFactory::make()->persist();
        $this->assertSame(1, SubscriptionFactory::count());

        (new SubscriptionKeyDeleteService())->delete($this->mockAdminAccessControl());

        $this->assertSame(0, SubscriptionFactory::count());
    }

    public function testSubscriptionKeyDeleteService_Delete_IsNoOpWhenNoSubscriptionExists(): void
    {
        (new SubscriptionKeyDeleteService())->delete($this->mockAdminAccessControl());

        $this->assertSame(0, SubscriptionFactory::count());
    }

    public function testSubscriptionKeyDeleteService_Delete_ThrowsForbiddenWhenUacIsNotAdmin(): void
    {
        SubscriptionFactory::make()->persist();

        $this->expectException(ForbiddenException::class);
        try {
            (new SubscriptionKeyDeleteService())->delete($this->mockUserAccessControl());
        } finally {
            // Row must not have been deleted.
            $this->assertSame(1, SubscriptionFactory::count());
        }
    }

    public function testSubscriptionKeyDeleteService_Delete_DoesNotTouchUnrelatedOrgSettings(): void
    {
        SubscriptionFactory::make()->persist();
        OrganizationSettingFactory::make()->setPropertyAndValue('unrelated', 'value')->persist();

        (new SubscriptionKeyDeleteService())->delete($this->mockAdminAccessControl());

        $this->assertSame(1, OrganizationSettingFactory::count());
        $this->assertSame('unrelated', OrganizationSettingFactory::firstOrFail()->get('property'));
    }
}

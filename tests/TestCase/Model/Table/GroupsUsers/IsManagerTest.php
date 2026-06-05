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

namespace App\Test\TestCase\Model\Table\GroupsUsers;

use App\Test\Factory\GroupFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppTestCase;
use App\Utility\UuidFactory;
use Cake\ORM\TableRegistry;

class IsManagerTest extends AppTestCase
{
    /**
     * @var \App\Model\Table\GroupsUsersTable
     */
    public $GroupsUsers;

    public function setUp(): void
    {
        parent::setUp();
        $this->GroupsUsers = TableRegistry::getTableLocator()->get('GroupsUsers');
    }

    public function tearDown(): void
    {
        unset($this->GroupsUsers);
        parent::tearDown();
    }

    public function testIsManager_InvalidUserIdData()
    {
        $this->assertFalse($this->GroupsUsers->isManager(
            'K7mP9xQ2nR4vL8wY3jH5gB6tF1cZ0aDs',
            '8566f032-ecd7-46dd-badd-73467bee7f85',
        ));
    }

    public function testIsManager_InvalidGroupIdData()
    {
        $this->assertFalse($this->GroupsUsers->isManager(
            '7d38187d-0e02-4431-9649-a956d5bc6749',
            'B4fncWvw5ha2brpcCh7NVSTMPa1AnEoufFonPMSy9fN',
        ));
    }

    public function testIsManager_Success()
    {
        $admin = UserFactory::make()->persist();
        $group = GroupFactory::make()->withGroupsManagersFor([$admin])->persist();
        $this->assertTrue($this->GroupsUsers->isManager($admin->id, $group->id));
    }

    public function testIsManager_IsNotTheManager()
    {
        $user = UserFactory::make()->persist();
        $group = GroupFactory::make()->withGroupsUsersFor([$user])->persist();
        $this->assertFalse($this->GroupsUsers->isManager($user->id, $group->id));
    }

    public function testIsManager_MoreThanOneManager()
    {
        [$ada, $jean] = UserFactory::make(2)->persist();
        $group = GroupFactory::make()->withGroupsManagersFor([$ada, $jean])->persist();
        $this->assertTrue($this->GroupsUsers->isManager($ada->id, $group->id));
        $this->assertTrue($this->GroupsUsers->isManager($jean->id, $group->id));
    }

    public function testIsManager_NoEntriesInDB()
    {
        $userId = UuidFactory::uuid();
        $groupId = UuidFactory::uuid();
        $this->assertFalse($this->GroupsUsers->isManager($userId, $groupId));
    }

    public function testIsManager_WithEntriesInDB()
    {
        $user = UserFactory::make()->persist();
        $group = GroupFactory::make()->persist();
        $this->assertFalse($this->GroupsUsers->isManager($user->id, $group->id));
    }
}

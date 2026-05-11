<?php
declare(strict_types=1);

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SARL (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SARL (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.2.0
 */

namespace Passbolt\DirectorySync\Test\TestCase\Controller;

use App\Test\Factory\UserFactory;
use App\Utility\UuidFactory;
use Cake\ORM\TableRegistry;
use Passbolt\DirectorySync\Test\Utility\DirectorySyncDeprecatedIntegrationTestCase;

class DirectoryIgnoreDeleteControllerTest extends DirectorySyncDeprecatedIntegrationTestCase
{
    public array $fixtures = [];

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreDelete
     */
    public function testDirectorySyncControllerIgnoreDeleteSuccess()
    {
        $this->logInAsAdmin();
        $user = UserFactory::make()->persist();
        $this->postJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=2");
        $this->deleteJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=2");
        $this->assertSuccess();
        $DirectoryIgnore = TableRegistry::getTableLocator()->get('Passbolt/DirectorySync.DirectoryIgnore');
        $deletedIgnore = $DirectoryIgnore->find('all')->where(['id' => $user->get('id')])->first();
        $this->assertempty($deletedIgnore);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreDelete
     */
    public function testDirectorySyncControllerIgnoreDeleteErrorNotValidId()
    {
        $this->logInAsAdmin();
        $userId = 'invalid-id';
        $this->deleteJson("/directorysync/ignore/users/$userId.json");
        $this->assertError(400);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreDelete
     */
    public function testDirectorySyncControllerIgnoreDeleteErrorNotExist()
    {
        $this->logInAsAdmin();
        $userId = UuidFactory::uuid();
        $this->deleteJson("/directorysync/ignore/users/$userId.json");
        $this->assertError(404);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreDelete
     */
    public function testDirectorySyncControllerIgnoreDeleteNotExistAlreadyDeleted()
    {
        $this->logInAsAdmin();
        $user = UserFactory::make()->persist();
        $this->postJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=2");
        $this->deleteJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=2");
        $this->deleteJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=2");
        $this->assertError(404);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreDelete
     */
    public function testDirectorySyncControllerIgnoreDeleteErrorWrongModel()
    {
        $this->logInAsAdmin();
        $user = UserFactory::make()->persist();
        $this->deleteJson("/directorysync/ignore/biloute/{$user->get('id')}.json");
        $this->assertError(400);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreDelete
     */
    public function testDirectorySyncControllerIgnoreDeleteErrorNotExistModel()
    {
        $this->logInAsAdmin();
        $user = UserFactory::make()->persist();
        $this->deleteJson("/directorysync/ignore/groups/{$user->get('id')}.json");
        $this->assertError(404);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreDelete
     */
    public function testDirectorySyncControllerIgnoreDeleteErrorNotAuthenticated()
    {
        $user = UserFactory::make()->persist();
        $this->deleteJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=2");
        $this->assertAuthenticationError();
    }
}

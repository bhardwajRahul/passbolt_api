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
use Passbolt\DirectorySync\Test\Utility\DirectorySyncDeprecatedIntegrationTestCase;

class DirectoryIgnoreAddControllerTest extends DirectorySyncDeprecatedIntegrationTestCase
{
    public array $fixtures = [];

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreAdd
     */
    public function testDirectorySyncControllerIgnoreAddSuccess()
    {
        $this->loginAsAdmin();
        $user = UserFactory::make()->persist();
        $this->postJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=v2");
        $this->assertSuccess();
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreAdd
     */
    public function testDirectorySyncControllerIgnoreAddErrorNotValidId()
    {
        $this->loginAsAdmin();
        $userId = 'invalid-id';
        $this->postJson("/directorysync/ignore/users/$userId.json?api-version=v2");
        $this->assertError(400);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreAdd
     */
    public function testDirectorySyncControllerIgnoreAddErrorNotModel()
    {
        $this->loginAsAdmin();
        $userId = 'invalid-id';
        $this->postJson("/directorysync/ignore/comments/$userId.json?api-version=v2");
        $this->assertError(400);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreAdd
     */
    public function testDirectorySyncControllerIgnoreAddErrorRecordDoesNotExist()
    {
        $this->loginAsAdmin();
        $userId = UuidFactory::uuid();
        $this->postJson("/directorysync/ignore/users/$userId.json?api-version=v2");
        $this->assertError(404);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreAdd
     */
    public function testDirectorySyncControllerIgnoreAddErrorResourceAccessDenied()
    {
        // Check that the user cannot access the resource
        $this->logInAsUser();
        $user = UserFactory::make()->persist();
        $this->postJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=2");
        $this->assertError(403);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreAdd
     */
    public function testDirectorySyncControllerIgnoreAddErrorAlreadyMarkedAsIgnored()
    {
        $this->loginAsAdmin();
        $user = UserFactory::make()->persist();
        $this->postJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=2");
        $this->postJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=2");
        $this->assertError(400);
    }

    /**
     * @group DirectorySync
     * @group DirectorySyncController
     * @group DirectorySyncControllerIgnore
     * @group DirectorySyncControllerIgnoreAdd
     */
    public function testDirectorySyncControllerIgnoreAddErrorNotAuthenticated()
    {
        $user = UserFactory::make()->persist();
        $this->postJson("/directorysync/ignore/users/{$user->get('id')}.json?api-version=2");
        $this->assertAuthenticationError();
    }
}

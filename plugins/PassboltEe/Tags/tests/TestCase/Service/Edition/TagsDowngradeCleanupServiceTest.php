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

namespace Passbolt\Tags\Test\TestCase\Service\Edition;

use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\Tags\Service\Edition\TagsDowngradeCleanupService;
use Passbolt\Tags\Test\Factory\ResourcesTagFactory;
use Passbolt\Tags\Test\Factory\TagFactory;

/**
 * @covers \Passbolt\Tags\Service\Edition\TagsDowngradeCleanupService
 */
class TagsDowngradeCleanupServiceTest extends TestCase
{
    use TruncateDirtyTables;

    public function testTagsDowngradeCleanupService_Cleanup_TruncatesBothTagsTables(): void
    {
        TagFactory::make(3)->persist();
        ResourcesTagFactory::make(5)->persist();

        (new TagsDowngradeCleanupService())->cleanup();

        $this->assertSame(0, TagFactory::count());
        $this->assertSame(0, ResourcesTagFactory::count());
    }

    public function testTagsDowngradeCleanupService_Cleanup_IsIdempotentWhenTablesAreEmpty(): void
    {
        (new TagsDowngradeCleanupService())->cleanup();

        $this->assertSame(0, TagFactory::count());
        $this->assertSame(0, ResourcesTagFactory::count());
    }
}

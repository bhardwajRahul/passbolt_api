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
namespace Passbolt\Edition\Test\TestCase\Notification\Email;

use Cake\TestSuite\TestCase;
use Passbolt\Edition\Notification\Email\EditionDowngradeEmailRedactor;
use Passbolt\Edition\Notification\Email\EditionRedactorPool;
use Passbolt\Edition\Notification\Email\EditionUpgradeEmailRedactor;

/**
 * @covers \Passbolt\Edition\Notification\Email\EditionRedactorPool
 */
class EditionRedactorPoolTest extends TestCase
{
    public function testEditionRedactorPool_RegistersUpgradeAndDowngradeRedactors(): void
    {
        $redactors = (new EditionRedactorPool())->getSubscribedRedactors();

        $classes = array_map('get_class', $redactors);
        $this->assertContains(EditionDowngradeEmailRedactor::class, $classes);
        $this->assertContains(EditionUpgradeEmailRedactor::class, $classes);
    }
}

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
 * @since         6.0.0
 */
namespace App\Test\Lib\Cache\Engine;

use Cake\Cache\Engine\NullEngine;
use Cake\Cache\Exception\CacheWriteException;

/**
 * Cache engine used for testing purpose.
 */
class WriteFailCacheEngine extends NullEngine
{
    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        // Throwing exception everytime for testing
        throw new CacheWriteException('Unable to write cache');
    }
}

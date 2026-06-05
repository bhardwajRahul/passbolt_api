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
 * @since         4.10.0
 */

namespace App\Service\HealthcheckStatus;

use App\Utility\UuidFactory;
use Cake\Cache\Cache;
use Cake\Log\Log;
use Exception;

class DefaultHealthcheckStatusService implements HealthcheckStatusServiceInterface
{
    /**
     * @inheritDoc
     */
    public function check(): bool
    {
        return $this->isCacheServerUp();
    }

    /**
     * Check if cache is able to perform basic read, write, etc. operations.
     *
     * @return bool
     */
    private function isCacheServerUp(): bool
    {
        /**
         * Try to write and read the cache.
         * The reason for reading the cache is that some cache server's (i.e. redis) proxy connection might be available (hence no exception),
         * but cache isn't actually written because the actual server is down.
         */
        try {
            $key = 'healthcheck_status_' . UuidFactory::uuid();
            $status = Cache::write($key, 'ok');
            $status = $status && (Cache::read($key) === 'ok');
            $status = $status && Cache::delete($key);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return false;
        }

        return $status;
    }
}

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
namespace Passbolt\Edition\Service\Cleanup;

/**
 * Implemented by every PRO plugin that owns PRO-only data which must be wiped
 * when the instance is downgraded from PRO to CE.
 *
 * Implementations are invoked by EditionDowngradeCleanupRunner from inside the
 * transaction opened by EditionDowngradeService. Implementations:
 *
 *  - MUST NOT open their own transactions.
 *  - SHOULD be idempotent: calling cleanup() twice in a row must not fail and
 *    must not produce a different end state than calling it once.
 *  - Any exception thrown propagates and rolls back the entire downgrade.
 */
interface EditionDowngradeCleanupServiceInterface
{
    /**
     * Removes the PRO-only data owned by the implementing plugin.
     *
     * @return void
     */
    public function cleanup(): void;
}

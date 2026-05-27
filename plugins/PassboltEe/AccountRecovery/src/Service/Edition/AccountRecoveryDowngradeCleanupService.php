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
namespace Passbolt\AccountRecovery\Service\Edition;

use App\Model\Entity\AuthenticationToken;
use Cake\Datasource\ConnectionManager;
use Passbolt\AccountRecovery\Command\TruncateAccountRecoveryTablesCommand;
use Passbolt\Edition\Service\Cleanup\EditionDowngradeCleanupServiceInterface;

/**
 * Wipes AccountRecovery PRO data on edition downgrade.
 *
 * The AccountRecovery feature persists state in
 *  - 7 dedicated PRO tables
 *  - the shared authentication_tokens table (rows of type 'recover')
 */
final class AccountRecoveryDowngradeCleanupService implements EditionDowngradeCleanupServiceInterface
{
    /**
     * @inheritDoc
     */
    public function cleanup(): void
    {
        /** @var \Cake\Database\Connection $connection */
        $connection = ConnectionManager::get('default');

        // 1. Truncate every PRO-owned table.
        foreach (TruncateAccountRecoveryTablesCommand::ACCOUNT_RECOVERY_TABLES_TO_TRUNCATE as $table) {
            $connection->delete($table);
        }

        // 2. Drop the recover-type tokens from the shared authentication_tokens table.
        //    TYPE_RECOVER is also issued by base account-recovery (non-PRO) recover
        //    flows; in-flight standard recoveries at the moment of downgrade will
        //    therefore be invalidated. Affected users should request a new token.
        $connection->delete('authentication_tokens', ['type' => AuthenticationToken::TYPE_RECOVER]);
    }
}

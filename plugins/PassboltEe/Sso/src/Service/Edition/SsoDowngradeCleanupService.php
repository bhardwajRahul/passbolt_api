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
namespace Passbolt\Sso\Service\Edition;

use Cake\Datasource\ConnectionManager;
use Passbolt\Edition\Service\Cleanup\EditionDowngradeCleanupServiceInterface;
use Passbolt\Sso\Model\Table\SsoAuthenticationTokensTable;

/**
 * Wipes Sso PRO data on edition downgrade.
 */
final class SsoDowngradeCleanupService implements EditionDowngradeCleanupServiceInterface
{
    /**
     * Plugin-owned tables. The SSO feature stores its settings in its own
     * sso_settings table, not in organization_settings, so no shared-settings
     * cleanup is required.
     */
    private const TABLES = [
        'sso_states',
        'sso_keys',
        'sso_settings',
    ];

    /**
     * @inheritDoc
     */
    public function cleanup(): void
    {
        /** @var \Cake\Database\Connection $connection */
        $connection = ConnectionManager::get('default');

        foreach (self::TABLES as $table) {
            $connection->delete($table);
        }

        $connection->delete('authentication_tokens', [
            'type IN' => SsoAuthenticationTokensTable::SSO_ALLOWED_TYPES,
        ]);
    }
}

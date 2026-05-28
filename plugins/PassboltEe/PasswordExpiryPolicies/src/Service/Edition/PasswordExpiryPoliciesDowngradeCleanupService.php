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
namespace Passbolt\PasswordExpiryPolicies\Service\Edition;

use Cake\ORM\TableRegistry;
use Passbolt\Edition\Service\Cleanup\EditionDowngradeCleanupServiceInterface;
use Passbolt\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto;

/**
 * Wipes PasswordExpiryPolicies PRO data on edition downgrade.
 *
 * The PRO plugin owns no table of its own. It enriches the value blob of the
 * CE PasswordExpiry plugin's `passwordExpiry` organization_settings row with
 * Pro-only sub-fields. Cleanup reads the row through the CE DTO and writes it
 * back: any field the CE DTO does not know about is dropped.
 */
final class PasswordExpiryPoliciesDowngradeCleanupService implements EditionDowngradeCleanupServiceInterface
{
    /**
     * @inheritDoc
     */
    public function cleanup(): void
    {
        $settingsTable = TableRegistry::getTableLocator()
            ->get('Passbolt/PasswordExpiry.PasswordExpirySettings');

        /** @var \Passbolt\PasswordExpiry\Model\Entity\PasswordExpirySetting|null $setting */
        $setting = $settingsTable->find()->first();
        if ($setting === null) {
            return;
        }

        $dto = PasswordExpirySettingsDto::createFromArray((array)$setting->get('value'));
        $setting->set('value', $dto->getValue());
        $settingsTable->saveOrFail($setting, ['validate' => false]);
    }
}

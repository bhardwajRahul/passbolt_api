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
namespace Passbolt\MfaPolicies\Service\Edition;

use App\Utility\UuidFactory;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\Edition\Service\Cleanup\EditionDowngradeCleanupServiceInterface;
use Passbolt\MfaPolicies\Model\Entity\MfaPoliciesSetting;

/**
 * Wipes MfaPolicies PRO data on edition downgrade.
 */
final class MfaPoliciesDowngradeCleanupService implements EditionDowngradeCleanupServiceInterface
{
    use LocatorAwareTrait;

    /**
     * @inheritDoc
     */
    public function cleanup(): void
    {
        $this->fetchTable('OrganizationSettings')->deleteAll([
            'property_id' => UuidFactory::uuid(MfaPoliciesSetting::PROPERTY_NAME),
        ]);
    }
}

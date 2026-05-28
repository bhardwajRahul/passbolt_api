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
namespace Passbolt\Subscription\Service\Subscriptions;

use App\Utility\UserAccessControl;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Removes the persisted subscription key from `organization_settings`.
 *
 * Intended to be called from EditionDowngradeService as the first step of
 * the in-product downgrade flow. Idempotent: succeeds without error when
 * no subscription row exists.
 */
class SubscriptionKeyDeleteService
{
    use LocatorAwareTrait;

    /**
     * @param \App\Utility\UserAccessControl $uac User access control.
     * @return void
     * @throws \Cake\Http\Exception\ForbiddenException When the UAC is not admin.
     */
    public function delete(UserAccessControl $uac): void
    {
        if (!$uac->isAdmin()) {
            throw new ForbiddenException(__('Only administrators can delete the subscription.'));
        }

        /** @var \Passbolt\Subscription\Model\Table\SubscriptionsTable $Subscriptions */
        $Subscriptions = $this->fetchTable('Passbolt/Subscription.Subscriptions');
        $Subscriptions->deleteAll([
            'property_id' => $Subscriptions->getPropertyId(),
        ]);
    }
}

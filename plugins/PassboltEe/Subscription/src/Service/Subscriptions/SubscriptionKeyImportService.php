<?php
declare(strict_types=1);

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SARL (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SARL (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         3.1.0
 */
namespace Passbolt\Subscription\Service\Subscriptions;

use App\Utility\UserAccessControl;
use Passbolt\Subscription\Error\Exception\Subscriptions\SubscriptionException;
use Passbolt\Subscription\Model\Dto\SubscriptionKeyDto;

/**
 * Persists a subscription key supplied via file or text.
 */
class SubscriptionKeyImportService
{
    /**
     * @param string $fileName Path to a file containing the subscription key.
     * @param \App\Utility\UserAccessControl $uac UAC object.
     * @return \Passbolt\Subscription\Model\Dto\SubscriptionKeyDto
     * @throws \Passbolt\Subscription\Error\Exception\Subscriptions\SubscriptionException If the file or the contained subscription is not valid.
     */
    public function importFromFile(string $fileName, UserAccessControl $uac): SubscriptionKeyDto
    {
        if (!file_exists($fileName)) {
            throw new SubscriptionException(__('The file {0} could not be found.', $fileName));
        }
        if (!is_readable($fileName)) {
            throw new SubscriptionException(__('The file {0} could not be read.', $fileName));
        }
        $subscription = file_get_contents($fileName);
        if (!$subscription) {
            throw new SubscriptionException(__('The file {0} could not be read.', $fileName));
        }

        return $this->import($subscription, $uac);
    }

    /**
     * @param string|null $subscription Raw subscription key payload (Base64).
     * @param \App\Utility\UserAccessControl $uac UAC object.
     * @return \Passbolt\Subscription\Model\Dto\SubscriptionKeyDto
     * @throws \Passbolt\Subscription\Error\Exception\Subscriptions\SubscriptionException If the subscription is not valid.
     */
    public function import(?string $subscription, UserAccessControl $uac): SubscriptionKeyDto
    {
        return (new SubscriptionKeySaveService())->save($subscription, $uac);
    }
}

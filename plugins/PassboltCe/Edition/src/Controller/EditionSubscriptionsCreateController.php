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
namespace Passbolt\Edition\Controller;

use App\Controller\AppController;
use App\Error\Exception\PaymentRequiredException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ConflictException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\Edition\Service\EditionGetService;
use Passbolt\Edition\Service\EditionUpgradeService;
use Passbolt\Subscription\Error\Exception\Subscriptions\SubscriptionException;
use Passbolt\Subscription\Error\Exception\Subscriptions\SubscriptionSignatureException;

/**
 * The in-product upgrade entry point.
 */
class EditionSubscriptionsCreateController extends AppController
{
    use LocatorAwareTrait;

    /**
     * @return void
     */
    public function create(): void
    {
        $this->User->assertIsAdmin();

        $keyString = $this->getRequest()->getData('data');
        if (!is_string($keyString) || trim($keyString) === '') {
            throw new BadRequestException(__('Subscription key data is required.'));
        }

        $this->assertNotAlreadyPro();

        try {
            $keyDto = (new EditionUpgradeService())->upgrade($keyString, $this->User->getAccessControl());
        } catch (SubscriptionSignatureException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (SubscriptionException $e) {
            throw new PaymentRequiredException($e->getMessage(), $e->getErrors());
        }

        $this->success(__('The subscription was created.'), $keyDto->toArray());
    }

    /**
     * Rejects with HTTP 409 if the instance is already on PRO or already has a
     * persisted subscription row.
     *
     * @return void
     * @throws \Cake\Http\Exception\ConflictException
     */
    private function assertNotAlreadyPro(): void
    {
        if ((new EditionGetService())->get()->isPro()) {
            throw new ConflictException(__('The instance is already on PRO.'));
        }

        $subscriptions = $this->fetchTable('Passbolt/Subscription.Subscriptions');
        if ($subscriptions->find()->count() > 0) {
            throw new ConflictException(__('A subscription key is already present.'));
        }
    }
}

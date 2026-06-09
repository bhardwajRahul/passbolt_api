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
 * @since         3.1.0
 */
namespace Passbolt\Subscription\Controller\Subscriptions;

use App\Controller\AppController;
use App\Error\Exception\PaymentRequiredException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Passbolt\Subscription\Error\Exception\Subscriptions\SubscriptionException;
use Passbolt\Subscription\Error\Exception\Subscriptions\SubscriptionSignatureException;
use Passbolt\Subscription\Service\Subscriptions\SubscriptionKeySaveService;

/**
 * Class SubscriptionsUpdateController
 */
class SubscriptionsUpdateController extends AppController
{
    /**
     * @return void
     */
    public function update(): void
    {
        if (!$this->User->isAdmin()) {
            throw new ForbiddenException(__('You are not allowed to access this location.'));
        }

        $keyString = $this->getRequest()->getData('data');
        if (!is_string($keyString) || trim($keyString) === '') {
            throw new BadRequestException(__('Subscription key data is required.'));
        }

        try {
            $keyDto = (new SubscriptionKeySaveService())->save($keyString, $this->User->getAccessControl());
        } catch (SubscriptionSignatureException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (SubscriptionException $e) {
            throw new PaymentRequiredException($e->getMessage(), $e->getErrors());
        }

        // POST and PUT both land here for backwards compatibility.
        // Preserve the historical success messages of the now-deleted
        // SubscriptionsCreateController (POST) and this controller (PUT) so the
        // legacy SubscriptionsCreateControllerTest keeps passing unchanged.
        $message = $this->getRequest()->is('post')
            ? __('The subscription was created.')
            : __('The subscription was updated.');

        $this->success($message, $keyDto->toArray());
    }
}

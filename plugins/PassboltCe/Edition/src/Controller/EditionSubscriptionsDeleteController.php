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
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Passbolt\Edition\Service\EditionDowngradeService;

/**
 * HTTP entry point for the in-product downgrade. Delegates to
 * EditionDowngradeService, which owns the transactional sequence.
 *
 * Endpoint: DELETE /edition/subscription/key.json
 */
class EditionSubscriptionsDeleteController extends AppController
{
    /**
     * @return void
     * @throws \Cake\Http\Exception\ForbiddenException When the caller is not admin.
     * @throws \Cake\Http\Exception\NotFoundException When no subscription row exists.
     * @throws \Cake\Http\Exception\ConflictException When the instance is already on CE.
     */
    public function delete(): void
    {
        $uac = $this->User->getAccessControl();
        if (!$uac->isAdmin()) {
            throw new ForbiddenException(__('You are not allowed to access this location.'));
        }

        // 404 if no subscription row. The downgrade service is idempotent on
        // the delete step, so this REST-level check has to live here.
        if (!$this->fetchTable('Passbolt/Subscription.Subscriptions')->exists([])) {
            throw new NotFoundException(__('The subscription key does not exist.'));
        }

        // Throws ConflictException(409) if already on CE.
        (new EditionDowngradeService())->downgrade($uac);

        $this->success(__('The instance was downgraded to CE.'));
    }
}

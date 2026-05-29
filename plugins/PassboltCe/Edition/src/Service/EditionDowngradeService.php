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
namespace Passbolt\Edition\Service;

use App\Utility\UserAccessControl;
//use Cake\Cache\Cache;
use Cake\Event\EventDispatcherTrait;
use Cake\Http\Exception\ConflictException;
//use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\Edition\Service\Cleanup\EditionDowngradeCleanupRunner;
use Passbolt\Subscription\Service\Subscriptions\SubscriptionKeyDeleteService;

/**
 * Orchestrates the in-product PRO → CE downgrade.
 *
 * Steps 1-3 (delete subscription key, run per-plugin cleanups, flip edition
 * flag to CE) run inside a single transaction — any exception rolls back the
 * entire downgrade. Step 4 (event dispatch + cache marker) runs after the
 * commit; listener failures must not be able to roll the downgrade back.
 *
 * Called by EditionSubscriptionsDeleteController and
 * EditionDowngradeCommand.
 */
class EditionDowngradeService
{
    use EventDispatcherTrait;
    use LocatorAwareTrait;

    /**
     * Event name AND cache key the post-commit marker is written under.
     *
     * @var string
     */
    public const EVENT_EDITION_DOWNGRADED = 'Edition.downgraded';

    /**
     * @param \App\Utility\UserAccessControl $uac User access control.
     * @return void
     * @throws \Cake\Http\Exception\ConflictException When the instance is already on CE.
     * @throws \Cake\Http\Exception\ForbiddenException When the UAC is not admin.
     */
    public function downgrade(UserAccessControl $uac): void
    {
        $uac->assertIsAdmin();

        // Downgrade only makes sense from PRO. If the instance is already CE
        // there is nothing to do; surface as 409 per spec.
        if (!(new EditionGetService())->get()->isPro()) {
            throw new ConflictException(__('The instance is already on CE.'));
        }

        $this->fetchTable('Passbolt/Edition.EditionOrganization')
            ->getConnection()
            ->transactional(function () use ($uac): void {
                (new SubscriptionKeyDeleteService())->delete($uac);
                (new EditionDowngradeCleanupRunner())->run();
                (new EditionSetService())->setToCe($uac);
            });

        // Post-commit: notify + record marker. Failures here must not roll
        // the downgrade back, so they live outside the transactional block.
        // TODO
//        Cache::write(self::EVENT_EDITION_DOWNGRADED, DateTime::now());
        $this->dispatchEvent(self::EVENT_EDITION_DOWNGRADED, compact('uac'), $this);
    }
}

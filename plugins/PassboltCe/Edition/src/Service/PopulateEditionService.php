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

use App\Model\Entity\Role;
use App\Utility\UserAccessControl;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\Edition\Model\Dto\EditionDto;
use Passbolt\Subscription\Service\Subscriptions\SubscriptionKeyGetService;
use Throwable;

class PopulateEditionService
{
    use LocatorAwareTrait;

    /**
     * Persist the edition in the database if not already present.
     *
     * @return void
     */
    public function populate(): void
    {
        /** @var \Passbolt\Edition\Model\Table\EditionOrganizationTable $editionOrganizationTable */
        $editionOrganizationTable = $this->fetchTable('Passbolt/Edition.EditionOrganization');

        // Do not overwrite an existing value.
        if ($editionOrganizationTable->find()->first() instanceof EntityInterface) {
            return;
        }

        $entity = $editionOrganizationTable->newEntity([
            'value' => $this->detectEdition(),
            // System generated row hence 0s.
            'created_by' => '00000000-0000-0000-0000-000000000000',
            'modified_by' => '00000000-0000-0000-0000-000000000000',
        ]);
        $editionOrganizationTable->saveOrFail($entity);
    }

    /**
     * PRO if a valid subscription key is present, CE otherwise.
     *
     * @return string
     */
    private function detectEdition(): string
    {
        try {
            (new SubscriptionKeyGetService())->get(new UserAccessControl(Role::ADMIN));

            return EditionDto::EDITION_PRO;
        } catch (Throwable $e) {
            return EditionDto::EDITION_CE;
        }
    }
}

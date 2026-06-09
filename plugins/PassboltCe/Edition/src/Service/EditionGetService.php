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

use App\Model\Entity\OrganizationSetting;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\Edition\Model\Dto\EditionDto;

/**
 * Reads the runtime edition flag from `organization_settings` and returns it as an EditionDto.
 */
final class EditionGetService
{
    use LocatorAwareTrait;

    /**
     * Returns the current edition.
     *
     * @return \Passbolt\Edition\Model\Dto\EditionDto
     */
    public function get(): EditionDto
    {
        /** @var \Passbolt\Edition\Model\Table\EditionOrganizationTable $editionOrganizationTable */
        $editionOrganizationTable = $this->fetchTable('Passbolt/Edition.EditionOrganization');

        $row = $editionOrganizationTable->find()->first();

        return EditionDto::fromOrgSettingRow($row instanceof OrganizationSetting ? $row : null);
    }
}

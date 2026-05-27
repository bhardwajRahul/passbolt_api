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
namespace Passbolt\Edition\Test\Factory;

use App\Test\Factory\OrganizationSettingFactory;
use App\Utility\UuidFactory;
use Faker\Generator;
use Passbolt\Edition\Model\Dto\EditionDto;
use Passbolt\Edition\Model\Table\EditionOrganizationTable;

/**
 * @method \App\Model\Entity\OrganizationSetting|\App\Model\Entity\OrganizationSetting[] persist()
 * @method \App\Model\Entity\OrganizationSetting getEntity()
 * @method \App\Model\Entity\OrganizationSetting[] getEntities()
 * @see \Passbolt\Edition\Model\Table\EditionOrganizationTable
 */
class EditionOrganizationSettingFactory extends OrganizationSettingFactory
{
    /**
     * @inheritDoc
     */
    protected function getRootTableRegistryName(): string
    {
        return EditionOrganizationTable::class;
    }

    /**
     * @inheritDoc
     */
    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker): array {
            return [
                'property' => EditionOrganizationTable::PROPERTY_NAME,
                'property_id' => UuidFactory::uuid(EditionOrganizationTable::PROPERTY_NAME),
                'value' => EditionDto::EDITION_CE,
                'created_by' => UuidFactory::uuid(),
                'modified_by' => UuidFactory::uuid(),
            ];
        });
    }
}

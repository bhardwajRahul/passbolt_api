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
namespace Passbolt\Edition\Model\Table;

use App\Model\Table\OrganizationSettingsTable;
use App\Utility\UuidFactory;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;

/**
 * EditionOrganization Model
 *
 * A scoped view over `organization_settings` exposing only the single row that
 * carries the runtime edition flag (`property = 'edition'`, `value ∈ {'ce', 'pro'}`).
 *
 * Every read is implicitly filtered on the `edition` property id via
 * `beforeFind()`. Every write is implicitly stamped with the same property and
 * property id via `beforeMarshal()`. Consumers of this table never need to
 * supply the property name.
 *
 * @method \App\Model\Entity\OrganizationSetting newEmptyEntity()
 * @method \App\Model\Entity\OrganizationSetting newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\OrganizationSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrganizationSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EditionOrganizationTable extends OrganizationSettingsTable
{
    /**
     * The property name that identifies the edition row inside
     * `organization_settings`.
     *
     * @var string
     */
    public const PROPERTY_NAME = 'edition';

    /**
     * Scopes every query on this table to the single `edition` row.
     *
     * @param \Cake\Event\EventInterface $event Model.beforeFind event.
     * @param \Cake\ORM\Query\SelectQuery $query Query under construction.
     * @return void
     */
    public function beforeFind(EventInterface $event, SelectQuery $query): void
    {
        $query->where([
            $this->aliasField('property_id') => $this->getPropertyId(),
        ]);
    }

    /**
     * Forces `property` and `property_id` on every marshalled payload, so
     * callers cannot accidentally write to a different organization setting
     * through this table.
     *
     * @param \Cake\Event\EventInterface $event Model.beforeMarshal event.
     * @param \ArrayObject<string, mixed> $data Incoming data.
     * @param \ArrayObject<string, mixed> $options Marshalling options.
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        $data['property'] = $this->getProperty();
        $data['property_id'] = $this->getPropertyId();
    }

    /**
     * Returns the property name owned by this table.
     *
     * @return string
     */
    public function getProperty(): string
    {
        return self::PROPERTY_NAME;
    }

    /**
     * Returns the deterministic UUID derived from the property name. Mirrors
     * the convention used by every other scoped settings table in the project
     * (e.g. `PasswordExpirySettingsTable::getPropertyId()`).
     *
     * @return string
     */
    public function getPropertyId(): string
    {
        return UuidFactory::uuid($this->getProperty());
    }

    /**
     * Locks `_getSettingPropertyId()` to the `edition` property regardless of
     * the argument passed in. This guarantees that the inherited
     * `createOrUpdateSetting()` and `deleteSetting()` methods can never touch
     * a different organization setting through this table.
     *
     * @param string $property Ignored — kept for signature parity with parent.
     * @return string
     */
    protected function _getSettingPropertyId(string $property): string
    {
        return $this->getPropertyId();
    }
}

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

namespace Passbolt\Scim\Test\TestCase\Model\Dto;

use App\Utility\UuidFactory;
use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use Passbolt\Scim\Model\Dto\ScimSettingsDto;

class ScimSettingsDtoTest extends TestCase
{
    public function testScimSettingsDto_constructorFull()
    {
        $settingId = UuidFactory::uuid();
        $data = [
            'id' => UuidFactory::uuid(),
            'setting_id' => $settingId,
            'scim_user_id' => UuidFactory::uuid(),
            'base_api_endpoint' => '/scim/v2/' . $settingId,
            'expired' => Date::now()->modify('+' . rand(1, 365) . ' days')->format('Y-m-d'),
            'created' => DateTime::now(),
            'created_by' => UuidFactory::uuid(),
            'modified' => DateTime::now(),
            'modified_by' => UuidFactory::uuid(),
        ];

        $dto = new ScimSettingsDto(
            $data['id'],
            $data['setting_id'],
            $data['scim_user_id'],
            $data['base_api_endpoint'],
            $data['expired'],
            $data['created'],
            $data['created_by'],
            $data['modified'],
            $data['modified_by'],
        );

        $this->assertEquals($data['id'], $dto->id);
        $this->assertEquals($data['setting_id'], $dto->settingId);
        $this->assertEquals($data['scim_user_id'], $dto->scimUserId);
        $this->assertEquals($data['base_api_endpoint'], $dto->baseApiEndpoint);
        $this->assertEquals($data['expired'], $dto->expired);
        $this->assertEquals($data['created'], $dto->created);
        $this->assertEquals($data['created_by'], $dto->createdBy);
        $this->assertEquals($data['modified'], $dto->modified);
        $this->assertEquals($data['modified_by'], $dto->modifiedBy);
    }

    public function testScimSettingsDto_constructorEmpty()
    {
        $dto = new ScimSettingsDto(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        );

        foreach (get_object_vars($dto) as $property => $value) {
            $this->assertNull($value, "Property '$property' should be null");
        }
    }
}

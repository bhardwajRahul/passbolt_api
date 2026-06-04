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
 * @since         5.5.0
 */

namespace Passbolt\Scim\Test\TestCase\Service;

use App\Service\OpenPGP\OpenPGPCommonServerOperationsTrait;
use App\Test\Lib\AppTestCase;
use App\Utility\OpenPGP\OpenPGPBackendFactory;
use Passbolt\Scim\Model\Dto\ScimSettingsDto;
use Passbolt\Scim\Service\ScimGetSettingsService;
use Passbolt\Scim\Test\Factory\ScimSettingFactory;

/**
 * ScimGetSettingsServiceTest class
 */
class ScimGetSettingsServiceTest extends AppTestCase
{
    use OpenPGPCommonServerOperationsTrait;

    /**
     * @var \Passbolt\Scim\Service\ScimGetSettingsService
     */
    protected ScimGetSettingsService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new ScimGetSettingsService();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->service);
    }

    public function testGetSettingsDecryptedValue()
    {
        /** @var \Passbolt\Scim\Model\Entity\ScimSetting $scimSettings */
        $scimSettings = ScimSettingFactory::make()->default()->persist();
        $gpg = OpenPGPBackendFactory::get();
        $gpg = $this->setDecryptKeyWithServerKey($gpg);
        $data = json_decode($gpg->decrypt($scimSettings->value), associative: true);
        $value = $this->service->getSettingsDecryptedValue();
        $this->assertSame([
            'setting_id',
            'scim_user_id',
            'secret_token',
            'expired',
        ], array_keys($value));
        $this->assertSame($data, $value);
    }

    public function testGetSettings()
    {
        /** @var \Passbolt\Scim\Model\Entity\ScimSetting $scimSettings */
        $scimSettings = ScimSettingFactory::make()->default()->persist();
        $gpg = OpenPGPBackendFactory::get();
        $gpg = $this->setDecryptKeyWithServerKey($gpg);
        $data = json_decode($gpg->decrypt($scimSettings->value), associative: true);
        $settings = $this->service->getSettings();
        $this->assertInstanceOf(ScimSettingsDto::class, $settings);
        $settingsFilteredArray = $settings->toArray();
        $this->assertSame($data['setting_id'], $settingsFilteredArray['setting_id']);
        $this->assertSame($data['scim_user_id'], $settingsFilteredArray['scim_user_id']);
        $this->assertStringContainsString('/scim/v2/' . $data['setting_id'], $settingsFilteredArray['base_api_endpoint']);
    }
}

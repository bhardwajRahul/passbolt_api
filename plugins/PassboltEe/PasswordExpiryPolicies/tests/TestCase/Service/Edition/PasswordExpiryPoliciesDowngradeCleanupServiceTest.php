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

namespace Passbolt\PasswordExpiryPolicies\Test\TestCase\Service\Edition;

use App\Test\Lib\AppTestCase;
use App\Test\Lib\Utility\ExtendedUserAccessControlTestTrait;
use Cake\ORM\TableRegistry;
use Passbolt\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto;
use Passbolt\PasswordExpiry\Test\Factory\PasswordExpirySettingFactory;
use Passbolt\PasswordExpiryPolicies\Service\Edition\PasswordExpiryPoliciesDowngradeCleanupService;
use Passbolt\PasswordExpiryPolicies\Service\Settings\PasswordExpiryPoliciesSetSettingsService;

/**
 * @covers \Passbolt\PasswordExpiryPolicies\Service\Edition\PasswordExpiryPoliciesDowngradeCleanupService
 */
class PasswordExpiryPoliciesDowngradeCleanupServiceTest extends AppTestCase
{
    use ExtendedUserAccessControlTestTrait;

    private PasswordExpiryPoliciesSetSettingsService $setSettingsService;

    public function setUp(): void
    {
        parent::setUp();
        $this->setSettingsService = new PasswordExpiryPoliciesSetSettingsService();
    }

    public function tearDown(): void
    {
        unset($this->setSettingsService);
        parent::tearDown();
    }

    public function testPasswordExpiryPoliciesDowngradeCleanupService_Cleanup_StripsProSubFieldsButPreservesTheRow(): void
    {
        $uac = $this->mockExtendedAdminAccessControl();
        $this->setSettingsService->createOrUpdate($uac, [
            PasswordExpirySettingsDto::AUTOMATIC_EXPIRY => true,
            PasswordExpirySettingsDto::AUTOMATIC_UPDATE => true,
            PasswordExpirySettingsDto::POLICY_OVERRIDE => true,
            PasswordExpirySettingsDto::DEFAULT_EXPIRY_PERIOD => 30,
        ]);
        // The PRO form does not currently expose EXPIRY_NOTIFICATION, so inject
        // it directly to simulate a row left behind by an older PRO version.
        $settingsTable = TableRegistry::getTableLocator()->get('Passbolt/PasswordExpiry.PasswordExpirySettings');
        $setting = $settingsTable->find()->firstOrFail();
        $setting->set('value', $setting->get('value') + [
            PasswordExpirySettingsDto::EXPIRY_NOTIFICATION => 7,
        ]);
        $settingsTable->saveOrFail($setting, ['validate' => false]);

        (new PasswordExpiryPoliciesDowngradeCleanupService())->cleanup();

        $this->assertSame(1, PasswordExpirySettingFactory::count());
        $value = (array)PasswordExpirySettingFactory::firstOrFail()->get('value');
        $this->assertArrayNotHasKey(PasswordExpirySettingsDto::EXPIRY_NOTIFICATION, $value);
        $this->assertSame(true, $value[PasswordExpirySettingsDto::AUTOMATIC_EXPIRY]);
        $this->assertSame(true, $value[PasswordExpirySettingsDto::AUTOMATIC_UPDATE]);
        $this->assertSame(true, $value[PasswordExpirySettingsDto::POLICY_OVERRIDE]);
        $this->assertSame(30, $value[PasswordExpirySettingsDto::DEFAULT_EXPIRY_PERIOD]);
    }

    public function testPasswordExpiryPoliciesDowngradeCleanupService_Cleanup_IsNoOpWhenNoSettingExists(): void
    {
        (new PasswordExpiryPoliciesDowngradeCleanupService())->cleanup();

        $this->assertSame(0, PasswordExpirySettingFactory::count());
    }

    public function testPasswordExpiryPoliciesDowngradeCleanupService_Cleanup_PreservesCeOnlyValue(): void
    {
        $uac = $this->mockExtendedAdminAccessControl();
        $this->setSettingsService->createOrUpdate($uac, [
            PasswordExpirySettingsDto::AUTOMATIC_EXPIRY => true,
            PasswordExpirySettingsDto::AUTOMATIC_UPDATE => true,
            PasswordExpirySettingsDto::POLICY_OVERRIDE => false,
            PasswordExpirySettingsDto::DEFAULT_EXPIRY_PERIOD => null,
        ]);

        (new PasswordExpiryPoliciesDowngradeCleanupService())->cleanup();

        $this->assertSame(1, PasswordExpirySettingFactory::count());
        $value = (array)PasswordExpirySettingFactory::firstOrFail()->get('value');
        $this->assertSame(true, $value[PasswordExpirySettingsDto::AUTOMATIC_EXPIRY]);
        $this->assertSame(true, $value[PasswordExpirySettingsDto::AUTOMATIC_UPDATE]);
        $this->assertSame(false, $value[PasswordExpirySettingsDto::POLICY_OVERRIDE]);
        $this->assertNull($value[PasswordExpirySettingsDto::DEFAULT_EXPIRY_PERIOD]);
    }
}

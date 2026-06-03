<?php
declare(strict_types=1);

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * SubscriptionKeyd under GNU Affero General Public SubscriptionKey version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL SubscriptionKey
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.5.0
 */
namespace Passbolt\WebInstaller\Test\TestCase\Controller;

use Cake\Core\Configure;
use Passbolt\Subscription\SubscriptionPlugin;
use Passbolt\WebInstaller\Test\Lib\WebInstallerIntegrationTestCase;
use RuntimeException;

class SubscriptionKeyControllerTest extends WebInstallerIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->mockPassboltIsNotconfigured();
        $this->initWebInstallerSession();
    }

    protected function mockSubscriptionKeyIssuerKey()
    {
        Configure::load('Passbolt/Subscription.config', 'default', true);
        $licenseDevPublicKey = PLUGINS . DS . 'PassboltEe' . DS . 'Subscription' . DS . 'tests' . DS . 'Fixture' . DS . 'gpg' . DS . 'subscription_dev_public.key';
        Configure::write('passbolt.plugins.edition.subscriptionKey.public', $licenseDevPublicKey);
    }

    protected function checkPluginSubscriptionExists(): bool
    {
        return file_exists(PLUGINS . DS . 'PassboltEe' . DS . 'Subscription');
    }

    public function testWebInstallerSubscriptionKeyViewSuccess()
    {
        $this->get('/install/subscription');
        $data = $this->_getBodyAsString();
        $this->assertResponseOk();
        $this->assertStringContainsString('Passbolt Pro activation.', $data);
    }

    public function testWebInstallerSubscriptionKeyPostSuccess()
    {
        if ($this->checkPluginSubscriptionExists()) {
            $this->mockSubscriptionKeyIssuerKey();
            $postData = [
                'subscription_key' => file_get_contents(PLUGINS . DS . 'PassboltEe' . DS . 'Subscription' . DS . 'tests' . DS . 'Fixture' . DS . 'subscription' . DS . 'subscription_dev'),
            ];
            $this->post('/install/subscription', $postData);
            $this->assertResponseCode(302);
            $this->assertRedirectContains('/install/installation');
            $this->assertSession($postData, 'webinstaller.subscription');
        }
        $this->assertTrue(true);
    }

    public function testWebInstallerSubscriptionKeyPostError_InvalidData()
    {
        if ($this->checkPluginSubscriptionExists()) {
            $this->mockSubscriptionKeyIssuerKey();
            $postData = [
                'subscription_key' => 'invalid-format',
            ];
            $this->post('/install/subscription', $postData);
            $data = $this->_getBodyAsString();
            $this->assertResponseOk();
            $this->assertStringContainsString('The subscription format is not valid', $data);
        }
        $this->assertTrue(true);
    }

    public function testWebInstallerSubscriptionKey_PostSkip_ForwardsWithNullKey()
    {
        $this->post('/install/subscription', ['skip' => '1']);

        $this->assertResponseCode(302);
        $this->assertRedirectContains('/install/installation');
        $this->assertSession(['subscription_key' => null], 'webinstaller.subscription');
    }

    public function testWebInstallerSubscriptionKey_GetRendersWhenSubscriptionPluginDisabled()
    {
        // The page is shown on every edition (WP 7.4) so a CE operator can
        // still enter a key here to upgrade in one go, or skip.
        $this->disableFeaturePlugin(SubscriptionPlugin::class);

        $this->get('/install/subscription');

        $this->assertResponseOk();
        $this->assertStringContainsString('Passbolt Pro activation.', $this->_getBodyAsString());
    }

    public function testWebInstallerSubscriptionKey_PostSkipForwardsWhenSubscriptionPluginDisabled()
    {
        $this->disableFeaturePlugin(SubscriptionPlugin::class);

        $this->post('/install/subscription', ['skip' => '1']);

        $this->assertResponseCode(302);
        $this->assertRedirectContains('/install/installation');
        $this->assertSession(['subscription_key' => null], 'webinstaller.subscription');
    }

    public function testWebInstallerSubscriptionKeyPostError_SubscriptionKeyExpired()
    {
        if ($this->checkPluginSubscriptionExists()) {
            $this->mockSubscriptionKeyIssuerKey();
            $expiredSubscriptionFile = PLUGINS . DS . 'PassboltEe' . DS . 'Subscription' . DS . 'tests' . DS . 'Fixture' . DS . 'subscription' . DS . 'subscription_expired';
            if (!is_file($expiredSubscriptionFile)) {
                throw new RuntimeException('Cannot find expired subscription file ' . $expiredSubscriptionFile);
            }
            $postData = [
                'subscription_key' => file_get_contents($expiredSubscriptionFile),
            ];
            $this->post('/install/subscription', $postData);
            $data = $this->_getBodyAsString();
            $this->assertResponseOk();
            $this->assertStringContainsString('The subscription format is not valid', $data);
            $this->assertStringContainsString('The subscription is expired', $data);
        }
        $this->assertTrue(true);
    }
}

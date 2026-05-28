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
 * @since         5.12.0
 */
namespace Passbolt\Edition\Test\TestCase\Service;

use App\BaseSolutionBootstrapper;
use App\Test\Lib\AppTestCaseV5;
use Cake\Core\Configure;
use Passbolt\Edition\Model\Dto\EditionDto;
use Passbolt\Edition\Service\EditionManager;
use Passbolt\Edition\Test\Factory\EditionOrganizationSettingFactory;
use Passbolt\Ee\EeSolutionBootstrapper;

/**
 * @covers \Passbolt\Edition\Service\EditionManager
 */
class EditionManagerTest extends AppTestCaseV5
{
    private EditionManager $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new EditionManager();
        // Clear any value the test bootstrap may have written to avoid any side effects
        Configure::delete('passbolt.edition');
    }

    public function tearDown(): void
    {
        unset($this->sut);
        parent::tearDown();
    }

    public function testEditionManager_Boot_CeEdition(): void
    {
        EditionOrganizationSettingFactory::make()
            ->setField('value', EditionDto::EDITION_CE)
            ->persist();

        $this->sut->boot();

        $this->assertSame(EditionDto::EDITION_CE, $this->sut->getEdition());
        $this->assertFalse($this->sut->isPro());
        $this->assertSame(BaseSolutionBootstrapper::class, $this->sut->getSolutionBootstrapperClass());
        $this->assertSame(EditionDto::EDITION_CE, Configure::read('passbolt.edition'));
    }

    public function testEditionManager_Boot_ProEdition(): void
    {
        EditionOrganizationSettingFactory::make()
            ->setField('value', EditionDto::EDITION_PRO)
            ->persist();

        $this->sut->boot();

        $this->assertSame(EditionDto::EDITION_PRO, $this->sut->getEdition());
        $this->assertTrue($this->sut->isPro());
        $this->assertSame(EeSolutionBootstrapper::class, $this->sut->getSolutionBootstrapperClass());
        $this->assertSame(EditionDto::EDITION_PRO, Configure::read('passbolt.edition'));
    }

    public function testEditionManager_Boot_NoRowDefaultsToCe(): void
    {
        $this->sut->boot();

        $this->assertSame(EditionDto::EDITION_CE, $this->sut->getEdition());
        $this->assertSame(EditionDto::EDITION_CE, Configure::read('passbolt.edition'));
    }

    public function testEditionManager_Boot_DbValueOverridesPreExistingConfigure(): void
    {
        Configure::write('passbolt.edition', EditionDto::EDITION_CE);
        EditionOrganizationSettingFactory::make()
            ->setField('value', EditionDto::EDITION_PRO)
            ->persist();

        $this->sut->boot();

        $this->assertSame(EditionDto::EDITION_PRO, $this->sut->getEdition());
        $this->assertSame(EditionDto::EDITION_PRO, Configure::read('passbolt.edition'));
    }

    public function testEditionManager_Boot_CeEdition_ProPluginsAreDisabled(): void
    {
        EditionOrganizationSettingFactory::make()
            ->setField('value', EditionDto::EDITION_CE)
            ->persist();
        // Simulate pro.php having been loaded already (config/bootstrap.php does this now)
        Configure::write('passbolt.plugins.sso.enabled', true);
        Configure::write('passbolt.plugins.accountRecovery.enabled', true);

        $this->sut->boot();

        $this->assertFalse(Configure::read('passbolt.plugins.sso.enabled'));
        $this->assertFalse(Configure::read('passbolt.plugins.accountRecovery.enabled'));
    }

    public function testEditionManager_Boot_ProEdition_ProPluginsEnabled(): void
    {
        EditionOrganizationSettingFactory::make()
            ->setField('value', EditionDto::EDITION_PRO)
            ->persist();
        Configure::write('passbolt.plugins.sso.enabled', true);
        Configure::write('passbolt.plugins.accountRecovery.enabled', true);

        $this->sut->boot();

        $this->assertTrue(Configure::read('passbolt.plugins.sso.enabled'));
        $this->assertTrue(Configure::read('passbolt.plugins.accountRecovery.enabled'));
    }

    public function testEditionManager_Boot_CeEdition_EveryProPluginMentionedInConfigAreDisabled(): void
    {
        EditionOrganizationSettingFactory::make()
            ->setField('value', EditionDto::EDITION_CE)
            ->persist();
        // Simulate config/bootstrap.php having loaded pro.php (which it now does unconditionally).
        Configure::load('pro', 'default', true);

        $this->sut->boot();

        // Discover every `passbolt.plugins.X.enabled` key declared in pro.php and
        // assert each is `false` after EditionManager has disabled it.
        $pro = include CONFIG . 'pro.php';
        $plugins = $pro['passbolt']['plugins'] ?? [];
        $found = false;
        foreach ($plugins as $name => $config) {
            if (!is_array($config) || !array_key_exists('enabled', $config)) {
                continue;
            }
            $found = true;
            $this->assertFalse(
                Configure::read("passbolt.plugins.{$name}.enabled"),
                "PRO plugin '{$name}' should be disabled on CE edition but is not. " .
                "Did you forget to add \$this->disableFeaturePlugin('{$name}') " .
                'in EditionManager::disableProPluginsIfNotPro()?'
            );
        }
        $this->assertTrue($found, 'No PRO plugin .enabled keys found in pro.php — test wiring broken.');
    }
}

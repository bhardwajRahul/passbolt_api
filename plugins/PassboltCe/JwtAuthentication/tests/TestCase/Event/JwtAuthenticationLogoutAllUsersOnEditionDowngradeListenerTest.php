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

namespace Passbolt\JwtAuthentication\Test\TestCase\Event;

use App\Model\Entity\AuthenticationToken;
use App\Test\Factory\AuthenticationTokenFactory;
use App\Test\Factory\UserFactory;
use Cake\Event\Event;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Passbolt\Edition\Service\EditionDowngradeService;
use Passbolt\JwtAuthentication\Event\JwtAuthenticationLogoutAllUsersOnEditionDowngradeListener;

/**
 * @covers \Passbolt\JwtAuthentication\Event\JwtAuthenticationLogoutAllUsersOnEditionDowngradeListener
 */
class JwtAuthenticationLogoutAllUsersOnEditionDowngradeListenerTest extends TestCase
{
    use LocatorAwareTrait;
    use TruncateDirtyTables;

    private JwtAuthenticationLogoutAllUsersOnEditionDowngradeListener $listener;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->listener = new JwtAuthenticationLogoutAllUsersOnEditionDowngradeListener();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->listener);
        parent::tearDown();
    }

    public function testJwtAuthenticationLogoutAllUsersOnEditionDowngradeListener_DeactivatesAllRefreshTokensAcrossAllUsers(): void
    {
        /** @var array<\App\Model\Entity\User> $users */
        $users = UserFactory::make(3)->user()->persist();
        foreach ($users as $user) {
            AuthenticationTokenFactory::make(2)
                ->active()
                ->type(AuthenticationToken::TYPE_REFRESH_TOKEN)
                ->userId($user->id)
                ->persist();
        }

        $this->listener->invalidRefreshToken(new Event(EditionDowngradeService::EVENT_EDITION_DOWNGRADED));

        $activeTokens = AuthenticationTokenFactory::find()
            ->where(['type' => AuthenticationToken::TYPE_REFRESH_TOKEN, 'active' => true])
            ->count();
        $this->assertSame(0, $activeTokens);
        $inactiveTokens = AuthenticationTokenFactory::find()
            ->where(['type' => AuthenticationToken::TYPE_REFRESH_TOKEN, 'active' => false])
            ->count();
        $this->assertSame(6, $inactiveTokens);
    }

    public function testJwtAuthenticationLogoutAllUsersOnEditionDowngradeListener_LeavesNonRefreshAndInactiveTokensUntouched(): void
    {
        /** @var \App\Model\Entity\User $user */
        $user = UserFactory::make()->user()->persist();
        AuthenticationTokenFactory::make()
            ->active()
            ->type(AuthenticationToken::TYPE_REFRESH_TOKEN)
            ->userId($user->id)
            ->persist();
        AuthenticationTokenFactory::make()
            ->inactive()
            ->type(AuthenticationToken::TYPE_REFRESH_TOKEN)
            ->userId($user->id)
            ->persist();
        AuthenticationTokenFactory::make()
            ->active()
            ->type(AuthenticationToken::TYPE_LOGIN)
            ->userId($user->id)
            ->persist();

        $this->listener->invalidRefreshToken(new Event(EditionDowngradeService::EVENT_EDITION_DOWNGRADED));

        $activeLoginTokens = AuthenticationTokenFactory::find()
            ->where(['type' => AuthenticationToken::TYPE_LOGIN, 'active' => true])
            ->count();
        $this->assertSame(1, $activeLoginTokens);
        $inactiveRefreshTokens = AuthenticationTokenFactory::find()
            ->where(['type' => AuthenticationToken::TYPE_REFRESH_TOKEN, 'active' => false])
            ->count();
        $this->assertSame(2, $inactiveRefreshTokens);
        $activeRefreshTokens = AuthenticationTokenFactory::find()
            ->where(['type' => AuthenticationToken::TYPE_REFRESH_TOKEN, 'active' => true])
            ->count();
        $this->assertSame(0, $activeRefreshTokens);
    }

    public function testJwtAuthenticationLogoutAllUsersOnEditionDowngradeListener_NoOpWhenNoActiveRefreshTokens(): void
    {
        $this->listener->invalidRefreshToken(new Event(EditionDowngradeService::EVENT_EDITION_DOWNGRADED));

        $this->assertSame(
            0,
            AuthenticationTokenFactory::find()
                ->where(['type' => AuthenticationToken::TYPE_REFRESH_TOKEN, 'active' => true])
                ->count()
        );
    }
}

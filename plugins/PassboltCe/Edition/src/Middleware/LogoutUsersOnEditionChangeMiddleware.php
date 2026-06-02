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
namespace Passbolt\Edition\Middleware;

use App\Model\Entity\User;
use Authentication\AuthenticationServiceInterface;
use Authentication\Authenticator\SessionAuthenticator;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\Routing\Router;
use Passbolt\Edition\Service\EditionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Terminates session-authenticated requests whose login predates the last
 * edition change (CE → PRO or PRO → CE). Sessions that predate the change
 * must not survive it: the PRO data backing their permissions has been
 * wiped (downgrade) or new PRO policies now apply (upgrade), so re-
 * authentication against the post-change state is required.
 *
 * Source of truth is `Configure::passbolt.editionLastChangeDateTime`,
 * hydrated by EditionManager at boot from the `organization_settings.edition`
 * row's `modified` column (durable, survives cache eviction).
 *
 * JWT / refresh-token sessions are out of scope for this middleware — a
 * separate listener invalidates them when the edition-change event fires.
 *
 * Registered after SetUserIdentityInRequestMiddleware so the identity is
 * already hydrated to a User entity (and carries `last_logged_in` thanks
 * to the matching select-list amend in UsersFindersTrait::findAuthIdentifier).
 */
final class LogoutUsersOnEditionChangeMiddleware implements MiddlewareInterface
{
    /**
     * Configure key that disables the middleware entirely. When `true`, the
     * middleware is a pure pass-through regardless of any other state. Backed
     * by the `PASSBOLT_PLUGINS_EDITION_DISABLE_LOGOUT_USERS_ON_EDITION_CHANGE_MIDDLEWARE`
     * env var (see `plugins/PassboltCe/Edition/config/config.php`). Intended as
     * an operator-side escape hatch — e.g. to keep long-running ops sessions
     * alive across an edition change.
     *
     * @var string
     */
    public const CONFIGURE_KEY_DISABLED = 'passbolt.plugins.edition.disableLogoutUsersOnEditionChangeMiddleware';

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request Request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler Handler.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (Configure::read(self::CONFIGURE_KEY_DISABLED) === true) {
            return $handler->handle($request);
        }

        if (!$this->sessionPredatesEditionChange($request)) {
            return $handler->handle($request);
        }

        /** @var \Cake\Http\ServerRequest $request */
        $request->getSession()->destroy();

        return $this->buildLogoutResponse($request);
    }

    /**
     * Returns true when this request belongs to a session-authenticated user
     * whose login predates `passbolt.editionLastChangeDateTime`.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request.
     * @return bool
     */
    private function sessionPredatesEditionChange(ServerRequestInterface $request): bool
    {
        // No edition change recorded → middleware is a no-op. Instances that
        // have never transitioned (fresh install on either edition) land here.
        $lastChangeAt = Configure::read(EditionManager::CONFIGURE_KEY_LAST_CHANGE_DATETIME);
        if (!$lastChangeAt instanceof DateTime) {
            return false;
        }

        // Anonymous request → nothing to invalidate.
        $user = $request->getAttribute('identity');
        if (!$user instanceof User) {
            return false;
        }

        // Session-only: JWT auth is handled by its own listener.
        // getAuthenticationProvider() returns the authenticator that produced
        // the current identity — SessionAuthenticator for the session path,
        // a JWT-specific authenticator for token auth.
        $service = $request->getAttribute('authentication');
        if (!$service instanceof AuthenticationServiceInterface) {
            return false;
        }
        $provider = $service->getAuthenticationProvider();
        if (!$provider instanceof SessionAuthenticator) {
            return false;
        }

        $lastLoggedIn = $user->get('last_logged_in');
        if (!$lastLoggedIn instanceof DateTime) {
            return false;
        }

        // Loose comparison at second precision — matches the DTO's
        // millisecond-drift tolerance
        return $lastLoggedIn->getTimestamp() < $lastChangeAt->getTimestamp();
    }

    /**
     * Mirrors AuthLogoutController's response shape: 302 to the login page for
     * HTML clients, JSON envelope for API clients. We expose a 401 status on
     * the JSON path so the front-end's standard "re-authenticate" handler
     * fires (we did not honour the user's request — the session was forcibly
     * terminated — so this is not a success response).
     *
     * @param \Cake\Http\ServerRequest $request Request.
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function buildLogoutResponse(ServerRequest $request): ResponseInterface
    {
        $isJson = $request->is('json');
        $loginUrl = Router::url([
            'prefix' => 'Auth',
            'plugin' => null,
            'controller' => 'AuthLogin',
            'action' => 'loginGet',
            '_method' => 'GET',
            '_ext' => $isJson ? 'json' : null,
        ]);

        $response = new Response();
        if ($isJson) {
            return $response->withStatus(401)
                ->withHeader('Content-Type', 'application/json')
                ->withStringBody((string)json_encode([
                    'header' => [
                        'status' => 'error',
                        'message' => __('Your session expired due to an edition change. Please log in again.'),
                        'code' => 401,
                    ],
                    'body' => null,
                ]));
        }

        return $response->withStatus(302)->withHeader('Location', $loginUrl);
    }
}

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
namespace Passbolt\Edition\Test\TestCase\Middleware;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use Cake\TestSuite\TestCase;
use Passbolt\Edition\Middleware\EditionDowngradeDisabledMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \Passbolt\Edition\Middleware\EditionDowngradeDisabledMiddleware
 */
class EditionDowngradeDisabledMiddlewareTest extends TestCase
{
    public function testEditionDowngradeDisabledMiddleware_PassesThrough_WhenFlagIsFalse(): void
    {
        Configure::write(EditionDowngradeDisabledMiddleware::PASSBOLT_SECURITY_EDITION_DOWNGRADE_DISABLED, false);

        $request = $this->createMock(ServerRequestInterface::class);
        $expected = new Response();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($expected);

        $response = (new EditionDowngradeDisabledMiddleware())->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($expected, $response);
    }

    public function testEditionDowngradeDisabledMiddleware_ThrowsException_WhenFlagIsTrue(): void
    {
        Configure::write(EditionDowngradeDisabledMiddleware::PASSBOLT_SECURITY_EDITION_DOWNGRADE_DISABLED, true);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Edition downgrade is disabled.');

        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        (new EditionDowngradeDisabledMiddleware())->process($request, $handler);
    }
}

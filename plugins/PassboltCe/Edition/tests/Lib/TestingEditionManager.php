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
namespace Passbolt\Edition\Test\Lib;

use Cake\Core\Configure;
use Passbolt\Edition\Model\Dto\EditionDto;
use Passbolt\Edition\Service\EditionManager;

/**
 * Test stub that bypasses the DB read. Resolves edition from `Configure::passbolt.edition`
 * and defaults to PRO when unset, so tests can drive the edition with a single
 * `Configure::write('passbolt.edition', ...)` call.
 *
 * Registered via EditionManager::setInstance() from tests/bootstrap.php.
 */
class TestingEditionManager extends EditionManager
{
    protected function resolveEditionDto(): EditionDto
    {
        $value = Configure::read('passbolt.edition');
        $edition = is_string($value) ? $value : EditionDto::EDITION_PRO;

        return new EditionDto($edition);
    }
}

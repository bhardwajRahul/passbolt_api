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
const fs = require('node:fs');
const path = require('node:path');

const SRC = 'vendor/cakephp/localized/resources/locales/fr_FR';
const DEST = 'resources/locales/fr_FR';

fs.mkdirSync(DEST, { recursive: true });
for (const file of fs.readdirSync(SRC)) {
  if (file.endsWith('.po')) {
    fs.cpSync(path.join(SRC, file), path.join(DEST, file));
  }
}

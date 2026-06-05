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
namespace Passbolt\Edition\Command;

use App\Command\PassboltCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Passbolt\Edition\Service\PopulateEditionService;

class PopulateEditionCommand extends PassboltCommand
{
    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return __('Populate the edition row in organization_settings if missing.');
    }

    /**
     * @inheritDoc
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription($this->getCommandDescription());

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        parent::execute($args, $io);

        $io->out(__('Populating the edition row...'));

        (new PopulateEditionService())->populate();

        $io->success(__('Edition row populated successfully.'));

        return $this->successCode();
    }
}

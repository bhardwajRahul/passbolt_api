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
use Passbolt\Edition\Service\Cleanup\EditionDowngradeCleanupRunner;
use Passbolt\Edition\Service\EditionGetService;

/**
 * Wipes residual PRO data on a CE instance.
 *
 * Targets the scenario where a downgrade has already run (edition flag = CE)
 * but per-plugin cleanup left rows behind — for example, after a partial /
 * failed downgrade or rows seeded by a sysadmin while the instance was PRO.
 * Same data-model targets as EditionDowngradeService's step 2 (the
 * per-plugin EditionDowngradeCleanupServiceInterface implementations), but
 * with the subscription-key delete and edition-flag flip omitted.
 *
 * Refuses to run on a PRO instance — the right tool there is the downgrade
 * command, which atomically wipes data AND flips the flag inside a
 * transaction.
 *
 * No --username option: the cleanup runner only performs deletes, dispatches
 * no events and writes no audit trail, so there is nothing to attribute.
 *
 * Reachable as: bin/cake passbolt flush_pro_data
 */
class FlushProDataCommand extends PassboltCommand
{
    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return __('Flush residual PRO data on a CE instance.');
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        parent::execute($args, $io);

        // CE-only precondition: refuse on PRO.
        if ((new EditionGetService())->get()->isPro()) {
            $io->error(__('This command can only run on a CE instance. Use "passbolt edition_downgrade" first.'));
            $this->abort();
        }

        // Confirmation dialog. Default 'n' so a stray Enter doesn't wipe data.
        $io->warning(__('This will permanently wipe residual PRO data from the database. This cannot be undone.'));
        $continue = $io->askChoice(__('Continue?'), ['y', 'n'], 'n');
        if ($continue !== 'y') {
            $io->info(__('Aborting...'));

            return $this->successCode();
        }

        (new EditionDowngradeCleanupRunner())->run();
        $io->success(__('Residual PRO data was flushed.'));

        return $this->successCode();
    }
}

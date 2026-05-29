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
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Validation\EmailValidationRule;
use App\Utility\UserAccessControl;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Http\Exception\ConflictException;
use Passbolt\Edition\Service\EditionDowngradeService;

/**
 * CLI entry point for the in-product downgrade. Mirrors the HTTP endpoint
 * exposed by EditionSubscriptionsDeleteController, but attributes the action
 * to an admin user resolved from the --username option so downstream emails
 * and audit trails carry a real operator id.
 *
 * Reachable as: bin/cake passbolt edition_downgrade --username <admin-email>
 */
class EditionDowngradeCommand extends PassboltCommand
{
    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return __('Downgrade passbolt from PRO to CE edition.');
    }

    /**
     * @inheritDoc
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->addOption('username', [
                'short' => 'u',
                'required' => true,
                'help' => __('Email of the admin user performing the downgrade.'),
            ]);
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        parent::execute($args, $io);

        $admin = $this->resolveAdmin($args, $io);
        $uac = new UserAccessControl($admin->role->name, $admin->id, $admin->username);

        // Confirmation dialog. Default 'n' so a stray Enter doesn't wipe data.
        $io->warning(__('This will permanently wipe PRO data from the database. This cannot be undone.'));
        $continue = $io->askChoice(__('Continue?'), ['y', 'n'], 'n');
        if ($continue !== 'y') {
            $io->info(__('Aborting...'));

            return $this->successCode();
        }

        try {
            (new EditionDowngradeService())->downgrade($uac);
        } catch (ConflictException $e) {
            $io->warning($e->getMessage());

            // Already on CE means the desired end state is already reached.
            // Exit 0 so automation pipelines don't fail on a re-run.
            return $this->successCode();
        }

        $io->success(__('Passbolt was downgraded to the CE edition.'));

        return $this->successCode();
    }

    /**
     * Resolve --username to an active admin user. Aborts (non-zero exit) on
     * invalid email, missing user, soft-deleted user, inactive user, disabled
     * user, or non-admin user.
     *
     * @param \Cake\Console\Arguments $args CLI args.
     * @param \Cake\Console\ConsoleIo $io Console IO.
     * @return \App\Model\Entity\User
     */
    private function resolveAdmin(Arguments $args, ConsoleIo $io): User
    {
        $username = (string)$args->getOption('username');
        if (!EmailValidationRule::check($username)) {
            $io->error(__('The username should be a valid email.'));
            $this->abort();
        }

        /** @var \App\Model\Table\UsersTable $UsersTable */
        $UsersTable = $this->fetchTable('Users');
        // Push eligibility (active, not deleted, not disabled, admin role) into
        // SQL via the existing finders in UsersFindersTrait. Happy path is a
        // single query; only the failure path runs the fallback below.
        /** @var \App\Model\Entity\User|null $user */
        $user = $UsersTable->findByUsernameCaseAware($username)
            ->find('activeNotDeletedContainRole')
            ->find('notDisabled')
            ->where(['Roles.name' => Role::ADMIN])
            ->first();

        if ($user === null) {
            $io->error(__('No active admins were found.'));
            $this->abort();
        }

        return $user;
    }
}

<?php
declare(strict_types=1);

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SARL (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SARL (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         3.2.0
 */
namespace Passbolt\Edition\Command;

use App\Command\PassboltCommand;
use App\Model\Entity\Role;
use App\Utility\UserAccessControl;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Exception\CakeException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\Edition\Service\EditionSetService;
use Passbolt\Subscription\Error\Exception\Subscriptions\SubscriptionException;
use Passbolt\Subscription\Service\Subscriptions\SubscriptionKeyGetService;
use Passbolt\Subscription\Service\Subscriptions\SubscriptionKeyImportService;

/**
 * Subscription Check shell command.
 */
class SubscriptionImportCommand extends PassboltCommand
{
    use LocatorAwareTrait;

    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return __('Import a subscription key using file or text.');
    }

    /**
     * @inheritDoc
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->addOption('file', [
            'short' => 'f',
            'help' => __('Path to subscription key file.'),
            'default' => SubscriptionKeyGetService::SUBSCRIPTION_FILE,
        ]);
        $parser->addOption('text', [
            'short' => 't',
            'help' => __('Subscription key text (Base 64).'),
            'default' => '',
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        parent::execute($args, $io);

        $text = $args->getOption('text');
        $file = $args->getOption('file');
        $useText = !empty($text);

        if ($useText && !is_string($text)) {
            $this->error(__('The subscription key text is not valid.'), $io);
            $this->abort();
        }
        if (!$useText && (empty($file) || !is_string($file))) {
            $this->error(__('The subscription key file is invalid.'), $io);
            $this->abort();
        }

        $uac = $this->buildAdminUac();
        try {
            /** @var \Passbolt\Edition\Model\Table\EditionOrganizationTable $editionTable */
            $editionTable = $this->fetchTable('Passbolt/Edition.EditionOrganization');
            $editionTable->getConnection()->transactional(
                function () use ($useText, $text, $file, $uac): void {
                    // Save subscription key
                    $importService = new SubscriptionKeyImportService();
                    if ($useText) {
                        /** @var string $text */
                        $importService->import($text, $uac);
                    } else {
                        /** @var string $file */
                        $importService->importFromFile($file, $uac);
                    }

                    // Set edition to PRO in the DB
                    (new EditionSetService())->setToPro($uac);
                }
            );
        } catch (SubscriptionException $e) {
            $this->error($e->getMessage(), $io);
            $this->abort();
        } catch (CakeException $e) {
            $this->error($e->getMessage(), $io);
            $this->abort();
        }

        $this->success(__('The subscription key was successfully imported in the database.'), $io);

        return $this->successCode();
    }

    /**
     * @return \App\Utility\UserAccessControl
     * @throws \Passbolt\Subscription\Error\Exception\Subscriptions\SubscriptionException If no active admin exists.
     */
    private function buildAdminUac(): UserAccessControl
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');
        $firstAdmin = $usersTable->findFirstAdmin();
        if ($firstAdmin === null) {
            throw new SubscriptionException(__('No active admins were found.'));
        }

        return new UserAccessControl(Role::ADMIN, $firstAdmin->id);
    }
}

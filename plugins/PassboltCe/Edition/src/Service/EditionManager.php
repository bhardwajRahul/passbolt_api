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

namespace Passbolt\Edition\Service;

use App\BaseSolutionBootstrapper;
use App\Utility\Application\FeaturePluginAwareTrait;
use Cake\Core\Configure;
use Cake\Http\Exception\InternalErrorException;
use Passbolt\Edition\Model\Dto\EditionDto;
use Passbolt\Ee\EeSolutionBootstrapper;

class EditionManager
{
    use FeaturePluginAwareTrait;

    private bool $booted = false;

    /**
     * @var string
     */
    private string $edition = EditionDto::EDITION_CE;

    private ?string $solutionBootstrapperClass = null;

    /**
     * @var array<string, class-string>
     */
    private array $editionBootstrapperMapping = [
        EditionDto::EDITION_CE => BaseSolutionBootstrapper::class,
        EditionDto::EDITION_PRO => EeSolutionBootstrapper::class,
    ];

    /**
     * Boot edition manager to determine instance's feature capabilities.
     * Called once from Application::bootstrap().
     *
     * For cloud, overwrite this to hard-code it to set 'cloud' edition.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->edition = Configure::readOrFail('passbolt.edition');

        $this->disableProPluginsIfNotPro();

        $this->setSolutionBootstrapperClass();

        $this->booted = true;
    }

    /**
     * Disable each PRO-only plugin if edition is CE.
     *
     * Each plugin is listed explicitly — when a new PRO plugin is introduced,
     * add a line here and a matching assertion in EditionManagerTest.
     *
     * @return void
     */
    private function disableProPluginsIfNotPro(): void
    {
        if ($this->edition === EditionDto::EDITION_PRO) {
            return;
        }

        $this->disableFeaturePlugin('AccountRecovery');
        $this->disableFeaturePlugin('AuditLog');
        $this->disableFeaturePlugin('DirectorySync');
        $this->disableFeaturePlugin('Ee');
        $this->disableFeaturePlugin('MfaPolicies');
        $this->disableFeaturePlugin('PasswordExpiryPolicies');
        $this->disableFeaturePlugin('PasswordPoliciesUpdate');
        $this->disableFeaturePlugin('Scim');
        $this->disableFeaturePlugin('Sso');
        $this->disableFeaturePlugin('SsoRecover');
        $this->disableFeaturePlugin('Subscription');
        $this->disableFeaturePlugin('Tags');
        $this->disableFeaturePlugin('UserPassphrasePolicies');
    }

    /**
     * @return string
     */
    public function getEdition(): string
    {
        return $this->edition;
    }

    /**
     * @return bool
     */
    public function isPro(): bool
    {
        return $this->edition === EditionDto::EDITION_PRO;
    }

    /**
     * @return string|null
     */
    public function getSolutionBootstrapperClass(): ?string
    {
        return $this->solutionBootstrapperClass;
    }

    /**
     * @return void
     * @throws \Cake\Http\Exception\InternalErrorException When bootstrapper for edition not found.
     */
    private function setSolutionBootstrapperClass(): void
    {
        if (!isset($this->editionBootstrapperMapping[$this->edition])) {
            throw new InternalErrorException('Edition "' . $this->edition . '" does not exist');
        }

        $this->solutionBootstrapperClass = $this->editionBootstrapperMapping[$this->edition];
    }
}

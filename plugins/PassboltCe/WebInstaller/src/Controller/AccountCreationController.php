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
 * @since         2.0.0
 */
namespace Passbolt\WebInstaller\Controller;

use Cake\Core\Exception\CakeException;
use Passbolt\WebInstaller\Form\AccountCreationForm;

class AccountCreationController extends WebInstallerController
{
    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->stepInfo['previous'] = $this->getPrevious();
        $this->stepInfo['next'] = '/install/subscription';
        $this->stepInfo['template'] = 'Pages/account_creation';
    }

    /**
     * Mirrors the inbound flow: account_creation is reached from /install/email
     * when SMTP wasn't already configured, otherwise from /install/options.
     */
    protected function getPrevious(): string
    {
        if (!$this->webInstaller->getSettings('hasSmtpSettings')) {
            return '/install/email';
        }

        return '/install/options';
    }

    /**
     * Index
     *
     * @return mixed|void
     */
    public function index()
    {
        if ($this->request->is('post')) {
            return $this->indexPost();
        }

        $this->prefillFromSession();

        $this->set('formExecuteResult', null);
        $this->render('Pages/account_creation');
    }

    /**
     * Re-injects previously saved first_user settings into the request so the
     * form re-renders with the values the user already entered (Cancel/Back
     * round-trip). Saved shape is nested ({username, profile: {first_name,
     * last_name}}) but the form fields are flat — flatten here.
     */
    private function prefillFromSession(): void
    {
        $saved = $this->webInstaller->getSettings('first_user');
        if (empty($saved)) {
            return;
        }

        $flat = [
            'username' => $saved['username'] ?? null,
            'first_name' => $saved['profile']['first_name'] ?? null,
            'last_name' => $saved['profile']['last_name'] ?? null,
        ];
        foreach ($flat as $key => $value) {
            if ($value !== null) {
                $this->request = $this->request->withData($key, $value);
            }
        }
    }

    /**
     * Index post
     *
     * @return mixed|void
     */
    protected function indexPost()
    {
        try {
            $data = $this->getAndValidateData();
        } catch (CakeException $e) {
            $this->_error($e->getMessage());

            return;
        }

        $this->webInstaller->setSettingsAndSave('first_user', $data);
        $this->goToNextStep();
    }

    /**
     * Get and validate the posted data.
     *
     * @throws \Cake\Core\Exception\CakeException If the user is not valid
     * @return array
     */
    protected function getAndValidateData()
    {
        $data = $this->request->getData();
        $accountCreationForm = new AccountCreationForm();
        $isValid = $accountCreationForm->execute($data);
        $this->set('formExecuteResult', $accountCreationForm);

        if (!$isValid) {
            throw new CakeException(__('The data entered are not correct'));
        }

        return [
            'username' => $data['username'],
            'profile' => [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
            ],
        ];
    }
}

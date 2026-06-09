<?php
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
 *
 * @see \Passbolt\Edition\Notification\Email\EditionDowngradeEmailRedactor
 * @var \App\View\AppView $this
 * @var array $body
 */

use App\Utility\Purifier;
use App\View\Helper\AvatarHelper;
use Cake\Routing\Router;

if (PHP_SAPI === 'cli') {
    Router::fullBaseUrl($body['fullBaseUrl']);
}

/** @var array $operator */
$operator = $body['operator'];
/** @var \Cake\I18n\DateTime $downgradedAt */
$downgradedAt = $body['downgradedAt'];
$operatorFullName = Purifier::clean($operator['profile']['full_name']);

echo $this->element('Email/module/avatar', [
    'url' => AvatarHelper::getAvatarUrl($operator['profile']['avatar']),
    'text' => $this->element('Email/module/avatar_text', [
        'user' => $operator,
        'datetime' => $downgradedAt,
        'text' => __('{0} downgraded the instance to Community Edition', $operatorFullName),
    ]),
]);

echo $this->element('Email/module/text', [
    'text' => __(
        'Your Passbolt instance was downgraded from Pro to Community Edition. Pro-only features and data have been removed.'
    ),
]);

echo $this->element('Email/module/button', [
    'url' => Router::url('/', true),
    'text' => __('Log in to Passbolt'),
]);

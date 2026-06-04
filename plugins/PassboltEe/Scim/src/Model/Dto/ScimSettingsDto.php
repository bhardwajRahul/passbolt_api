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
namespace Passbolt\Scim\Model\Dto;

use Cake\I18n\DateTime;

class ScimSettingsDto
{
    /**
     * @var string|null
     */
    public ?string $id = null;

    /**
     * @var string|null
     */
    public ?string $settingId = null;

    /**
     * @var string|null
     */
    public ?string $scimUserId = null;

    /**
     * @var string|null
     */
    public ?string $baseApiEndpoint = null;

    /**
     * @var string|null
     */
    public ?string $expired = null;

    /**
     * @var \Cake\I18n\DateTime|null
     */
    public ?DateTime $created = null;

    /**
     * @var string|null
     */
    public ?string $createdBy = null;

    /**
     * @var \Cake\I18n\DateTime|null
     */
    public ?DateTime $modified = null;

    /**
     * @var string|null
     */
    public ?string $modifiedBy = null;

    /**
     * @param string|null $id ID.
     * @param string|null $settingId Setting ID.
     * @param string|null $scimUserId SCIM user ID.
     * @param string|null $baseApiEndpoint Base API endpoint.
     * @param string|null $expired Expired.
     * @param \Cake\I18n\DateTime|null $created Created time.
     * @param string|null $createdBy Created by.
     * @param \Cake\I18n\DateTime|null $modified Modified time.
     * @param string|null $modifiedBy Modified by.
     */
    public function __construct(
        ?string $id,
        ?string $settingId,
        ?string $scimUserId,
        ?string $baseApiEndpoint,
        ?string $expired,
        ?DateTime $created,
        ?string $createdBy,
        ?DateTime $modified,
        ?string $modifiedBy,
    ) {
        $this->id = $id;
        $this->settingId = $settingId;
        $this->scimUserId = $scimUserId;
        $this->baseApiEndpoint = $baseApiEndpoint;
        $this->expired = $expired;
        $this->created = $created;
        $this->createdBy = $createdBy;
        $this->modified = $modified;
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * Returns object of itself from provided array.
     *
     * @param array $data Data.
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['setting_id'] ?? null,
            $data['scim_user_id'] ?? null,
            $data['base_api_endpoint'] ?? null,
            $data['expired'] ?? null,
            $data['created'] ?? null,
            $data['created_by'] ?? null,
            $data['modified'] ?? null,
            $data['modified_by'] ?? null,
        );
    }

    /**
     * Returns array representation of the object.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'setting_id' => $this->settingId,
            'scim_user_id' => $this->scimUserId,
            'base_api_endpoint' => $this->baseApiEndpoint,
            'expired' => $this->expired,
            'created' => $this->created,
            'created_by' => $this->createdBy,
            'modified' => $this->modified,
            'modified_by' => $this->modifiedBy,
        ];
    }
}

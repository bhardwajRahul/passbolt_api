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
 * @since         4.10.0
 */
namespace Passbolt\Metadata\Service;

use App\Error\Exception\CustomValidationException;
use App\Error\Exception\ValidationException;
use App\Utility\UserAccessControl;
use Cake\Event\EventDispatcherTrait;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Passbolt\Metadata\Model\Dto\MetadataKeysSettingsDto;

class MetadataKeysSettingsSetService
{
    use EventDispatcherTrait;
    use LocatorAwareTrait;

    public const AFTER_METADATA_SETTINGS_SET_SUCCESS_EVENT_NAME = 'MetadataSettings.afterSettingSet.success';

    /**
     * Validates and save the metadata settings
     *
     * @param \App\Utility\UserAccessControl $uac user access control
     * @param array $data Data provided in the payload
     * @return \Passbolt\Metadata\Model\Dto\MetadataKeysSettingsDto dto
     * @throws \Cake\Http\Exception\UnauthorizedException When user role is not admin.
     * @throws \App\Error\Exception\CustomValidationException When there are validation errors.
     * @throws \Cake\Http\Exception\InternalErrorException|\Exception When unable to save the entity.
     * @throws \App\Error\Exception\FormValidationException if the data does not validate
     */
    public function saveSettings(UserAccessControl $uac, array $data): MetadataKeysSettingsDto
    {
        $uac->assertIsAdmin();

        $settingsInDB = MetadataKeysSettingsGetService::getSettings();
        $isDisablingZeroKnowledge = $this->isDisablingZeroKnowledge($data, $settingsInDB);
        $dto = (new MetadataKeysSettingsAssertService())->assert($data, $isDisablingZeroKnowledge);

        if ($isDisablingZeroKnowledge && $this->shouldCreateMetadataPrivateKey($dto, $data)) {
            $service = new MetadataPrivateKeysCreateService();
            foreach ($data['metadata_private_keys'] as $i => $key) {
                try {
                    $service->create($uac, $key['metadata_key_id'], $key);
                } catch (ValidationException $exception) {
                    $msg = __('The server metadata private keys data are invalid.');
                    $errors['metadata_private_keys'][$i] = $exception->getErrors();
                    throw new CustomValidationException($msg, $errors);
                }
            }
        }

        /** @var \App\Model\Table\OrganizationSettingsTable $orgSettingsTable */
        $orgSettingsTable = $this->fetchTable('OrganizationSettings');
        $updatedEntity = $orgSettingsTable->createOrUpdateSetting(
            MetadataKeysSettingsGetService::ORG_SETTING_PROPERTY,
            $dto->toJson(),
            $uac
        );

        $this->dispatchEvent(
            static::AFTER_METADATA_SETTINGS_SET_SUCCESS_EVENT_NAME,
            compact('dto', 'updatedEntity', 'uac'),
            $this
        );

        return $dto;
    }

    /**
     * @param \Passbolt\Metadata\Model\Dto\MetadataKeysSettingsDto $settingsDto Metadata keys settings DTO.
     * @param array $data Data to check against.
     * @return bool
     */
    private function shouldCreateMetadataPrivateKey(MetadataKeysSettingsDto $settingsDto, array $data): bool
    {
        $metadataKeysTable = $this->fetchTable('Passbolt/Metadata.MetadataKeys');
        $nonDeletedKeysCount = $metadataKeysTable->find()
            ->where(['deleted IS NULL'])
            ->all()
            ->count();

        if ($nonDeletedKeysCount && $settingsDto->isUserFriendlyMode()) {
            /** @var \Passbolt\Metadata\Model\Table\MetadataPrivateKeysTable $metadataPrivateKeysTable */
            $metadataPrivateKeysTable = $this->fetchTable('Passbolt/Metadata.MetadataPrivateKeys');
            $serverKeysCount = $metadataPrivateKeysTable->find()
                ->where(['user_id IS' => null])
                ->orderBy(['created' => 'DESC'])
                ->all()
                ->count();
            if ($serverKeysCount === 0) {
                if (
                    !isset($data['metadata_private_keys']) ||
                    !is_array($data['metadata_private_keys']) ||
                    !count($data['metadata_private_keys'])
                ) {
                    $msg = __('The server metadata private key is required to enable these settings.');
                    throw new BadRequestException($msg);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * When updating the settings, we want to know if the zero knowledge mode is being disabled.
     * If so, metadata private keys will be requested in the payload at a later stage.
     *
     * @param array $data payload
     * @param \Passbolt\Metadata\Model\Dto\MetadataKeysSettingsDto $organizationSetting DTO of the settings currently in DB
     * @return bool
     */
    private function isDisablingZeroKnowledge(array $data, MetadataKeysSettingsDto $organizationSetting): bool
    {
        if ($organizationSetting->isUserFriendlyMode()) {
            // The setting is already in user-friendly mode, potentially because falling back on the default settings
            return false;
        }
        if (!isset($data['zero_knowledge_key_share'])) {
            // If not set in payload, will fail validation at a later stage
            return false;
        }

        return !$data['zero_knowledge_key_share'];
    }
}

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
namespace Passbolt\Edition\Model\Dto;

use App\Model\Entity\OrganizationSetting;
use Cake\I18n\DateTime;

/**
 * Immutable value object representing the runtime edition flag served by
 * the application. The only accepted values are `EditionDto::EDITION_CE`
 * (`'ce'`) and `EditionDto::EDITION_PRO` (`'pro'`). Any other input — empty
 * string, unknown literal, value with surrounding whitespace, etc. — is
 * silently sanitised to `EditionDto::EDITION_CE`, which is the safe default
 * (PRO features stay locked behind a valid subscription key).
 *
 * The DTO is the single source of truth for what counts as a valid edition
 * value. Downstream callers must never compare raw strings.
 */
final class EditionDto
{
    /**
     * Canonical literal for the Community Edition.
     */
    public const EDITION_CE = 'ce';

    /**
     * Canonical literal for the Pro edition.
     */
    public const EDITION_PRO = 'pro';

    /**
     * Whitelist of accepted edition values.
     *
     * @var list<string>
     */
    private const VALID_EDITIONS = [self::EDITION_CE, self::EDITION_PRO];

    /**
     * The sanitised edition value. Guaranteed to be one of
     * {@see self::VALID_EDITIONS}.
     */
    public readonly string $edition;

    /**
     * Timestamp of the last edition change (CE → PRO or PRO → CE), or `null`
     * when the edition has never been modified since creation. Populated when
     * the underlying `organization_settings.edition` row has been touched
     * since creation (`modified` differs from `created`). The initial insert
     * — when `created == modified` — is excluded.
     */
    public readonly ?DateTime $lastEditionChangeDateTime;

    /**
     * @param string $edition Raw edition value. Sanitised to
     *   {@see self::EDITION_CE} when not in {@see self::VALID_EDITIONS}.
     * @param \Cake\I18n\DateTime|null $lastEditionChangeDateTime Last edition
     *   change timestamp. `null` when the row was never touched after insert.
     */
    public function __construct(string $edition, ?DateTime $lastEditionChangeDateTime = null)
    {
        $this->edition = self::sanitise($edition);
        $this->lastEditionChangeDateTime = $lastEditionChangeDateTime;
    }

    /**
     * Builds a DTO from the `organization_settings.edition` row, detecting
     * the last-edition-change timestamp from the row's `created` / `modified`
     * columns.
     *
     * Detection fires whenever `created` and `modified` differ, regardless of
     * the current value: a touched row reflects an edition change (either
     * direction). The comparison is done at second precision (`getTimestamp()`)
     * so millisecond drift between two TimestampBehavior touches on the same
     * insert is ignored.
     *
     * @param \App\Model\Entity\OrganizationSetting|null $row Edition row, or `null`
     *   when no row exists.
     * @return self
     */
    public static function fromOrgSettingRow(?OrganizationSetting $row): self
    {
        if ($row === null) {
            return new self(self::EDITION_CE);
        }

        $value = (string)$row->get('value');

        $lastChangeAt = null;
        $created = $row->get('created');
        $modified = $row->get('modified');
        if (
            $created instanceof DateTime
            && $modified instanceof DateTime
            && $created->getTimestamp() !== $modified->getTimestamp()
        ) {
            $lastChangeAt = $modified;
        }

        return new self($value, $lastChangeAt);
    }

    /**
     * Returns the sanitised edition value.
     *
     * @return string
     */
    public function getEdition(): string
    {
        return $this->edition;
    }

    /**
     * @return \Cake\I18n\DateTime|null Timestamp of the last edition change
     *   (CE → PRO or PRO → CE), or `null` when the edition has never been
     *   modified since creation.
     */
    public function getLastEditionChangeDateTime(): ?DateTime
    {
        return $this->lastEditionChangeDateTime;
    }

    /**
     * @return bool True when the edition is {@see self::EDITION_CE}.
     */
    public function isCe(): bool
    {
        return $this->edition === self::EDITION_CE;
    }

    /**
     * @return bool True when the edition is {@see self::EDITION_PRO}.
     */
    public function isPro(): bool
    {
        return $this->edition === self::EDITION_PRO;
    }

    /**
     * @return array{edition: string, lastEditionChangeDateTime: string|null}
     */
    public function toArray(): array
    {
        return [
            'edition' => $this->edition,
            'lastEditionChangeDateTime' => $this->lastEditionChangeDateTime?->toAtomString(),
        ];
    }

    /**
     * Reduces an arbitrary input string to a known-good edition literal.
     * Trimming guards against trailing whitespace introduced by manual SQL
     * edits or copy-paste in a config tool.
     *
     * @param string $value Raw input.
     * @return string One of {@see self::VALID_EDITIONS}.
     */
    private static function sanitise(string $value): string
    {
        $candidate = strtolower(trim($value));

        return in_array($candidate, self::VALID_EDITIONS, true)
            ? $candidate
            : self::EDITION_CE;
    }
}

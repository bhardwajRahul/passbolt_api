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
     * @param string $edition Raw edition value. Sanitised to
     *   {@see self::EDITION_CE} when not in {@see self::VALID_EDITIONS}.
     */
    public function __construct(string $edition)
    {
        $this->edition = self::sanitise($edition);
    }

    /**
     * Builds a DTO from an untrusted string (e.g. a value loaded straight
     * from `organization_settings.value`).
     *
     * @param string|null $value Raw value, possibly `null` when the row is
     *   missing.
     * @return self
     */
    public static function fromString(?string $value): self
    {
        return new self($value ?? self::EDITION_CE);
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
     * @return array{edition: string}
     */
    public function toArray(): array
    {
        return ['edition' => $this->edition];
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

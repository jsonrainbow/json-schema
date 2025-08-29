<?php

declare(strict_types=1);

namespace JsonSchema;

/**
 * @method static DraftIdentifiers DRAFT_3()
 * @method static DraftIdentifiers DRAFT_4()
 * @method static DraftIdentifiers DRAFT_6()
 * @method static DraftIdentifiers DRAFT_7()
 * @method static DraftIdentifiers DRAFT_2019_09()
 * @method static DraftIdentifiers DRAFT_2020_12()
 */
class DraftIdentifiers extends Enum
{
    public const DRAFT_3 = 'http://json-schema.org/draft-03/schema#';
    public const DRAFT_4 = 'http://json-schema.org/draft-04/schema#';
    public const DRAFT_6 = 'http://json-schema.org/draft-06/schema#';
    public const DRAFT_7 = 'http://json-schema.org/draft-07/schema#';
    public const DRAFT_2019_09 = 'https://json-schema.org/draft/2019-09/schema';
    public const DRAFT_2020_12 = 'https://json-schema.org/draft/2020-12/schema';

    public function toConstraintName(): string
    {
        switch ($this->getValue()) {
            case self::DRAFT_3:
                return 'draft03';
            case self::DRAFT_4:
                return 'draft04';
            case self::DRAFT_6:
                return 'draft06';
            case self::DRAFT_7:
                return 'draft07';
            case self::DRAFT_2019_09:
                return 'draft2019-09';
            case self::DRAFT_2020_12:
                return 'draft2020-12';
            default:
                throw new \Exception('Unsupported schema URI: ' . $this->getValue());
        }
    }

    public function withoutFragment(): string
    {
        return rtrim($this->getValue(), '#');
    }
}

<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Models;

use Contao\Model;

/**
 * @property int $id
 * @property int $tstamp
 * @property int $meetup_id
 * @property int $option_id
 * @property int $member_id
 */
class PsaMeetupPollVoteModel extends Model
{
    protected static $strTable = 'tl_psa_meetup_poll_vote';
}

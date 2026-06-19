<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Models;

use Contao\Model;

/**
 * @property int $id
 * @property int $tstamp
 * @property int $campaign_id
 * @property int $reason_id
 * @property int $candidate_id
 * @property int $member_id
 */
class PsaVoteBallotModel extends Model
{
    protected static $strTable = 'tl_psa_vote_ballot';
}

<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Models;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $sorting
 * @property int    $reason_id
 * @property string $name
 * @property string $photo
 * @property string $position
 * @property string $description
 * @property string $published
 */
class PsaVoteCandidateModel extends Model
{
    protected static $strTable = 'tl_psa_vote_candidate';
}

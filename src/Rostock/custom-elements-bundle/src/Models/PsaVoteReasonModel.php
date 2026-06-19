<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Models;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $sorting
 * @property string $title
 * @property string $description
 * @property string $photo
 * @property string $published
 */
class PsaVoteReasonModel extends Model
{
    protected static $strTable = 'tl_psa_vote_reason';
}

<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Models;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $title
 * @property string $description
 * @property int    $startDate
 * @property int    $endDate
 * @property string $showResults
 * @property string $published
 */
class PsaVoteCampaignModel extends Model
{
    protected static $strTable = 'tl_psa_vote_campaign';
}

<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Models;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $member_id
 * @property string $title
 * @property string $description
 * @property int    $meetupDate
 * @property string $location
 * @property string $postType
 * @property string $pollQuestion
 * @property string $published
 */
class PsaMeetupModel extends Model
{
    protected static $strTable = 'tl_psa_meetup';
}

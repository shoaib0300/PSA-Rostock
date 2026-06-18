<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Models;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $pid
 * @property int    $member_id
 * @property string $comment
 * @property string $published
 */
class PsaMeetupCommentModel extends Model
{
    protected static $strTable = 'tl_psa_meetup_comment';
}

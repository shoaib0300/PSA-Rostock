<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Models;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property string $label
 * @property int    $sorting
 */
class PsaMeetupPollOptionModel extends Model
{
    protected static $strTable = 'tl_psa_meetup_poll_option';
}

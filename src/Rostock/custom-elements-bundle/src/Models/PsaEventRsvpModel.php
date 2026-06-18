<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Models;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $event_id
 * @property int    $member_id
 * @property string $status
 */
class PsaEventRsvpModel extends Model
{
    protected static $strTable = 'tl_psa_event_rsvp';
}

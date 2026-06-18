<?php

declare(strict_types=1);

namespace Rostock\CustomElementsBundle\Models;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $sorting
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $position
 * @property string $degree
 * @property string $university
 * @property string $photo
 * @property string $published
 */
class PsaTeamMemberModel extends Model
{
    protected static $strTable = 'tl_psa_team_member';
}

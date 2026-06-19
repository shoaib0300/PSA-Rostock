<?php

declare(strict_types=1);

$strTable = 'tl_psa_vote_ballot';

$GLOBALS['TL_DCA'][$strTable] = [
    'config' => [
        'dataContainer' => Contao\DC_Table::class,
        'closed' => true,
        'notEditable' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'campaign_id' => 'index',
                'member_id' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['tstamp'],
            'flag' => 6,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['campaign_id', 'member_id', 'candidate_id', 'tstamp'],
            'format' => '%s',
            'label_callback' => [$strTable, 'generateLabel'],
        ],
        'operations' => [
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
            'delete' => ['href' => 'act=delete', 'icon' => 'delete.svg'],
        ],
    ],
    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'tstamp' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'campaign_id' => [
            'foreignKey' => 'tl_psa_vote_campaign.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy', 'table' => 'tl_psa_vote_campaign', 'field' => 'id'],
        ],
        'reason_id' => [
            'foreignKey' => 'tl_psa_vote_reason.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'candidate_id' => [
            'foreignKey' => 'tl_psa_vote_candidate.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'member_id' => [
            'foreignKey' => 'tl_member.username',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy', 'table' => 'tl_member', 'field' => 'id'],
        ],
    ],
];

class tl_psa_vote_ballot
{
    /**
     * @param array<string, mixed> $row
     * @param array<int, mixed>   $args
     */
    public function generateLabel(array $row, string $label, \Contao\DataContainer $dc, array $args = []): string
    {
        $db = \Contao\Database::getInstance();
        $campaign = $db->prepare('SELECT title FROM tl_psa_vote_campaign WHERE id=?')->execute((int) $row['campaign_id']);
        $member = $db->prepare('SELECT username FROM tl_member WHERE id=?')->execute((int) $row['member_id']);
        $candidate = $db->prepare('SELECT name FROM tl_psa_vote_candidate WHERE id=?')->execute((int) $row['candidate_id']);

        return sprintf(
            '%s voted for %s in %s',
            $member->numRows > 0 ? (string) $member->username : '#'.$row['member_id'],
            $candidate->numRows > 0 ? (string) $candidate->name : '#'.$row['candidate_id'],
            $campaign->numRows > 0 ? (string) $campaign->title : '#'.$row['campaign_id'],
        );
    }
}

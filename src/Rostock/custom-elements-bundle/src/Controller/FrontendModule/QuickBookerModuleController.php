<?php
// Path: src/controller/FrontendModule/QuickBookerModuleController.php
namespace Rostock\CustomElementsBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Contao\FrontendTemplate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'Quick Booker',template: 'mod_quick_booker_module', cache: false)]
class QuickBookerModuleController extends AbstractFrontendModuleController
{
    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $db = Database::getInstance();

        // Fetch seasons that are published and currently active OR in the future
        $today = strtotime('today');

        $records = $db->prepare("
            SELECT id, seasonName, startDate, endDate, minStay 
            FROM tl_quickbooker 
            WHERE published = '1' AND endDate >= ? 
            ORDER BY startDate ASC
        ")->execute($today);

        $seasons = [];

        while ($records->next()) {
            $seasons[] = [
                'id' => $records->id,
                'seasonName' => $records->seasonName,
                'startDate' => date('Y-m-d', $records->startDate),
                'endDate' => date('Y-m-d', $records->endDate),
                'minStay' => $records->minStay,
            ];
        }

        $template->seasons = $seasons;
        return $template->getResponse();
    }
}
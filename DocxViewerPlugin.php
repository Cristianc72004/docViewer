<?php
namespace APP\plugins\generic\docxViewer;

use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\RedirectAction;
use APP\template\TemplateManager;
use APP\core\Application;

class DocxViewerPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null): bool
    {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled()) {
                Hook::add('TemplateManager::fetch', [$this, 'addViewButton']);
                Hook::add('LoadHandler', [$this, 'callbackLoadHandler']);
            }
            return true;
        }
        return false;
    }

    public function getDisplayName(): string
    {
        return __('plugins.generic.docxViewer.displayName');
    }

    public function getDescription(): string
    {
        return __('plugins.generic.docxViewer.description');
    }

    public function callbackLoadHandler(string $hookName, array $args): bool
    {
        if ($args[0] === 'docxViewer' && $args[1] === 'view') {
            require_once($this->getPluginPath() . '/classes/DocxViewerHandler.php');
            define('HANDLER_CLASS', \APP\plugins\generic\docxViewer\classes\DocxViewerHandler::class);
            return true;
        }
        return false;
    }

    public function addViewButton($hookName, $params): bool
    {
        // Verifica si los índices existen antes de usarlos
        if (!is_array($params) || count($params) < 2 || !isset($params[0]) || !isset($params[1])) {
            return false;
        }
    
        $templateMgr = $params[0];
        $resource = $params[1];
    
        if ($resource !== 'controllers/grid/gridRow.tpl') {
            return false;
        }
    
        $row = $templateMgr->getTemplateVars('row');
        if (!$row || !method_exists($row, 'getData')) {
            return false;
        }
    
        $data = $row->getData();
        if (!isset($data['submissionFile'])) {
            return false;
        }
    
        $submissionFile = $data['submissionFile'];
        $mimeType = strtolower($submissionFile->getData('mimetype'));
    
        if ($mimeType !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            return false;
        }
    
        $request = Application::get()->getRequest();
        $dispatcher = $request->getDispatcher();
    
        $url = $dispatcher->url(
            $request,
            \PKP\core\PKPApplication::ROUTE_PAGE,
            null,
            'docxViewer',
            'view',
            null,
            [
                'submissionFileId' => $submissionFile->getId(),
                'submissionId' => $submissionFile->getData('submissionId'),
                'stageId' => (int)$request->getUserVar('stageId')
            ]
        );
    
        $row->addAction(new LinkAction(
            'viewDocx',
            new RedirectAction($url),
            __('plugins.generic.docxViewer.button.viewDocx'),
            'preview'
        ));
    
        return false;
    }
    
}

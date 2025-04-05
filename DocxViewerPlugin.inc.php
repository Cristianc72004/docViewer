<?php

namespace APP\plugins\generic\docxViewer;

use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\RedirectAction;
use PKP\core\Application;
use APP\facades\Repo;

class DocxViewerPlugin extends GenericPlugin {

    public function register($category, $path, $mainContextId = null) {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled()) {
                Hook::add('TemplateManager::fetch', [$this, 'addViewButton']);
                Hook::add('LoadHandler', [$this, 'callbackLoadHandler']);
            }
            return true;
        }
        return false;
    }

    public function getDisplayName() {
        return __('plugins.generic.docxViewer.displayName');
    }

    public function getDescription() {
        return __('plugins.generic.docxViewer.description');
    }

    public function callbackLoadHandler($hookName, $args) {
        if ($args[0] === 'docxViewer' && $args[1] === 'view') {
            require_once($this->getPluginPath() . '/classes/DocxViewerHandler.inc.php');
            define('HANDLER_CLASS', 'APP\\plugins\\generic\\docxViewer\\DocxViewerHandler');
            return true;
        }
        return false;
    }

    public function addViewButton($hookName, $params) {
        $templateMgr = $params[0];
        $resource = $params[1];

        // Asegurarse de que estamos en la plantilla correcta
        if ($resource !== 'controllers/grid/gridRow.tpl') return false;

        $row = $templateMgr->getTemplateVars('row');
        $submissionFile = $row?->getData();

        // Solo continuar si es un SubmissionFile
        if (!$submissionFile instanceof \PKP\submission\SubmissionFile) return false;

        $mimeType = strtolower($submissionFile->getData('mimetype'));
        if ($mimeType !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') return false;

        $request = Application::get()->getRequest();
        $dispatcher = $request->getDispatcher();
        $submissionId = $submissionFile->getData('submissionId');
        $stageId = (int) $request->getUserVar('stageId');

        $url = $dispatcher->url($request, ROUTE_PAGE, null, 'docxViewer', 'view', null, [
            'submissionFileId' => $submissionFile->getId(),
            'submissionId' => $submissionId,
            'stageId' => $stageId
        ]);

        $row->addAction(new LinkAction(
            'viewDocx',
            new RedirectAction($url),
            __('plugins.generic.docxViewer.button.viewDocx'),
            'preview'
        ));

        return false;
    }
}

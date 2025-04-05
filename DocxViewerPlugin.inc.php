<?php

import('lib.pkp.classes.plugins.GenericPlugin');

class DocxViewerPlugin extends GenericPlugin {
    function getDisplayName() {
        return __('plugins.generic.docxViewer.displayName');
    }

    function getDescription() {
        return __('plugins.generic.docxViewer.description');
    }

    function register($category, $path, $mainContextId = null) {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled()) {
                HookRegistry::register('TemplateManager::fetch', [$this, 'addViewButton']);
                HookRegistry::register('LoadHandler', [$this, 'callbackLoadHandler']);
            }
            return true;
        }
        return false;
    }

    public function callbackLoadHandler($hookName, $args) {
        if ($args[0] === 'docxViewer' && $args[1] === 'view') {
            require_once($this->getPluginPath() . '/classes/DocxViewerHandler.inc.php');
            define('HANDLER_CLASS', 'DocxViewerHandler');
            return true;
        }
        return false;
    }

    public function addViewButton($hookName, $params) {
        $templateMgr = $params[0];
        $resource = $params[1];
        if ($resource !== 'controllers/grid/gridRow.tpl') return false;

        $row = $templateMgr->getTemplateVars('row');
        $data = $row->getData();
        if (!isset($data['submissionFile'])) return false;

        $submissionFile = $data['submissionFile'];
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

        import('lib.pkp.classes.linkAction.request.RedirectAction');
        $row->addAction(new LinkAction(
            'viewDocx',
            new RedirectAction($url),
            __('plugins.generic.docxViewer.button.viewDocx'),
            'preview'
        ));
    }
}

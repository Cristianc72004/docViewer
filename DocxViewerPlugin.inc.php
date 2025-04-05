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
                error_log('[docxViewer] Plugin registrado y habilitado');
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
            error_log('[docxViewer] Handler cargado correctamente');
            return true;
        }
        return false;
    }

    public function addViewButton($hookName, $params) {
        $templateMgr = $params[0];
        $resource = $params[1];
    
        if ($resource !== 'controllers/grid/gridRow.tpl') return false;
    
        $row = $templateMgr->getTemplateVars('row');
        if (!$row) {
            error_log("[docxViewer] No se encontró el objeto row");
            return false;
        }
    
        $gridId = $row->getGridId();
        $allowedGrids = [
            'submissionFiles',
            'grid-files-submission-editorsubmissiondetailsfilesgrid',
            'grid-files-review-reviewroundsubmissionfilesgrid',
            'grid-files-production-productionreadyfilesgrid',
            'grid-files-copyedit-copyeditfilesgrid',
        ];
    
        if (!in_array($gridId, $allowedGrids)) {
            error_log("[docxViewer] Grilla ignorada: $gridId");
            return false;
        }
    
        $data = $row->getData();
    
        // ✅ Verificar si es directamente un SubmissionFile
        if ($data instanceof \PKP\submission\SubmissionFile) {
            $submissionFile = $data;
        }
        // ✅ Si es array, tratar de obtener 'submissionFile'
        elseif (is_array($data) && isset($data['submissionFile']) && $data['submissionFile'] instanceof \PKP\submission\SubmissionFile) {
            $submissionFile = $data['submissionFile'];
        }
        else {
            $tipo = is_object($data) ? get_class($data) : gettype($data);
            error_log("[docxViewer] El objeto no es SubmissionFile, es de tipo: $tipo");
            return false;
        }
    
        $mimeType = strtolower($submissionFile->getData('mimetype'));
        if ($mimeType !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            error_log("[docxViewer] Archivo con MIME no soportado: $mimeType");
            return false;
        }
    
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
    
        error_log("[docxViewer] Botón agregado para archivo {$submissionFile->getId()}");
    
        return false;
    }
    
}

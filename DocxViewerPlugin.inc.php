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
            error_log('[docxViewer] No se encontró la variable row');
            return false;
        }
    
        $gridId = $row->getGridId();
        if (!str_contains($gridId, 'grid-files-')) {
            error_log("[docxViewer] Grilla ignorada: $gridId");
            return false;
        }
    
        $data = $row->getData();
    
        // 📦 Registro completo del contenido del row
        error_log('[docxViewer] Contenido completo de row->getData(): ' . print_r($data, true));
    
        // Ahora detectamos el SubmissionFile en objetos anidados
        $submissionFile = null;
    
        if ($data instanceof \PKP\submission\SubmissionFile) {
            $submissionFile = $data;
        } elseif (is_array($data)) {
            foreach ($data as $key => $valor) {
                if ($valor instanceof \PKP\submission\SubmissionFile) {
                    $submissionFile = $valor;
                    error_log("[docxViewer] SubmissionFile encontrado en clave: $key");
                    break;
                } elseif (is_object($valor)) {
                    error_log("[docxViewer] Valor en $key es objeto de tipo: " . get_class($valor));
                } else {
                    error_log("[docxViewer] Valor en $key es de tipo: " . gettype($valor));
                }
            }
        } else {
            error_log("[docxViewer] Tipo raíz inesperado: " . gettype($data));
        }
    
        if (!$submissionFile) {
            error_log('[docxViewer] No se encontró ningún SubmissionFile');
            return false;
        }
    
        $mimeType = strtolower($submissionFile->getData('mimetype'));
        if ($mimeType !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            error_log("[docxViewer] MIME no soportado: $mimeType");
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
    
        error_log("[docxViewer] Botón agregado para archivo ID={$submissionFile->getId()}");
        return false;
    }
    
}

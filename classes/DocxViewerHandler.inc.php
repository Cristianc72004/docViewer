<?php

namespace APP\plugins\generic\docxViewer;

import('lib.pkp.classes.handler.PKPHandler');

use APP\facades\Repo;
use PKP\file\PrivateFileManager;
use PKP\template\TemplateManager;

class DocxViewerHandler extends \PKPHandler {

    public function view($args, $request) {
        $user = $request->getUser();
        if (!$user) {
            error_log('[docxViewer] Usuario no autenticado. Acceso denegado.');
            die('Acceso no autorizado');
        }

        $submissionFileId = (int) $request->getUserVar('submissionFileId');
        $submissionFile = Repo::submissionFile()->get($submissionFileId);

        if (!$submissionFile) {
            error_log('[docxViewer] Archivo no encontrado con ID=' . $submissionFileId);
            die('Archivo no encontrado');
        }

        $fileManager = new PrivateFileManager();
        $filePath = $fileManager->getBasePath() . DIRECTORY_SEPARATOR . $submissionFile->getData('path');

        if (!file_exists($filePath)) {
            error_log('[docxViewer] Archivo no existe en el servidor: ' . $filePath);
            die('Archivo no disponible en el servidor');
        }

        // Generar URL temporal para el iframe
        $fileUrl = $request->getBaseUrl() . '/files/' . $submissionFile->getData('path');

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('fileUrl', $fileUrl);
        $templateMgr->assign('fileName', $submissionFile->getLocalizedData('name'));
        $templateMgr->display(__DIR__ . '/../templates/viewer.tpl');
    }
}

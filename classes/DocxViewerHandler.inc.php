<?php

namespace APP\plugins\generic\docxViewer;

// Carga necesaria para heredar PKPHandler
import('lib.pkp.classes.handler.PKPHandler');

use APP\facades\Repo;
use PKP\file\PrivateFileManager;

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

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

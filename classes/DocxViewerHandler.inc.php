<?php

namespace APP\plugins\generic\docxViewer;

// Carga la clase base del handler
import('lib.pkp.classes.handler.PKPHandler');

use APP\facades\Repo;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\file\PrivateFileManager;

class DocxViewerHandler extends \PKPHandler {

    public function authorize($request, &$args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.WorkflowStageAccessPolicy');
    
        $this->addPolicy(new \PKP\security\authorization\WorkflowStageAccessPolicy(
            $request,
            $args,
            $roleAssignments,
            'submissionId', // El nombre de la variable en la URL
            (int) $request->getUserVar('stageId') // valor stageId (ej. 1, 2, 3)
        ));
    
        return parent::authorize($request, $args, $roleAssignments);
    }
    

    public function view($args, $request) {
        $submissionFileId = (int) $request->getUserVar('submissionFileId');
        $submissionFile = Repo::submissionFile()->get($submissionFileId);

        if (!$submissionFile) {
            die('Archivo no encontrado');
        }

        $fileManager = new PrivateFileManager();
        $filePath = $fileManager->getBasePath() . DIRECTORY_SEPARATOR . $submissionFile->getData('path');

        if (!file_exists($filePath)) {
            die('Archivo no disponible en el servidor');
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

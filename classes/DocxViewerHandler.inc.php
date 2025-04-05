<?php

namespace APP\plugins\generic\docxViewer;

use PKP\core\PKPHandler;
use APP\facades\Repo;
use PKP\security\authorization\WorkflowStageAccessPolicy;
use PKP\file\PrivateFileManager;

class DocxViewerHandler extends PKPHandler {

    public function authorize($request, &$args, $roleAssignments) {
        $this->addPolicy(new WorkflowStageAccessPolicy(
            $request, $args, $roleAssignments,
            'submissionId',
            (int) $request->getUserVar('stageId')
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

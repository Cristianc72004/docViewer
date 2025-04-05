<?php

import('classes.handler.Handler');
import('lib.pkp.classes.file.PrivateFileManager');

class DocxViewerHandler extends Handler {
    function authorize($request, &$args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.WorkflowStageAccessPolicy');
        $this->addPolicy(new WorkflowStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', (int) $request->getUserVar('stageId')));
        return parent::authorize($request, $args, $roleAssignments);
    }

    function view($args, $request) {
        $submissionFileId = (int) $request->getUserVar('submissionFileId');
        $submissionFile = Services::get('submissionFile')->get($submissionFileId);
        if (!$submissionFile) die('Archivo no encontrado');

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

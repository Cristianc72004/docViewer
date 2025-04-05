<?php
namespace APP\plugins\generic\docxViewer\classes;

use PKP\core\PKPApplication;
use PKP\handler\PKPHandler;
use APP\facades\Repo;
use PKP\file\PrivateFileManager;
use PKP\security\authorization\WorkflowStageAccessPolicy;

class DocxViewerHandler extends PKPHandler {

    public function authorize($request, &$args, $roleAssignments) {
        $this->addPolicy(new WorkflowStageAccessPolicy(
            $request, $args, $roleAssignments, 'submissionId', (int) $request->getUserVar('stageId')
        ));
        return parent::authorize($request, $args, $roleAssignments);
    }

    public function view($args, $request) {
        $submissionFileId = (int) $request->getUserVar('submissionFileId');
        $submissionFile = Repo::submissionFile()->get($submissionFileId);

        if (!$submissionFile) {
            die(__('plugins.generic.docxViewer.fileNotFound'));
        }

        $filePath = (new PrivateFileManager())->getBasePath() . DIRECTORY_SEPARATOR . $submissionFile->getData('path');

        if (!file_exists($filePath)) {
            die(__('plugins.generic.docxViewer.fileMissing'));
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

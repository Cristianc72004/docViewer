<?php

namespace APP\plugins\generic\docxViewer;

import('lib.pkp.classes.handler.PKPHandler');
import('classes.template.TemplateManager'); // ✅ esta es la correcta

use APP\facades\Repo;
use PKP\file\PrivateFileManager;

class DocxViewerHandler extends \PKPHandler {
    public static $pluginInstance;

    public function view($args, $request) {
        $user = $request->getUser();
        if (!$user) {
            die('Acceso no autorizado');
        }

        $submissionFileId = (int) $request->getUserVar('submissionFileId');
        $submissionFile = Repo::submissionFile()->get($submissionFileId);

        if (!$submissionFile) {
            die('Archivo no encontrado');
        }

        $fileManager = new \PKP\file\PrivateFileManager();
        $filePath = $fileManager->getBasePath() . DIRECTORY_SEPARATOR . $submissionFile->getData('path');

        if (!file_exists($filePath)) {
            die('Archivo no disponible en el servidor');
        }

        $fileUrl = $request->getBaseUrl() . '/files/' . $submissionFile->getData('path');

        $templateMgr = \TemplateManager::getManager($request);
        $templateMgr->assign('fileUrl', $fileUrl);
        $templateMgr->assign('fileName', $submissionFile->getLocalizedData('name'));

        // ✅ Usa la instancia guardada del plugin
        $plugin = self::$pluginInstance;
        $templateMgr->display($plugin->getTemplateResource('viewer.tpl'));
    }
}

    


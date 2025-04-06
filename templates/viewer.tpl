<!DOCTYPE html>
<html>
<head>
    <title>{$fileName|escape}</title>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        .viewer-header {
            padding: 15px;
            background: #006699;
            color: white;
            font-size: 18px;
            font-weight: bold;
        }

        .viewer-container {
            width: 100%;
            height: calc(100vh - 60px);
            border: none;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .viewer-footer {
            padding: 15px;
            background: #eee;
            text-align: center;
            font-size: 14px;
        }

        .viewer-footer a {
            color: #006699;
            text-decoration: none;
            font-weight: bold;
        }

        input.url-box {
            width: 80%;
            padding: 8px;
            font-size: 13px;
            margin-top: 8px;
        }

    </style>
</head>
<body>
    <div class="viewer-header">
        {$fileName|escape}
    </div>

    <div class="viewer-container">
        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={$fileUrl|escape:"url"}" onerror="document.getElementById('viewer-error').style.display='block'"></iframe>
    </div>

    <div class="viewer-footer" id="viewer-error" style="display: none;">
        <p>⚠️ Este archivo no se pudo visualizar porque no es accesible públicamente.</p>
        <p>
            Puedes descargarlo directamente <a href="{$fileUrl|escape:"url"}" target="_blank">aquí</a> o copiar su URL para abrirlo en un visor compatible.
        </p>
        <input type="text" class="url-box" readonly value="{$fileUrl|escape:"url"}" onclick="this.select();" />
    </div>
</body>
</html>

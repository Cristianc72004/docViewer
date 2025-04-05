<!DOCTYPE html>
<html>
<head>
    <title>{$fileName|escape}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f2f2f2;
            font-family: Arial, sans-serif;
        }

        .viewer-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            z-index: 9999;
            background: #fff;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .viewer-header {
            padding: 10px;
            background: #006699;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="viewer-header">
        {$fileName|escape}
    </div>
    <div class="viewer-container">
        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={$fileUrl|escape:"url"}"></iframe>
    </div>
</body>
</html>

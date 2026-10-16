<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $assetCode }}</title>
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; }
        html, body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #000000;
        }
        table.label {
            width: 288pt;
            height: 432pt;
            border-collapse: collapse;
            background: #ffffff;
        }
        td.upper {
            width: 288pt;
            height: 302pt;
            text-align: center;
            vertical-align: middle;
            background: #ffffff;
        }
        td.lower {
            width: 288pt;
            height: 130pt;
            background: #ffffff;
        }
        img.qr {
            display: block;
            width: 180pt;
            height: 180pt;
            margin: 0 auto;
        }
        .code {
            font-family: DejaVu Sans, sans-serif;
            font-weight: bold;
            font-size: 16pt;
            line-height: 1.2;
            color: #000000;
            text-align: center;
            margin-top: 12pt;
        }
    </style>
</head>
<body>
    <table class="label" cellspacing="0" cellpadding="0">
        <tr>
            <td class="upper">
                <img class="qr" src="{{ $qrDataUri }}" alt="">
                <div class="code">{{ $assetCode }}</div>
            </td>
        </tr>
        <tr>
            <td class="lower"></td>
        </tr>
    </table>
</body>
</html>

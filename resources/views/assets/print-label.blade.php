<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Asset label {{ $assetCode }}</title>
    <style>
        @page {
            size: 50mm 50mm;
            margin: 0;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
        }

        .toolbar {
            max-width: 440px;
            margin: 16px auto 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #333;
        }

        .toolbar button {
            font: inherit;
            padding: 6px 12px;
            cursor: pointer;
        }

        .toolbar p {
            margin: 8px 0 0;
            line-height: 1.4;
        }

        .sheet {
            width: 50mm;
            height: 50mm;
        }

        .label {
            width: 50mm;
            height: 50mm;
            padding: 2mm 2mm 1.5mm;
            overflow: hidden;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .qr {
            width: 34mm;
            height: 34mm;
            display: block;
        }

        .code {
            margin-top: 1.4mm;
            max-width: 46mm;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: 0.01em;
            word-break: break-word;
        }

        @media screen {
            body { background: #eceff1; }
            .sheet {
                margin: 12px auto 24px;
                box-shadow: 0 1px 6px rgba(0, 0, 0, 0.18);
            }
        }

        @media print {
            .no-print { display: none !important; }
            html, body {
                width: 50mm;
                height: 50mm;
                background: #fff;
            }
            .sheet { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" onclick="window.print()">Print label</button>
        <p>
            Paper size <strong>50 mm × 50 mm</strong>, margins <strong>None</strong>, scale <strong>100%</strong>.
            Turn off headers and footers. Set the label printer gap to 3 mm.
        </p>
    </div>

    <div class="sheet">
        <div class="label">
            <img class="qr" src="{{ $qrDataUri }}" alt="">
            <div class="code">{{ $assetCode }}</div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
</body>
</html>

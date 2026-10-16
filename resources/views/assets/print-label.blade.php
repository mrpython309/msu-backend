<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title> </title>
    <style>
        @page {
            size: 50mm 48mm;
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
            max-width: 460px;
            margin: 16px auto 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #333;
        }

        .toolbar button,
        .toolbar a.button {
            font: inherit;
            padding: 6px 12px;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            background: #696cff;
            border: 0;
            border-radius: 4px;
            display: inline-block;
        }

        .toolbar a.secondary {
            color: #333;
            background: #fff;
            border: 1px solid #c5c5c5;
        }

        .toolbar p,
        .message {
            margin: 8px 0 0;
            line-height: 1.4;
        }

        .message.success { color: #146c43; }
        .message.error { color: #b02a37; }
        .message.warning { color: #997404; }

        .copies {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-right: 8px;
        }

        .copies input {
            width: 64px;
            font: inherit;
            padding: 4px 6px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
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
            .no-print,
            template,
            script {
                display: none !important;
            }
            html, body {
                width: 50mm;
                height: auto;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff;
            }
            html.single-label,
            html.single-label body {
                height: 50mm;
                overflow: hidden;
            }
            html.single-label #sheets {
                position: absolute;
                top: 0;
                left: 0;
                width: 50mm;
                height: 49mm;
                overflow: hidden;
            }
            .sheet {
                width: 50mm;
                height: 49mm;
                margin: 0;
                padding: 0;
                box-shadow: none;
                overflow: hidden;
                break-inside: avoid;
                page-break-inside: avoid;
                break-after: avoid;
                page-break-after: avoid;
            }
            .label {
                width: 50mm;
                height: 49mm;
            }
            .sheet:not(:last-child) {
                break-after: page;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        @if (session('success'))
            <p class="message success" role="status">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="message error" role="alert">{{ session('error') }}</p>
        @endif
        @if ($setupWarning)
            <p class="message warning" role="status">{{ $setupWarning }}</p>
        @endif

        <form method="POST" action="{{ route('asset.print-label.send', $asset) }}" class="actions" id="label-form"@if ($directPrinter === null) onsubmit="return false;"@endif>
            @csrf
            <label class="copies">
                Copies
                <input id="copies" name="copies" type="number" min="1" max="{{ $maxCopies }}" value="{{ $copies }}" required>
            </label>

            @if ($directPrinter)
                <button type="submit" id="print-button">Print label</button>
            @else
                <button type="button" id="print-button">Print label</button>
            @endif

            <a class="button secondary" id="download-link" href="{{ route('asset.print-label.commands', ['asset' => $asset, 'copies' => $copies]) }}">Download printer file</a>
        </form>

        <p id="print-status" class="message" role="status"></p>
        @if ($directPrinter)
            <p>
                Print sends {{ $copies === 1 ? 'one label' : $copies.' labels' }} for <strong>{{ $assetCode }}</strong>
                to <strong>{{ $directPrinter['target'] }}</strong>.
                The printer job is a 50 mm × 50 mm label with a 3 mm gap.
            </p>
        @else
            <p>
                Paper size <strong>50 mm × 50 mm</strong>, margins <strong>None</strong>, scale <strong>100%</strong>.
                Turn off headers and footers. Set the label gap to 3 mm.
            </p>
        @endif
    </div>

    <div id="sheets">
        <div class="sheet">
            <div class="label">
                <img class="qr" src="{{ $qrDataUri }}" alt="">
                <div class="code">{{ $assetCode }}</div>
            </div>
        </div>
    </div>

    <template id="sheet-template">
        <div class="sheet">
            <div class="label">
                <img class="qr" src="{{ $qrDataUri }}" alt="">
                <div class="code">{{ $assetCode }}</div>
            </div>
        </div>
    </template>

    <script>
        (function () {
            var assetCode = @json($assetCode);
            var maxCopies = {{ (int) $maxCopies }};
            var directPrint = @json($directPrinter !== null);
            var input = document.getElementById('copies');
            var sheets = document.getElementById('sheets');
            var template = document.getElementById('sheet-template');
            var status = document.getElementById('print-status');
            var download = document.getElementById('download-link');
            var downloadUrl = @json(route('asset.print-label.commands', $asset));

            function copies() {
                var value = parseInt(input.value, 10);
                if (!value || value < 1) {
                    value = 1;
                }
                if (value > maxCopies) {
                    value = maxCopies;
                }
                return value;
            }

            function syncSheets() {
                var count = copies();
                input.value = String(count);
                document.documentElement.classList.toggle('single-label', count === 1);
                sheets.replaceChildren();
                for (var i = 0; i < count; i++) {
                    sheets.appendChild(template.content.cloneNode(true));
                }
                download.href = downloadUrl + '?copies=' + encodeURIComponent(count);
            }

            input.addEventListener('change', syncSheets);
            syncSheets();

            function printDocument() {
                var count = copies();
                var sheetHtml = '';
                for (var i = 0; i < count; i++) {
                    sheetHtml += template.innerHTML;
                }

                var frame = document.getElementById('label-print-frame');
                if (!frame) {
                    frame = document.createElement('iframe');
                    frame.id = 'label-print-frame';
                    frame.setAttribute('aria-hidden', 'true');
                    frame.style.cssText = 'position:fixed;left:-120mm;top:0;width:50mm;height:50mm;border:0;';
                    document.body.appendChild(frame);
                }

                var doc = frame.contentWindow.document;
                doc.open();
                doc.write(
                    '<!DOCTYPE html><html><head><meta charset="utf-8"><title></title><style>' +
                    '@page{size:50mm 48mm;margin:0;}' +
                    '*{margin:0;padding:0;}' +
                    'html,body{width:50mm;background:#fff;}' +
                    'body{height:' + (count === 1 ? '46mm' : 'auto') + ';overflow:hidden;}' +
                    '.sheet,.label{width:50mm;height:46mm;overflow:hidden;box-sizing:border-box;}' +
                    '.label{padding:2mm 2mm 1.5mm;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;}' +
                    '.qr{width:30mm;height:30mm;display:block;}' +
                    '.code{margin-top:1mm;max-width:46mm;font-family:Arial,Helvetica,sans-serif;font-size:8pt;font-weight:700;line-height:1.1;word-break:break-word;}' +
                    (count > 1 ? '.sheet{break-after:page;page-break-after:always;}.sheet:last-child{break-after:auto;page-break-after:auto;}' : '') +
                    '</style></head><body>' + sheetHtml + '</body></html>'
                );
                doc.close();

                var pending = 0;
                Array.prototype.forEach.call(doc.images, function (image) {
                    if (image.complete) {
                        return;
                    }
                    pending++;
                    image.onload = image.onerror = function () {
                        pending--;
                        if (pending === 0) {
                            frame.contentWindow.focus();
                            frame.contentWindow.print();
                        }
                    };
                });

                if (pending === 0) {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                }

                status.textContent = 'Print dialog opened for ' + count + ' label' + (count === 1 ? '' : 's') + ' of ' + assetCode + '. Use 50 mm paper, no headers, and no footers.';
            }

            if (!directPrint) {
                document.getElementById('print-button').addEventListener('click', function () {
                    syncSheets();
                    printDocument();
                });

                window.addEventListener('load', function () {
                    setTimeout(printDocument, 300);
                });
            }
        })();
    </script>
</body>
</html>

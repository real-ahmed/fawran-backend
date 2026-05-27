<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.courier_contract') }} - {{ $contractNumber }}</title>
    <!-- Use Google Fonts for premium typography -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e293b;
            --accent-color: #0ea5e9;
            --text-main: #334155;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --bg-color: #f8fafc;
        }
        
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            line-height: 1.8;
            color: var(--text-main);
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
        }

        /* Print Button Styles */
        .action-bar {
            background-color: #ffffff;
            padding: 15px 0;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            margin-bottom: 30px;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 28px;
            background-color: var(--primary-color);
            color: #ffffff;
            font-family: 'Tajawal', sans-serif;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .print-btn:hover {
            background-color: #0f172a;
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .print-btn svg {
            width: 20px;
            height: 20px;
        }

        /* Document Container */
        .container {
            max-width: 210mm; /* A4 width */
            margin: 0 auto;
            background: #ffffff;
            padding: 40px 50px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            position: relative;
        }

        /* Header Layout */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px double var(--border-color);
        }

        .header-logo {
            flex: 0 0 auto;
        }

        .header-logo img {
            max-height: 80px;
            max-width: 180px;
            object-fit: contain;
        }

        .header-company-name {
            flex: 1;
            text-align: left; /* Because RTL, left is right physically, actually let's align center if no logo or left */
            padding-right: 20px;
        }

        .header-company-name h1 {
            font-family: 'Amiri', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0 0 5px 0;
        }

        .contract-meta {
            text-align: left;
            font-size: 13px;
            color: var(--text-muted);
        }

        .contract-meta p {
            margin: 2px 0;
        }

        /* Contract Body Styles */
        .contract-body {
            font-family: 'Amiri', serif;
            font-size: 16px;
            text-align: justify;
            text-justify: inter-word;
        }

        .contract-body h2, .contract-body h3 {
            font-family: 'Tajawal', sans-serif;
            color: var(--primary-color);
            margin-top: 30px;
            margin-bottom: 15px;
            position: relative;
        }

        .contract-body h2 {
            text-align: center;
            font-size: 24px;
            text-decoration: underline;
            text-underline-offset: 8px;
            margin-bottom: 40px;
        }

        .contract-body h3 {
            font-size: 18px;
            background-color: var(--bg-color);
            padding: 8px 15px;
            border-right: 4px solid var(--accent-color);
            border-radius: 4px 0 0 4px;
        }

        .contract-body p {
            margin-bottom: 15px;
        }

        .contract-body ul, .contract-body ol {
            margin-bottom: 20px;
            padding-right: 30px;
        }

        .contract-body li {
            margin-bottom: 8px;
        }

        .contract-body strong {
            font-family: 'Tajawal', sans-serif;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Signatures Section */
        .signatures {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            padding-top: 30px;
            border-top: 1px solid var(--border-color);
        }

        .signature-block {
            flex: 1;
            text-align: center;
        }

        .signature-block h4 {
            font-family: 'Tajawal', sans-serif;
            font-size: 16px;
            color: var(--primary-color);
            margin: 0 0 40px 0;
        }

        .signature-line {
            width: 80%;
            margin: 0 auto;
            border-bottom: 1px dashed var(--text-muted);
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            font-family: 'Tajawal', sans-serif;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.03;
            pointer-events: none;
            z-index: 0;
            text-align: center;
        }

        .watermark img {
            max-width: 400px;
            max-height: 400px;
        }
        
        .watermark h1 {
            font-size: 120px;
            white-space: nowrap;
        }

        .contract-content {
            position: relative;
            z-index: 1;
        }

        /* Print Media Queries */
        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }
            
            body {
                background-color: #ffffff;
            }
            
            .action-bar {
                display: none;
            }
            
            .container {
                box-shadow: none;
                padding: 0;
                margin: 0;
                width: 100%;
                max-width: 100%;
                border-radius: 0;
            }

            .contract-body h3 {
                background-color: #f1f5f9 !important; /* Force background in print */
            }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <button onclick="window.print()" class="print-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            {{ __('messages.print_contract') }}
        </button>
    </div>

    <div class="container">
        <!-- Watermark -->
        <div class="watermark">
            @if($appLogo)
                <img src="{{ $appLogo }}" alt="Watermark">
            @else
                <h1>{{ $appName }}</h1>
            @endif
        </div>

        <div class="contract-content">
            <div class="header">
                <div class="header-logo">
                    @if($appLogo)
                        <img src="{{ $appLogo }}" alt="{{ $appName }}">
                    @else
                        <!-- Fallback if no logo -->
                        <div style="font-family:'Tajawal', sans-serif; font-size:24px; font-weight:800; color:#0ea5e9;">{{ $appName }}</div>
                    @endif
                </div>
                
                <div class="header-company-name">
                    <h1>{{ $appName }}</h1>
                    <div class="contract-meta">
                        <p><strong>{{ __('messages.reference_number') }}</strong> {{ $contractNumber }}</p>
                        <p><strong>{{ __('messages.issue_date') }}</strong> {{ date('Y-m-d') }}</p>
                    </div>
                </div>
            </div>

            <div class="contract-body">
                {!! $html !!}
            </div>

            <div class="footer">
                <p>{{ __('messages.contract_issued_by', ['app_name' => $appName]) }}</p>
                <p>{{ __('messages.print_date') }} {{ date('Y-m-d H:i:s') }}</p>
            </div>
        </div>
    </div>

    <script>
        // Optional: Auto open print dialog when page loads
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 800);
        // }
    </script>
</body>
</html>

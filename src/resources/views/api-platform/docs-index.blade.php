<!DOCTYPE html>
<html>
<head>
    <title>Bagisto API Documentation</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
        }
        
        .container {
            padding: 40px 20px; 
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: #fff;
            margin-bottom: 80px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #fff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .logo img {
            width: 50px;
            height: 50px;
        }
        
        .header h1 {
            font-size: 3.5em;
            margin-bottom: 10px;
            font-weight: 800;
            letter-spacing: -1px;
        }
        
        .header p {
            font-size: 1.2em;
            opacity: 0.95;
            font-weight: 300;
            margin-bottom: 30px;
        }
        
        .header-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 2px solid #fff;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #fff;
            color: #667eea;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-secondary {
            background: transparent;
            color: #fff;
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .btn-secondary:hover {
            border-color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .section-title {
            color: #fff;
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section {
            margin-bottom: 80px;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 40px 30px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            border-color: rgba(102, 126, 234, 0.2);
        }
        
        .card:hover::before {
            width: 8px;
        }
        
        .card-icon {
            font-size: 3.5em;
            margin-bottom: 20px;
            display: block;
            line-height: 1;
        }
        
        .card h2 {
            font-size: 1.8em;
            margin-bottom: 12px;
            color: #333;
            font-weight: 700;
        }
        
        .card p {
            color: #666;
            font-size: 1em;
            line-height: 1.6;
            margin-bottom: 24px;
            flex-grow: 1;
        }
        
        .card-link {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            align-self: flex-start;
        }
        
        .card:hover .card-link {
            transform: translateX(4px);
        }
        
        .card-link:active {
            transform: scale(0.95);
        }
        
        .playground-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .playground-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 30px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .playground-card:hover {
            transform: translateY(-8px);
            border-color: #fff;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }
        
        .playground-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
        
        .playground-card h3 {
            font-size: 1.3em;
            color: #333;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .playground-card p {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
        }
        
        .playground-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        .playground-btn:hover {
            transform: scale(1.05);
        }
        
        .footer {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .footer a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5em;
            }
            
            .header p {
                font-size: 1em;
            }
            
            .header-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .section-title {
                font-size: 1.4em;
            }
            
            .cards-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        .bs-download-header {
            background-color: #030e1a;
            width: 100%;
            min-height: 100%;
            height: 100vh;
            position: relative;
            display: table;
            overflow: hidden;
        }

        .bs-download-header:before {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            content: url({{ asset('themes/admin/default/assets/images/left-gradient.webp') }});
        }

        .bs-download-header:after {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            content: url({{ asset('themes/admin/default/assets/images/right-gradient.webp') }});
        }
    </style>
</head>
<body>
    <section class="bs-download-header">
        <div class="container ">
            <div class="header">
                <div class="logo">
                    <img src="{{ asset('themes/admin/default/assets/images/top-logo.svg') }}" alt="Bagisto API Logo" width="150" height="60">
                </div>
                <h1>Bagisto API</h1>
                <p>Comprehensive REST & GraphQL API Documentation</p>
                <div class="header-actions">
                    <a href="{{ $documentation_url }}" target="_blank" class="btn btn-primary">
                        <img src="{{ asset('themes/admin/default/assets/images/document.svg') }}" alt="Document Icon" width="20" height="20">
                        View Full Documentation
                    </a>
                </div>
            </div>
            
            <!-- REST API Section -->
            <div class="section">
                <div class="section-title">                
                    REST API Playgrounds</div>
                <div class="cards-grid">
                    @foreach($rest_apis as $api)
                    <a href="{{ $api['url'] }}" class="card">
                        <span class="card-icon"><img src="{{ asset('themes/admin/default/assets/images/' . $api['icon']) }}" alt="{{ $api['name'] }} Icon" width="48" height="48"></span>
                        <h2>{{ $api['name'] }}</h2>
                        <p>{{ $api['description'] }}</p>
                        <span class="card-link">Explore API →</span>
                    </a>
                    @endforeach
                </div>
            </div>
            
            <!-- GraphQL Section -->
            <div class="section" id="rest-playground">
                <div class="section-title">GraphQL Playground</div>
                <div class="playground-cards">
                    <a href="{{ $graphql_playground_url }}" class="playground-card">
                        <div class="playground-icon"><img src="{{ asset('themes/admin/default/assets/images/graph-QL.svg') }}" alt="GraphQL Icon" width="80" height="80"></div>

                        <h3>GraphQL Explorer</h3>
                        <p>Interactive GraphQL IDE with auto-completion, schema exploration, and real-time queries</p>
                        <span class="playground-btn">Open Playground →</span>
                    </a>
                </div>
            </div>
            

            
            <div class="footer">
                <p>Bagisto API Documentation • Powered by <a href="https://api-platform.com" target="_blank">API Platform</a> & <a href="https://bagisto.com" target="_blank">Bagisto</a></p>
            </div>
        </div>
    </section>
</body>
</html>

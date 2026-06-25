<head>
  <title>Webstimate</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>
    body { color: #333; }
    .jumbotron { padding-top: 32px; padding-bottom: 32px; margin-bottom: 0; background: #f7f9fc; border-bottom: 1px solid #e3e8ef; }
    .jumbotron h1 { font-weight: 600; letter-spacing: -0.3px; }
    .jumbotron p { margin: 10px 0 0; font-size: 17px; color: #555; }
    .landing-main { max-width: 720px; margin: 0 auto; padding: 36px 15px 48px; }
    .app-card { background: #fafbfc; border: 1px solid #e3e8ef; border-radius: 6px; padding: 24px 22px; margin-bottom: 20px; }
    .app-card h2 { margin-top: 0; margin-bottom: 10px; font-size: 22px; font-weight: 600; }
    .app-card p { font-size: 16px; line-height: 1.6; margin-bottom: 16px; color: #444; }
    .app-card .btn { min-width: 140px; }
    .page-footer { padding: 16px 0 32px; font-size: 12px; color: #999; text-align: center; }
  </style>
</head>
<body>

<div class="jumbotron text-center">
  <h1>Webstimate</h1>
  <p>Online apps for statistical analysis</p>
</div>

<div class="landing-main">
  <div class="app-card">
    <h2>Two-lines test</h2>
    <p>Test for u-shaped (or inverted u-shaped) relationships using the two-lines procedure (Simonsohn, 2018). Upload data, specify a regression model, and get a publication-ready figure.</p>
    <a href="twolines/" class="btn btn-success btn-lg">Open two-lines test</a>
  </div>

  <div class="app-card">
    <h2>Johnson-Neyman 2.0</h2>
    <p>Probe interactions with GAM simple slopes and Johnson-Neyman curves. Upload data, select focal predictor, moderator, and outcome, then run <code>interprobe</code> from the R package <code>statuser</code>.</p>
    <a href="interprobe/" class="btn btn-success btn-lg">Open Johnson-Neyman 2.0</a>
  </div>
</div>

<div class="page-footer">webstimate.org</div>
</body>
</html>

<head>
  <title>Interprobe</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>
    body { color: #333; }
    .jumbotron { padding-top: 28px; padding-bottom: 28px; margin-bottom: 0; background: #f7f9fc; border-bottom: 1px solid #e3e8ef; }
    .jumbotron h1 { font-weight: 600; letter-spacing: -0.5px; }
    .landing-main { max-width: 720px; margin: 0 auto; padding: 32px 15px 24px; font-size: 16px; line-height: 1.6; }
    .landing-main p { margin-bottom: 14px; }
    .landing-main ol { padding-left: 22px; margin-bottom: 18px; }
    .landing-main ol li { margin-bottom: 6px; }
    .landing-main .refs { font-size: 15px; color: #444; }
    .upload-section { max-width: 520px; margin: 0 auto; text-align: center; padding: 8px 0 24px; }
    .upload-section h3 { margin-top: 0; margin-bottom: 18px; font-size: 20px; font-weight: 600; }
    .upload-panel { background: #fafbfc; border: 1px solid #e3e8ef; border-radius: 6px; padding: 24px 20px; }
    .upload-panel input[type="file"] { display: inline-block; margin: 10px 0 14px; max-width: 100%; }
    .upload-panel .btn { min-width: 120px; }
    .upload-hint { margin-top: 12px; font-size: 14px; color: #666; }
    .privacy-block { max-width: 640px; margin: 8px auto 0; }
    .page-footer { margin-top: 24px; padding: 16px 0 32px; font-size: 12px; color: #999; text-align: center; }
  </style>
</head>
<body>

<div class="jumbotron text-center">
  <h1>Interprobe</h1>
</div>

<div class="landing-main">
  <p>This online app allows you to run GAM probing of interactions, computing GAM Simple Slopes and GAM Johnson-Neyman.</p>

  <p><strong>To proceed:</strong></p>
  <ol>
    <li>Upload the data</li>
    <li>Select a focal predictor, moderator, and dependent variable from the list of variables in it</li>
    <li>Click the Run button</li>
    <li>Get publication-ready figures with the probing</li>
  </ol>

  <p>The server runs the function <code>interprobe</code> from the R package <code>statuser</code>. The results you get here will be identical to those you would obtain using R (with the same version of all software involved).</p>

  <div class="refs">
    <p><strong>For a tutorial see</strong><br>
    Montealegre &amp; Simonsohn (2026) &ldquo;Johnson-Neyman 2.0&rdquo;, under review, <em>Journal of Consumer Research</em></p>

    <p><strong>For background see</strong><br>
    Simonsohn (2024) &ldquo;Interacting with Curves&rdquo;, <em>Advances in Methods and Practices in Psychological Science</em>.
    <a href="https://doi.org/10.1177/25152459231207787">https://doi.org/10.1177/25152459231207787</a></p>
  </div>
</div>

<hr>

<div class="upload-section">
  <h3>Upload your data</h3>
  <div class="upload-panel">
    <form action="upload.php" method="post" enctype="multipart/form-data">
      <input type="file" name="fileToUpload" id="fileToUpload">
      <br>
      <input type="submit" value="Upload" name="Submit" class="btn btn-success btn-lg">
    </form>
    <p class="upload-hint">No file handy? Download this <a href="example.csv">example datafile</a> and upload it.</p>
  </div>
</div>

<div class="privacy-block">
  <div class="alert alert-danger text-center">
    <h4 class="alert-heading">Data privacy information</h4>
    <p style="margin-bottom: 0; font-size: 13px;">
    Uploaded data is deleted within 72 hours. Files are saved unencrypted in a public
    folder but given a temporary name, so they are hard to find but not impossible to locate.
    For confidential data, run the analysis locally in R instead of uploading here.
    </p>
  </div>
</div>

<?
  $dir1 = "/home/urisoh5/uploaded_data/webstimate.org/interprobe/temp/";
  foreach (glob($dir1."*") as $file) {
    if (filemtime($file) < time() - 24*3*60) {
      unlink($file);
    }
  }

  $dir2 = "./temp/";
  foreach (glob($dir2."*") as $file) {
    if (filemtime($file) < time() - 60*3*24) {
      unlink($file);
    }
  }
?>

<div class="page-footer">Thanks for using Interprobe</div>
</body>
</html>

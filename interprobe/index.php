<?php
require_once __DIR__ . '/../includes/turnstile.php';
$turnstile_site_key = turnstile_site_key();
?>
<head>
  <title>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  <style>
    body { color: #333; }
    .jumbotron { padding-top: 28px; padding-bottom: 28px; margin-bottom: 0; background: #f7f9fc; border-bottom: 1px solid #e3e8ef; }
    .jumbotron h1 { font-size: 26px; line-height: 1.35; font-weight: 600; letter-spacing: -0.3px; }
    .landing-main { max-width: 720px; margin: 0 auto; padding: 32px 15px 24px; font-size: 16px; line-height: 1.6; }
    .landing-main p { margin-bottom: 14px; }
    .landing-main ol { padding-left: 22px; margin-bottom: 18px; }
    .landing-main ol li { margin-bottom: 6px; }
    .landing-main .refs { font-size: 15px; color: #444; }
    .upload-section { max-width: 400px; margin: 0 auto; text-align: center; padding: 4px 0 12px; }
    .upload-section h3 { margin-top: 0; margin-bottom: 10px; font-size: 18px; font-weight: 600; }
    .upload-panel { background: #fafbfc; border: 3px dashed #337ab7; border-radius: 6px; padding: 14px 16px; transition: border-color 0.15s, background 0.15s; cursor: pointer; }
    .upload-panel.drag-over { border-color: #23527c; background: #eef5fc; }
    .upload-icon { font-size: 34px; color: #337ab7; display: block; margin-bottom: 6px; line-height: 1; }
    .drop-prompt { padding: 0; color: #666; }
    .drop-prompt p { margin-bottom: 4px; font-size: 14px; }
    .choose-file-link { color: #337ab7; cursor: pointer; font-size: 14px; font-weight: normal; margin-bottom: 0; text-decoration: underline; }
    .file-name { font-size: 13px; color: #333; margin: 6px 0 0; font-weight: 600; min-height: 16px; }
    .upload-hint { margin-top: 10px; font-size: 13px; color: #666; }
    .turnstile-wrap { height: 0; overflow: hidden; }
    .privacy-block { max-width: 640px; margin: 8px auto 0; }
    .page-footer { margin-top: 24px; padding: 16px 0 32px; font-size: 12px; color: #999; text-align: center; }
  </style>
</head>
<body>

<div class="jumbotron text-center">
  <h1>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</h1>
</div>

<div class="landing-main">
  <p>This online app allows you to run GAM probing of interactions, computing GAM Simple Slopes and GAM Johnson-Neyman. It uses the function <code>interprobe</code> in the R package <a href="https://github.com/urisohn/statuser">statuser</a> for calculations.</p>

  <p><strong>To proceed:</strong></p>
  <ol>
    <li>Upload the data</li>
    <li>Select a focal predictor, moderator, and dependent variable from the list of variables in it</li>
    <li>Click the Run button</li>
    <li>Get publication-ready figures</li>
  </ol>

  <div class="refs">
    <p><strong>For a tutorial see</strong><br>
    Montealegre &amp; Simonsohn (2026) &ldquo;Johnson-Neyman 2.0&rdquo;, under review, <em>Journal of Consumer Research</em></p>

    <p><strong>For background see</strong><br>
    Simonsohn, U. (2024). Interacting with curves: How to validly test and probe interactions in the real (nonlinear) world. <em>Advances in Methods and Practices in Psychological Science</em>, <em>7</em>(1), 1&ndash;22. <a href="https://doi.org/10.1177/25152459231207787">https://doi.org/10.1177/25152459231207787</a></p>
  </div>
</div>

<hr>

<div class="upload-section">
  <h3>Upload your data</h3>
  <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
    <div class="upload-panel" id="uploadDropzone">
      <span class="glyphicon glyphicon-cloud-upload upload-icon" aria-hidden="true"></span>
      <div class="drop-prompt">
        <p>Drag and drop your file here</p>
        <label for="fileToUpload" class="choose-file-link">Choose file</label>
        <input type="file" name="fileToUpload" id="fileToUpload" style="display:none">
        <p class="file-name" id="fileName"></p>
      </div>
    </div>
    <div class="turnstile-wrap">
      <div id="turnstileWidget"></div>
    </div>
  </form>
  <p class="upload-hint">No file handy? Download this <a href="example.csv">example datafile</a> and upload it.</p>
</div>

<script>
(function () {
  var dropzone = document.getElementById("uploadDropzone");
  var form = document.getElementById("uploadForm");
  var fileInput = document.getElementById("fileToUpload");
  var fileName = document.getElementById("fileName");
  var pendingFile = null;
  var turnstileWidgetId = null;

  function getTurnstileToken() {
    var el = document.querySelector('[name="cf-turnstile-response"]');
    return el ? el.value : "";
  }

  function submitPendingFile() {
    if (!pendingFile) return;
    var dt = new DataTransfer();
    dt.items.add(pendingFile);
    fileInput.files = dt.files;
    fileName.textContent = "Uploading " + pendingFile.name + "\u2026";
    pendingFile = null;
    form.submit();
  }

  function queueFile(file) {
    if (!file) return;
    pendingFile = file;
    if (getTurnstileToken()) {
      submitPendingFile();
      return;
    }
    fileName.textContent = "Verifying\u2026";
    if (typeof turnstile === "undefined") {
      fileName.textContent = "Security check failed to load. Please refresh the page.";
      pendingFile = null;
      return;
    }
    turnstile.ready(function () {
      if (turnstileWidgetId === null) {
        turnstileWidgetId = turnstile.render("#turnstileWidget", {
          sitekey: "<?php echo htmlspecialchars($turnstile_site_key, ENT_QUOTES, 'UTF-8'); ?>",
          size: "invisible",
          callback: onTurnstileSuccess
        });
      }
      turnstile.execute(turnstileWidgetId);
    });
  }

  window.onTurnstileSuccess = function () {
    submitPendingFile();
  };

  fileInput.addEventListener("change", function () {
    if (fileInput.files.length) queueFile(fileInput.files[0]);
  });

  dropzone.addEventListener("click", function (e) {
    if (e.target.classList.contains("choose-file-link")) return;
    fileInput.click();
  });

  ["dragenter", "dragover"].forEach(function (eventName) {
    dropzone.addEventListener(eventName, function (e) {
      e.preventDefault();
      e.stopPropagation();
      dropzone.classList.add("drag-over");
    });
  });

  ["dragleave", "drop"].forEach(function (eventName) {
    dropzone.addEventListener(eventName, function (e) {
      e.preventDefault();
      e.stopPropagation();
      dropzone.classList.remove("drag-over");
    });
  });

  dropzone.addEventListener("drop", function (e) {
    var files = e.dataTransfer.files;
    if (files.length) queueFile(files[0]);
  });
})();
</script>

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

<div class="page-footer">Last updated: <? $date = "2025 06 25"; echo $date; ?></div>
</body>
</html>

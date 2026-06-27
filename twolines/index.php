<?php
require_once __DIR__ . '/../includes/turnstile.php';
$turnstile_site_key = turnstile_site_key();
?>
<head>
  <title>Two-lines test</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  <style>
    body { color: #333; }
    .jumbotron { padding-top: 28px; padding-bottom: 28px; margin-bottom: 0; background: #f7f9fc; border-bottom: 1px solid #e3e8ef; }
    .jumbotron h1 { font-size: 32px; line-height: 1.35; font-weight: 600; letter-spacing: -0.3px; }
    .landing-main { max-width: 720px; margin: 0 auto; padding: 32px 15px 24px; font-size: 16px; line-height: 1.6; }
    .landing-main p { margin-bottom: 14px; }
    .landing-main ol { padding-left: 22px; margin-bottom: 18px; }
    .landing-main ol li { margin-bottom: 6px; }
    .landing-main .refs { font-size: 15px; color: #444; }
    .example-figure { text-align: center; margin: 20px 0 8px; }
    .r-links { margin-top: 18px; }
    .r-links .btn { margin: 4px 6px 4px 0; }
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
  <h1>Two-lines test</h1>
</div>

<div class="landing-main">
  <p>This app runs the u-shape test introduced by Simonsohn (2017 <a href="http://urisohn.com/sohn_files/wp/wordpress/wp-content/uploads/2019/01/two-lines-u-shape-published.pdf">.pdf</a>).</p>

  <p>It estimates an interrupted regression&mdash;two separate slopes&mdash;for the predictor hypothesized to have a u-shaped effect. The breakpoint is set using the &ldquo;Robin Hood&rdquo; algorithm, seeking higher power to detect a u-shape if it is present. If the resulting two slopes have opposite sign and are individually statistically significant, the test rejects the null that there is no u-shaped (nor inverted u-shaped) effect.</p>

  <p><strong>To proceed:</strong></p>
  <ol>
    <li>Upload the data</li>
    <li>Preview the file and enter the regression model to test</li>
    <li>Get publication-ready figures and results</li>
  </ol>

  <p class="example-figure"><strong>Example output:</strong><br>
  <a href="twolines.png" target="_blank"><img src="twolines.png" width="400" alt="Example two-lines chart"></a></p>

  <div class="refs r-links">
    <p><strong>If you know R:</strong></p>
    <a href="example.r" class="btn btn-primary">See example</a>
    <a href="http://webstimate.org/twolines/twolines.R" class="btn btn-success">Download the R code</a>
    <p style="margin-top: 14px; font-size: 14px; color: #666;">See <a href="changes.php">how the app has changed</a> over time.</p>
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
    if (turnstileWidgetId === null) {
      turnstileWidgetId = turnstile.render("#turnstileWidget", {
        sitekey: "<?php echo htmlspecialchars($turnstile_site_key, ENT_QUOTES, 'UTF-8'); ?>",
        size: "invisible",
        callback: onTurnstileSuccess
      });
    }
    turnstile.execute(turnstileWidgetId);
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
    For confidential data, download the R code and run locally instead of uploading here.
    </p>
  </div>
</div>

<?
  $dir1 = "/home/urisoh5/uploaded_data/webstimate.org/twolines/temp/";
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

<div class="page-footer">Thanks for using the two-lines test</div>
</body>
</html>

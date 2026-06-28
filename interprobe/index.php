<?php
require_once __DIR__ . '/../includes/turnstile.php';
require_once __DIR__ . '/../includes/upload_limits.php';
$turnstile_site_key = turnstile_site_key();
?>
<head>
  <title>Johnson-Neyman 2.0: Online App for Nonlinear Probing of Interactions</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
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
    .upload-panel { position: relative; background: #fafbfc; border: 3px dashed #337ab7; border-radius: 6px; padding: 14px 16px; transition: border-color 0.15s, background 0.15s; cursor: pointer; }
    .upload-panel.drag-over { border-color: #23527c; background: #eef5fc; }
    .upload-panel.is-busy { cursor: wait; pointer-events: none; border-color: #aab; background: #f3f4f6; }
    .upload-panel-content { transition: opacity 0.2s; }
    .upload-panel.is-busy .upload-panel-content { opacity: 0.28; }
    .upload-busy-overlay {
      display: none;
      position: absolute;
      inset: 0;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      gap: 10px;
      z-index: 2;
      border-radius: 4px;
    }
    .upload-panel.is-busy .upload-busy-overlay { display: flex; }
    .upload-spinner {
      font-size: 40px;
      color: #337ab7;
      animation: upload-spin 0.85s linear infinite;
    }
    .upload-busy-text {
      margin: 0;
      font-size: 14px;
      font-weight: 600;
      color: #337ab7;
    }
    @keyframes upload-spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
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

<div class="modal fade" id="turnstileHelpModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Upload blocked by browser privacy settings</h4>
      </div>
      <div class="modal-body">
        <p>This site uses Cloudflare Turnstile to block automated uploads. Your browser is blocking it.</p>
        <p><strong>Brave:</strong> Click the Shields icon in the address bar, turn off Shields for webstimate.org, then refresh this page.</p>
        <p><strong>Other browsers / ad blockers:</strong> Allow <code>challenges.cloudflare.com</code> or disable blocking for this site, then refresh.</p>
        <p>Alternatively, try Chrome or Firefox with default settings.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<div class="upload-section">
  <h3>Upload your data</h3>
  <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo UPLOAD_MAX_BYTES; ?>">
    <div class="upload-panel" id="uploadDropzone">
      <div class="upload-panel-content">
        <span class="glyphicon glyphicon-cloud-upload upload-icon" aria-hidden="true"></span>
        <div class="drop-prompt">
          <p>Drag and drop your file here</p>
          <label for="fileToUpload" class="choose-file-link">Choose file</label>
          <input type="file" name="fileToUpload" id="fileToUpload" style="display:none">
          <p class="file-name" id="fileName"></p>
        </div>
      </div>
      <div class="upload-busy-overlay" id="uploadBusyOverlay" aria-hidden="true">
        <span class="glyphicon glyphicon-refresh upload-spinner" aria-hidden="true"></span>
        <p class="upload-busy-text" id="uploadBusyText"></p>
      </div>
    </div>
    <div class="turnstile-wrap">
      <div id="turnstileWidget"></div>
    </div>
  </form>
  <p class="upload-hint"><?php echo htmlspecialchars(UPLOAD_MAX_MESSAGE, ENT_QUOTES, 'UTF-8'); ?></p>
  <p class="upload-hint">No file handy? Download this <a href="example.csv">example datafile</a> and upload it.</p>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js"></script>
<script>
(function () {
  var dropzone = document.getElementById("uploadDropzone");
  var form = document.getElementById("uploadForm");
  var fileInput = document.getElementById("fileToUpload");
  var fileName = document.getElementById("fileName");
  var uploadBusyOverlay = document.getElementById("uploadBusyOverlay");
  var uploadBusyText = document.getElementById("uploadBusyText");
  var pendingFile = null;
  var turnstileWidgetId = null;
  var verifyTimeoutId = null;
  var uploadMaxBytes = <?php echo UPLOAD_MAX_BYTES; ?>;
  var uploadMaxMessage = <?php echo json_encode(UPLOAD_MAX_MESSAGE); ?>;

  function fileTooLarge(file) {
    return file && file.size > uploadMaxBytes;
  }

  function setUploadBusy(busy, message) {
    if (busy) {
      dropzone.classList.add("is-busy");
      dropzone.classList.remove("drag-over");
      uploadBusyText.textContent = message || "Uploading\u2026";
      uploadBusyOverlay.setAttribute("aria-hidden", "false");
    } else {
      dropzone.classList.remove("is-busy");
      uploadBusyText.textContent = "";
      uploadBusyOverlay.setAttribute("aria-hidden", "true");
    }
  }

  function showFileTooLarge() {
    pendingFile = null;
    setUploadBusy(false);
    fileName.textContent = uploadMaxMessage;
  }

  function getTurnstileToken() {
    var el = document.querySelector('[name="cf-turnstile-response"]');
    return el ? el.value : "";
  }

  function showTurnstileHelp() {
    clearVerifyTimeout();
    pendingFile = null;
    setUploadBusy(false);
    fileName.textContent = "";
    if (turnstileWidgetId !== null && typeof turnstile !== "undefined") {
      turnstile.reset(turnstileWidgetId);
    }
    if (typeof $ !== "undefined") {
      $("#turnstileHelpModal").modal("show");
    }
  }

  function clearVerifyTimeout() {
    if (verifyTimeoutId !== null) {
      clearTimeout(verifyTimeoutId);
      verifyTimeoutId = null;
    }
  }

  function submitPendingFile() {
    if (!pendingFile) return;
    clearVerifyTimeout();
    var uploadingName = pendingFile.name;
    var dt = new DataTransfer();
    dt.items.add(pendingFile);
    fileInput.files = dt.files;
    fileName.textContent = "";
    setUploadBusy(true, "Uploading " + uploadingName + "\u2026");
    pendingFile = null;
    form.submit();
  }

  function initTurnstileWidget() {
    if (turnstileWidgetId !== null || typeof turnstile === "undefined") {
      return turnstileWidgetId;
    }
    turnstileWidgetId = turnstile.render("#turnstileWidget", {
      sitekey: "<?php echo htmlspecialchars($turnstile_site_key, ENT_QUOTES, 'UTF-8'); ?>",
      size: "invisible",
      callback: onTurnstileSuccess,
      "error-callback": onTurnstileError,
      "timeout-callback": onTurnstileError
    });
    return turnstileWidgetId;
  }

  window.onTurnstileSuccess = function () {
    submitPendingFile();
  };

  window.onTurnstileError = function () {
    showTurnstileHelp();
  };

  function queueFile(file) {
    if (!file) return;
    if (fileTooLarge(file)) {
      showFileTooLarge();
      return;
    }
    pendingFile = file;
    fileName.textContent = "";
    setUploadBusy(true, "Uploading " + file.name + "\u2026");
    if (getTurnstileToken()) {
      submitPendingFile();
      return;
    }
    if (typeof turnstile === "undefined") {
      showTurnstileHelp();
      return;
    }
    var widgetId = initTurnstileWidget();
    if (widgetId === null) {
      showTurnstileHelp();
      return;
    }
    clearVerifyTimeout();
    verifyTimeoutId = setTimeout(showTurnstileHelp, 20000);
    turnstile.execute(widgetId);
  }

  initTurnstileWidget();

  setTimeout(function () {
    if (typeof turnstile === "undefined") {
      showTurnstileHelp();
    }
  }, 3000);

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

<head>
  <title>Two-lines test</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
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
    .upload-section { max-width: 520px; margin: 0 auto; text-align: center; padding: 8px 0 24px; }
    .upload-section h3 { margin-top: 0; margin-bottom: 18px; font-size: 20px; font-weight: 600; }
    .upload-panel { background: #fafbfc; border: 1px solid #e3e8ef; border-radius: 6px; padding: 24px 20px; transition: border-color 0.15s, background 0.15s; }
    .upload-panel.drag-over { border-color: #5cb85c; background: #f3faf4; }
    .drop-prompt { padding: 8px 0 4px; color: #666; }
    .drop-prompt p { margin-bottom: 6px; }
    .drop-or { font-size: 13px; color: #999; }
    .file-name { font-size: 14px; color: #333; margin: 10px 0 0; font-weight: 600; min-height: 20px; }
    .upload-panel input[type="file"] { display: none; }
    .upload-panel .btn { min-width: 120px; }
    .upload-hint { margin-top: 12px; font-size: 14px; color: #666; }
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
  <div class="upload-panel" id="uploadDropzone">
    <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
      <div class="drop-prompt">
        <p>Drag and drop your file here</p>
        <p class="drop-or">or</p>
        <label for="fileToUpload" class="btn btn-default btn-sm">Choose file</label>
        <input type="file" name="fileToUpload" id="fileToUpload">
        <p class="file-name" id="fileName"></p>
      </div>
      <br>
      <input type="submit" value="Upload" name="Submit" class="btn btn-success btn-lg">
    </form>
    <p class="upload-hint">No file handy? Download this <a href="example.csv">example datafile</a> and upload it.</p>
  </div>
</div>

<script>
(function () {
  var dropzone = document.getElementById("uploadDropzone");
  var form = document.getElementById("uploadForm");
  var fileInput = document.getElementById("fileToUpload");
  var fileName = document.getElementById("fileName");

  function setFile(file, autoSubmit) {
    if (!file) return;
    var dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;
    fileName.textContent = file.name;
    if (autoSubmit) form.submit();
  }

  fileInput.addEventListener("change", function () {
    if (fileInput.files.length) {
      fileName.textContent = fileInput.files[0].name;
    }
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
    if (files.length) setFile(files[0], true);
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

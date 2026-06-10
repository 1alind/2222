<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$dir = __DIR__;

/* =========================
   SAVE FILE (AJAX POST)
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $file = basename($_POST["file"] ?? "");
    $content = $_POST["content"] ?? "";

    $path = $dir . "/" . $file;

    if (is_file($path)) {

        $result = file_put_contents($path, $content);

        if ($result === false) {
            echo "ERROR_WRITE";
        } else {
            echo "OK";
        }

    } else {
        echo "ERROR_FILE";
    }

    exit;
}

/* =========================
   SCAN FILES
========================= */
$files = array_values(array_filter(scandir($dir), function($f){
    return !in_array($f, [".", ".."]) && is_file($f);
}));
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>File Control Panel</title>

<style>
body {
  margin:0;
  font-family:Arial;
  background:#0b0f1a;
  color:#e8ecf3;
}

h2 {
  text-align:center;
  padding:10px;
}

.container {
  display:flex;
  height:100vh;
}

/* FILE LIST */
.sidebar {
  width:250px;
  background:#0f1626;
  overflow:auto;
  border-right:1px solid #222;
}

.file {
  padding:10px;
  cursor:pointer;
  border-bottom:1px solid #1f2a44;
}

.file:hover {
  background:#1a2540;
}

/* EDITOR */
.editor {
  flex:1;
  display:flex;
  flex-direction:column;
}

textarea {
  flex:1;
  width:100%;
  border:none;
  outline:none;
  padding:10px;
  font-family:monospace;
  font-size:13px;
  background:#0d111c;
  color:#fff;
}

/* TOP BAR */
.topbar {
  padding:10px;
  background:#111827;
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:10px;
}

button {
  padding:8px 12px;
  border:none;
  border-radius:5px;
  cursor:pointer;
  color:#fff;
}

.save { background:#2a6cff; }
.clear { background:#ff3b3b; }
.paste { background:#555; }

.status {
  font-size:12px;
  color:#aaa;
  margin-top:5px;
}
</style>
</head>

<body>

<h2>⚡ File Control Panel</h2>

<div class="container">

  <!-- FILE LIST -->
  <div class="sidebar">
    <?php foreach($files as $f): ?>
      <div class="file" onclick="loadFile('<?= $f ?>')">
        <?= $f ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- EDITOR -->
  <div class="editor">

    <div class="topbar">
      <div>
        <div id="currentFile">No file selected</div>
        <div class="status" id="status"></div>
      </div>

      <div style="display:flex; gap:6px;">
        <button class="paste" onclick="pasteClipboard()">📋 Paste</button>
        <button class="clear" onclick="clearFile()">🧹 Clear</button>
        <button class="save" onclick="saveFile()">💾 Save</button>
      </div>
    </div>

    <textarea id="editor"></textarea>

  </div>

</div>

<script>

let currentFile = "";

/* LOAD FILE */
function loadFile(file){
  currentFile = file;

  document.getElementById("currentFile").innerText = file;
  document.getElementById("status").innerText = "Loading...";

  fetch(file)
    .then(r => r.text())
    .then(text => {
      document.getElementById("editor").value = text;
      document.getElementById("status").innerText = "Loaded ✔";
    });
}

/* SAVE FILE */
function saveFile(){

  if(!currentFile){
    document.getElementById("status").innerText = "No file selected";
    return;
  }

  const content = document.getElementById("editor").value;

  document.getElementById("status").innerText = "Saving...";

  fetch(window.location.href, {
    method: "POST",
    headers: {"Content-Type":"application/x-www-form-urlencoded"},
    body:
      "file=" + encodeURIComponent(currentFile) +
      "&content=" + encodeURIComponent(content)
  })
  .then(r => r.text())
  .then(res => {
    if(res.trim() === "OK"){
      document.getElementById("status").innerText = "Saved ✔";
    } else {
      document.getElementById("status").innerText = "Error: " + res;
    }
  });
}

/* CLEAR FILE (NO WARNING) */
function clearFile(){
  if(!currentFile) return;

  document.getElementById("editor").value = "";
  document.getElementById("status").innerText = "Cleared ✔";

  saveFile();
}

/* PASTE FROM CLIPBOARD */
async function pasteClipboard(){
  try {
    const text = await navigator.clipboard.readText();
    document.getElementById("editor").value += text;
    document.getElementById("status").innerText = "Pasted ✔";
  } catch(e){
    document.getElementById("status").innerText = "Clipboard blocked ✖";
  }
}

</script>

</body>
</html>
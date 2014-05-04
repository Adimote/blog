<?php
  // TODO: uploading images and returning the location?
  include_once '../../includes/sql-manager.php';
  include_once '../common.php';

  $title = Conf::Title." | Secure area";
  $head = "Secure area for ".SQLSETTINGS::HOST;
  echo <<< "HTML"
<!DOCTYPE html>
<meta charset='utf-8'>
<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"></link>
<link rel="stylesheet" type="text/css" href="/css/styles.css"></link>
<title>{$title}</title>
<!-- Body -->
<div class="well">
  <legend class="text-center">
    {$head}
  </legend>

  <fieldset>
    <div class="form-group">
      <label class="col-md-2 control-label">Add a Post:</label>
      <div class="col-md-9">
        <a class="btn btn-info" href="post-add.php">post-add.php</a>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-2 control-label">Edit a Post:</label>
      <div class="col-md-9">
        <a class="btn btn-info" href="post-edit.php?id=30">post-edit.php?id=30</a>
      </div>
    </div>

    <div class="form-group">
      <label class="col-md-2 control-label">Edit a Tag:</label>
      <div class="col-md-9">
        <a class="btn btn-info" href="tag-edit.php?id=30">tag-edit.php</a>
      </div>
    </div>
  </fieldset>

</div>
HTML;
?>
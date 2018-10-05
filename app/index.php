<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>DMT</title>

    <!-- jQuery -->
    <script src="js/jquery-1.4.4.min.js"></script>

    <link rel="shortcut icon" href="/favicon.ico" type="image/vnd.microsoft.icon"/>
    <link rel="stylesheet" href="css/jq.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="css/prettify.css" rel="stylesheet">
    <script src="js/prettify.js"></script>

    <!-- Tablesorter: required for bootstrap -->
    <link rel="stylesheet" href="css/theme.bootstrap.css">
    <script src="js/jquery.tablesorter.js"></script>
    <script src="js/jquery.tablesorter.widgets.js"></script>

    <!-- Tablesorter: optional -->
    <link rel="stylesheet" href="css/jquery.tablesorter.pager.css">
    <script src="js/jquery.tablesorter.pager.js"></script>

    <!-- Custom js for the table. -->
    <script src="js/dmt-custom.js"></script>

  </head>
  <body>
    <div id="main">
      <h1>Hello, DMT!</h1>
      <div class="bootstrap_buttons"><button type="button" class="reset btn btn-primary" data-column="0" data-filter=""><i class="icon-white icon-refresh glyphicon glyphicon-refresh"></i> Reset filters</button>
      </div>
      <div id="dmt">
        <?php require_once './autoload.php'; ?>
        <?php require_once './dmt_results.php'; ?>
        <?php print dmt_render(); ?>
      </div>
    </div>
  </body>
</html>


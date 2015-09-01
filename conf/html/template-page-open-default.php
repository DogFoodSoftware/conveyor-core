<!DOCTYPE html>
<html lang="en">
<head>
  <?php global $req_resource, $response; ?>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Liquid Labs company home page.">
  <meta name="author" content="Zane Rockenbaugh">

  <?php
  if (array_key_exists('title', $response->get_data()['document'])) {
  ?>
  <title><?= $response->get_data()['document']['title'] ?></title>
  <?php } ?>

  <script src="/minify/?g=jquery-js"></script>
  <link rel="shortcut icon" href="/style/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/minify/?g=default-css" />
  <link rel="stylesheet" href="/superslides.css" />
  <link rel="stylesheet" href="style/main.css" />

  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>

<body role="document">
<div id="nonFooter">
<div id="content">
    <nav id="navbar" class="sticky" role="navigation">
        <div class="container-fluid">
            <div class="row inverse">
	        <div class="col-xs-12 col-sm-6">
	            <a class="logo-bug" style="padding-top: 5px; padding-bottom: 8px" href="/"><img src="/images/liquid-labs-text-only-white.svg" /></a>
	            <a class="resource-bug" href="/<?= $req_resource ?>">/<?= $req_resource ?></a>
	        </div>
	        <div class="cols-sm-6 hidden-xs pull-right">
	            <form class="navbar-form" role="search">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Search">
                        </div>
                        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
	            </form>
	        </div>
            </div>
<?php
if (array_key_exists('breadcrumbs', $response->get_data()['document'])) {
    $breadcrumbs = $response->get_data()['document']['breadcrumbs'];
?>
            <div class="col-xs-12">
                <ol class="breadcrumbs">
<?php
  $string = '';
  foreach ($breadcrumbs as $crumb) {
      $string = $string.'<li><a href="'.$crumb['path'].'">/'.$crumb['name'].'</a></li>'."\n";
  }
  echo "$string";
?>
                </ol>
            </div>
<?php } ?>
        </div><!-- .container-fluid -->
    </nav><!-- #navbar -->
    <div role="main" class="container-fluid">

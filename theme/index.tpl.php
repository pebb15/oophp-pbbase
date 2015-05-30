<!doctype html>
<!-- Modernizr byter ut no-js mot en lista med inställningar som webbläsaren stöder -->
<html lang='<?=$lang?>' class='no-js'>
<head>
<meta charset="utf-8">
<title><?=get_title($title)?></title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<?php if (isset($favicon)): ?><link rel='shortcut icon' href='<?=$favicon?>'><?php endif;?>
<?php foreach($stylesheets as $val): ?>
	<link rel='stylesheet' type='text/css' href='<?=$val?>'>
<?php endforeach; ?> 
<!--<link rel='stylesheet' href='   $stylesheet?>'>;-->
<script src='<?=$modernizr?>'></script>
</head>
<body>
	<div id='wrapper'>
		<div id='header'><?=$header?></div>
        <div id='nav'><?=GenerateMenu($nav)?></div>
        <article>
        	<div id='main'><?=$main?></div>
            <?=$byline?>
        </article>
        <div id='footer'><?=$footer?></div>
    </div>
    <?php if(isset($jquery)):?><script src='<?=$jquery?>'></script><?php endif;?>
    <?php if(isset($javascript_include)): foreach($javascript_include as $val): ?>
		<script src='<?=$val?>'></script>
	<?php endforeach; endif; ?>
    <?php if(isset($google_analytics)): ?>
		<script>
          var _gaq=[['_setAccount','<?=$google_analytics?>'],['_trackPageview']];
          (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
          g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
          s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
	<?php endif; ?>
</body>
</html>
<?php
// The workspace stylesheet, stamped with its own modification time so a design
// change lands on the next reload instead of hiding inside a cached copy.
$ro_css_path = FCPATH . 'theme/css/restaurantos.css';
$ro_css_ver  = is_file($ro_css_path) ? filemtime($ro_css_path) : time();
?>
<link rel="stylesheet" href="<?= $theme_link; ?>css/restaurantos.css?v=<?= (int) $ro_css_ver; ?>">

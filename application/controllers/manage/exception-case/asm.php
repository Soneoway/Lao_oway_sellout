<?php
$QArea = new Application_Model_Area();
$menus = $QArea->fetchAll(null, 'name');

foreach ($menus as $menu)
    $this->add_row($menu->id, $menu->name);

$this->view->menus = $this->generate_list(null);
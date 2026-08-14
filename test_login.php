<?php
require_once 'backend/Models/UserModel.php';
$model = new UserModel();
$u = $model->findByEmail('admin@lavicky.com'); // assuming admin@lavicky.com
print_r($u);

$u2 = $model->getAll();
print_r($u2);
?>

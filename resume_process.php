<?php
$pf = __DIR__ . '/pause.flag';
if (file_exists($pf)) unlink($pf);
echo json_encode(['success'=>true,'message'=>'Resumed']);
?>

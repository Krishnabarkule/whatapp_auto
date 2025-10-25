<?php
file_put_contents('control.json', json_encode(['action'=>'pause']));
echo json_encode(['message'=>'Paused successfully']);

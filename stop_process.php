<?php
file_put_contents('control.json', json_encode(['action'=>'stop']));
echo json_encode(['message'=>'Stopped successfully']);

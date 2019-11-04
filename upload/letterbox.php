<?php

$target = '-vf "scale=iw*sar*min($MAX_WIDTH/(iw*sar)\,$MAX_HEIGHT/ih):ih*min($MAX_WIDTH/(iw*sar)\,$MAX_HEIGHT/ih),pad=$MAX_WIDTH:$MAX_HEIGHT:(ow-iw)/2:(oh-ih)/2" \ ';

echo '<pre>';
echo $target;
echo "\n";
echo '</pre>';

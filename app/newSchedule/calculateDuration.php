<?php
function calculateDuration($start, $end)
{
    return (strtotime($end) - strtotime($start))/3600;
}
?>

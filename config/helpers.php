<?php
// config/helpers.php

function isActive($pageNames, $currentPage) {
    if (is_array($pageNames)) {
        return in_array($currentPage, $pageNames) ? 'active' : '';
    }
    return ($currentPage == $pageNames) ? 'active' : '';
}
?>
<?php

function ham_entities($in, $opts = null)
{
	return htmlspecialchars($in, ENT_COMPAT | ENT_HTML5, 'UTF-8');
}

?>

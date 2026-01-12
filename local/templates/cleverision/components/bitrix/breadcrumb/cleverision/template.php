<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//delayed function must return a string
if(empty($arResult))
	return "";

$strReturn = '';

$strReturn .= '<div class="cmpBreadcrumbs"><div class="container">';

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++)
{
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	$arrow = ($index > 0? '<span>&gt;&gt;</span>' : '');

	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
	{
		$strReturn .= $arrow . '
            <a href="'.$arResult[$index]["LINK"].'" title="'.$title.'">
                '.$title.'
            </a>';
	}
	else
	{
		$strReturn .= $arrow . '<span>'.$title.'</span>';
	}
}

$strReturn .= '</div></div>';

return $strReturn;

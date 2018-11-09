<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
session_write_close();
require_once(__DIR__."/../ajax/dbfuncs.php");
$db = new dbfuncs();


function getSideMenuPipelineItem($obj)
{
$html="";
foreach ($obj as $item):
    $nameSub = substr($item->{'name'}, 0, 20);
    $html.='<li pin="'.$item->{'pin'}.'" p="'.$item->{'perms'}.'" g="'.$item->{'group_id'}.'"><a href="index.php?np=1&id='.$item->{'id'}.'" class="pipelineItems"  draggable="false" id="pipeline-'.$item->{'id'}.'" ><i class="fa fa-angle-double-right"></i>'.$nameSub.'</a></li>';
endforeach;
return $html;
}

$parentMenusPipeline = json_decode($db->getParentSideBarPipeline($ownerID));

$menuhtml='<ul id="autocompletes1" class="sidebar-menu" data-widget="tree">';
//Add pipelines
$menuhtml.='<li class="header">PIPELINES</li>';
foreach ($parentMenusPipeline as $parentitem):
    $nameSub = substr($parentitem->{'name'}, 0, 20);
    $menuhtml.='<li class="treeview">';
    $menuhtml.='<a href="" draggable="false"><i class="fa fa-spinner"></i> <span p="'.$parentitem->{'perms'}.'" g="'.$parentitem->{'group_id'}.'" >'.$nameSub.'</span>';

    $items = json_decode($db->getSubMenuFromSideBarPipe($parentitem->{'name'}, $ownerID));
    
	$menuhtml.='<i class="fa fa-angle-left pull-right"></i></a>';
    $menuhtml.='<ul id="pipeGr-'.$parentitem->{'id'}.'" class="treeview-menu">';
    $menuhtml.= getSideMenuPipelineItem($items);
    $menuhtml.='</ul>';
    $menuhtml.='</li>';
endforeach;
$menuhtml.='<ul>';
echo $menuhtml;
?>
    <!-- /.sidebar -->





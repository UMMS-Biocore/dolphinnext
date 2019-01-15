<!-- sidebar menu: : style can be found in sidebar.less -->
<!--        Control Sidebar-->
<!--
-->
<?php
require_once(__DIR__."/../ajax/dbfuncs.php");
$db = new dbfuncs();

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
session_write_close();
function getSideMenuItem($obj)
{
$html="";
foreach ($obj as $item):
    $nameSub = substr($item->{'name'}, 0, 20);
        $html.='<li><a href="index.php?np=3&id='.$item->{'id'}.'" class="projectItems"  draggable="false" id="propipe-'.$item->{'id'}.'"><i class="fa fa-angle-double-right"></i>'.$nameSub.'</a></li>';
endforeach;
return $html;
}
$parentMenus = json_decode($db->getParentSideBarProject($ownerID));
$menuhtml='<ul id="autocompletes1" class="sidebar-menu" data-widget="tree">';
$menuhtml.='<li class="header">PROJECTS</li>';
foreach ($parentMenus as $parentitem):
    $nameSub = substr($parentitem->{'name'}, 0, 15);
    $menuhtml.='<li class="treeview">';
    $menuhtml.='<a href="" draggable="false"><i  class="fa fa-circle-o"></i> <span>'.$nameSub.'</span>';
    $items = json_decode($db->getSubMenuFromSideBarProject($parentitem->{'id'}, $ownerID));
    $menuhtml.='<i class="fa fa-angle-left pull-right"></i></a>';
    $menuhtml.='<ul id="side-'.$parentitem->{'id'}.'" class="treeview-menu">';
    $menuhtml.= getSideMenuItem($items);
    $menuhtml.='</ul>';
    $menuhtml.='</li>';
endforeach;
$menuhtml.='<ul>';
echo $menuhtml;
?>
    <!-- /.sidebar -->
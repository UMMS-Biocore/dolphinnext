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

class sidemenufuncs {
    function getSideMenuItem($obj){
        $html="";
        foreach ($obj as $item):
        $orgName = $item->{'name'};
        $showName = $orgName;
        $tooltip = "";
        if (strlen($orgName) >20){
            $showName = substr($orgName, 0, 20);
            $tooltip = 'data-toggle="tooltip" data-placement="right" data-original-title="'.$orgName.'"';
        }
        $nameSub = substr($item->{'name'}, 0, 20);
        $html.='<li '.$tooltip.'><a href="index.php?np=3&id='.$item->{'id'}.'" class="projectItems"  origin="'.$orgName.'" draggable="false" id="propipe-'.$item->{'id'}.'"><i class="fa fa-angle-double-right"></i>'.$showName.'</a></li>';
        endforeach;
        return $html;
    }

    function getSideMenuAllLiBlocks($type, $parentData, $menuhtml, $db, $ownerID){
        foreach (array_reverse($parentData) as $parentitem):
        $orgName = $parentitem->{'name'};
        $showName = $orgName;
        $tooltip = "";
        if (strlen($orgName) >15){
            $showName = substr($orgName, 0, 15);
            $tooltip = 'data-toggle="tooltip" data-placement="right" data-original-title="'.$orgName.'"';
        }
        $items = json_decode($db->getSubMenuFromSideBarProject($parentitem->{'id'}, $ownerID));
        if (($type == "shared" && count($items) > 0) || $type == "user"){
            $menuhtml.='<li class="treeview" '.$tooltip.'>';
            $menuhtml.='<a href="javascript:void(0)" draggable="false"><i  class="fa fa-circle-o"></i> <span>'.$showName.'</span>';
            $menuhtml.='<i class="fa fa-angle-left pull-right"></i></a>';
            $menuhtml.='<ul id="side-'.$parentitem->{'id'}.'" class="treeview-menu">';
            $menuhtml.= $this->getSideMenuItem($items);
            $menuhtml.='</ul>';
            $menuhtml.='</li>';
        }
        endforeach;
        return $menuhtml;
    }
}
$sidefunc = new sidemenufuncs();

$menuhtml='<ul id="autocompletes1" class="sidebar-menu" data-widget="tree">';
$menuhtml.='<li class="header"> MY PROJECTS</li>';
$parentData = json_decode($db->getProjects("", "user",$ownerID));
$menuhtml= $sidefunc->getSideMenuAllLiBlocks("user", $parentData, $menuhtml, $db, $ownerID);
$menuhtml.='<li class="header"> SHARED PROJECTS</li>';
$sharedParentData = json_decode($db->getProjects("", "shared",$ownerID));
$menuhtml= $sidefunc->getSideMenuAllLiBlocks("shared", $sharedParentData, $menuhtml, $db, $ownerID);
$menuhtml.='<ul>';
echo $menuhtml;
?>
<!-- /.sidebar -->
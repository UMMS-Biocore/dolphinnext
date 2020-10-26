<!--  when $np is not exist (main page), load this sidemenu}-->
<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
session_write_close();
require_once(__DIR__."/../ajax/dbfuncs.php");
$db = new dbfuncs();

function getShowLi($items){
    $showLi = "";
    $count_not_admin_only = 0;
    foreach ($items as $item):
        $admin_only = isset($item->{'admin_only'}) ? $item->{'admin_only'} : 0;
        settype($admin_only, "integer");
        if ($admin_only < 1){
            $count_not_admin_only += 1;
        }
    endforeach;
    if ($count_not_admin_only < 1){
        $showLi = ' style="display:none;"';
    }
    return $showLi;
}

function getSideMenuPipelineItem($obj)
{
$html="";
foreach ($obj as $item):
    $orgName = $item->{'name'};
    $showName = $orgName;
    $tooltip = "";
    if (strlen($orgName) >20){
        $showName = substr($orgName, 0, 20);
        $tooltip = 'data-toggle="tooltip" data-placement="right" data-original-title="'.$orgName.'"';
    }
    $admin_only = isset($item->{'admin_only'}) ? 'admin="'.$item->{'admin_only'}.'"' : "";
    $html.='<li '.$admin_only.'  pin="'.$item->{'pin'}.'"  p="'.$item->{'perms'}.'" g="'.$item->{'group_id'}.'"'.$tooltip.'><a href="index.php?np=1&id='.$item->{'id'}.'" class="pipelineItems"  origin="'.$orgName.'" ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="pipeline-'.$item->{'id'}.'" ><i class="fa fa-angle-double-right"></i>'.$showName.'</a></li>';
endforeach;
return $html;
}

$parentMenusPipeline = json_decode($db->getParentSideBarPipeline($ownerID));

$menuhtml='<ul id="autocompletes1" class="sidebar-menu" data-widget="tree">';
//Add pipelines
$menuhtml.='<li class="header">PIPELINES</li>';
foreach ($parentMenusPipeline as $parentitem):
    $orgName = $parentitem->{'name'};
    $showName = $orgName;
    $tooltip = "";
    if (strlen($orgName) >19){
        $showName = substr($orgName, 0, 19);
        $tooltip = 'data-toggle="tooltip" data-placement="right" data-original-title="'.$orgName.'"';
    }
    $items = json_decode($db->getSubMenuFromSideBarPipe($parentitem->{'name'}, $ownerID));
    if (count($items) > 0){
        $showLi= getShowLi($items);
        $menuhtml.='<li class="treeview" '.$tooltip.$showLi.'>';
        $perms = isset($parentitem->{'perms'}) ? $parentitem->{'perms'} : "";
        $group_id = isset($parentitem->{'group_id'}) ? $parentitem->{'group_id'} : "";
        $menuhtml.='<a href="javascript:void(0)" draggable="false"><i class="fa fa-spinner"></i> <span origin="'.$parentitem->{'name'}.'" p="'.$perms.'" g="'.$group_id.'" >'.$showName.'</span>';
	   $menuhtml.='<i class="fa fa-angle-left pull-right"></i></a>';
        $menuhtml.='<ul id="pipeGr-'.$parentitem->{'id'}.'" class="treeview-menu">';
        $menuhtml.= getSideMenuPipelineItem($items);
        $menuhtml.='</ul>';
        $menuhtml.='</li>';
    }
endforeach;
$menuhtml.='<ul>';
echo $menuhtml;
?>
    <!-- /.sidebar -->





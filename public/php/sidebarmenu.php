<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
session_write_close();
?>

<!-- sidebar menu: : style can be found in sidebar.less -->
<!--        Control Sidebar-->
<div class=" dropdown messages-menu ">
    <?php
    if ($ownerID != ''){
        echo '<a id="newPipeline" class="btn btn-primary" style=" margin-left:15px;" href="index.php?np=1&id=0" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="New Pipeline">
        <span class="glyphicon-stack">
            <i class="fa fa-plus-circle glyphicon-stack-2x" style="color:white;"></i>
            <i class="fa fa-spinner glyphicon-stack-1x" style="color:white;"></i>
        </span>
    </a>
    <button type="button" id="addprocess" class="btn btn-primary" data-toggle="modal" name="button" data-target="#addProcessModal" data-backdrop="false" style=" margin-left:0px;">
        <a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="New Process">
            <span class="glyphicon-stack">
                <i class="fa fa-plus-circle glyphicon-stack-2x" style="color:white;"></i>
                <i class="fa fa-circle-o glyphicon-stack-1x" style="color:white;"></i>
            </span>
        </a>
    </button>

        <button type="button" class="btn btn-primary dropdown-toggle" style="float:right; margin-right:10px;" data-toggle="dropdown"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Filter"><span class="glyphicon glyphicon-filter" style="color:white;"></span> </a><span class="caret"></span></button><ul id="filterMenu" class="dropdown-menu dropdown-menu-right filterM"></ul>"';                    } 
    ?>
</div>

<?php
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

function getSideMenuItem($obj, $type)
{
    $html="";
    foreach ($obj as $item):
    $orgName = $item->{'name'};
    $showName = $orgName;
    $tooltip = "";
    if (strlen($orgName) >20){
        $showName = substr($orgName, 0, 20);
        $tooltip = ' data-toggle="tooltip" data-placement="right" data-original-title="'.$orgName.'"';
    }
    $admin_only = isset($item->{'admin_only'}) ? 'admin="'.$item->{'admin_only'}.'"' : "";
    $publish = isset($item->{'publish'}) ? 'pub="'.$item->{'publish'}.'"' : "";

    if ($type == "process"){
        $html.='<li '.$admin_only.' '.$publish.' p="'.$item->{'perms'}.'" g="'.$item->{'group_id'}.'"'.$tooltip.'><a data-toggle="modal" data-target="#addProcessModal" class="processItems" origin="'.$orgName.'" data-backdrop="false" href="" ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="'.$orgName.'@'.$item->{'id'}.'" ><i class="fa fa-angle-double-right"></i>'.$showName.'</a></li>';
    } else if ($type == "pipeline"){
        $html.='<li '.$admin_only.'  pin="'.$item->{'pin'}.'"  p="'.$item->{'perms'}.'" g="'.$item->{'group_id'}.'"'.$tooltip.'><a href="index.php?np=1&id='.$item->{'id'}.'" class="pipelineItems"  origin="'.$orgName.'" ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="pipeline-'.$item->{'id'}.'" ><i class="fa fa-angle-double-right"></i>'.$showName.'</a></li>';
    }

    endforeach;
    return $html;
}
// get process_groups
$parentMenus = json_decode($db->getParentSideBar($ownerID));
// get pipeline_groups
$parentMenusPipeline = json_decode($db->getParentSideBarPipeline($ownerID));
//style="overflow-y: scroll; width: auto; height: calc(100vh - 250px);"
$menuhtml='<ul id="autocompletes1" class="sidebar-menu" data-widget="tree" >';
if ($ownerID != ''){
    //add input/output parameters
    $menuhtml.='<li class="header">INPUT/OUTPUT PARAMETER</li>';
    $menuhtml.='<li id="inputs" >  <a ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="inputparam@inPro" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Please drag & drop to use it"> <i class="fa fa-circle"  style="color:#F2AA3A;" ></i>  <text id="text-inPro" font-family="FontAwesome" font-size="0.9em" x="-6" y="15"></text> <span> Input Parameter </span> </a></li>';  
    $menuhtml.='<li id="outputs" class="treeview">  <a ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="outputparam@outPro" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Please drag & drop to use it"> <i class="fa fa-circle" style="color:#2D8C2E;"></i>  <text id="text-outPro" font-family="FontAwesome" font-size="0.9em" x="-6" y="15"></text> <span> Output Parameter </span> </a></li>'; 
}
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
// get pipelines belong to pipeline_group name
$items = json_decode($db->getSubMenuFromSideBarPipe($parentitem->{'name'}, $ownerID));


if (count($items) > 0){
    $showLi= getShowLi($items);
    $perms = isset($parentitem->{'perms'}) ? $parentitem->{'perms'} : "";
    $group_id = isset($parentitem->{'group_id'}) ? $parentitem->{'group_id'} : "";
    $menuhtml.='<li class="treeview" '.$tooltip.$showLi.'>';
    $menuhtml.='<a href="javascript:void(0)" draggable="false" ><i class="fa fa-spinner"></i> <span  class="pipelineParent" origin="'.$parentitem->{'name'}.'" p="'.$perms.'" g="'.$group_id.'" >'.$showName.'</span>';
    $menuhtml.='<i class="fa fa-angle-left pull-right"></i></a>';
    $menuhtml.='<ul id="pipeGr-'.$parentitem->{'id'}.'" class="treeview-menu">';
    $menuhtml.= getSideMenuItem($items, "pipeline");
    $menuhtml.='</ul>';
    $menuhtml.='</li>';
}
endforeach;

if ($ownerID != ''){
    $menuhtml.='<li id="processSideHeader" class="header">PROCESSES</li>';
    foreach ($parentMenus as $parentitem):
    $orgName = $parentitem->{'name'};
    $showName = $orgName;
    $tooltip = "";
    if (strlen($orgName) >15){
        $showName = substr($orgName, 0, 15);
        $tooltip = 'data-toggle="tooltip" data-placement="right" data-original-title="'.$orgName.'"';
    }
    // get processes belong to process_group name
    $items = json_decode($db->getSubMenuFromSideBar($parentitem->{'name'}, $ownerID));

    if (count($items) > 0){
        $showLi= getShowLi($items);
        $perms = isset($parentitem->{'perms'}) ? $parentitem->{'perms'} : "";
        $group_id = isset($parentitem->{'group_id'}) ? $parentitem->{'group_id'} : "";
        $menuhtml.='<li class="treeview" '.$tooltip.$showLi.'>';
        $menuhtml.='<a href="javascript:void(0)" draggable="false" ><i  class="fa fa-circle-o"></i> <span class="processParent" origin="'.$parentitem->{'name'}.'" p="'.$perms.'" g="'.$group_id.'" >'.$showName.'</span>';
        $menuhtml.='<i class="fa fa-angle-left pull-right"></i></a>';
        $menuhtml.='<ul id="side-'.$parentitem->{'id'}.'" class="treeview-menu">';
        $menuhtml.= getSideMenuItem($items, "process");
        $menuhtml.='</ul>';
        $menuhtml.='</li>';
    }
    endforeach;
}
$menuhtml.='<ul>';
echo $menuhtml;
?>
<!-- /.sidebar -->

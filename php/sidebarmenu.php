<!-- sidebar menu: : style can be found in sidebar.less -->
<!--        Control Sidebar-->
<div class=" dropdown messages-menu ">
    <a id="newPipeline" class="btn btn-warning" style=" margin-left:15px;" href="index.php?np=1" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="New Pipeline">
                  <span class="glyphicon-stack">
                    <i class="fa fa-plus-circle glyphicon-stack-2x" style="color:white;"></i>
                      <i class="fa fa-spinner glyphicon-stack-1x" style="color:white;"></i>
                  </span>
              </a>
    <button type="button" id="addprocess" class="btn btn-default btn-success" data-toggle="modal" name="button" data-target="#addProcessModal" data-backdrop="false" style=" margin-left:0px;">
              <a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="New Process">
                  <span class="glyphicon-stack">
                    <i class="fa fa-plus-circle glyphicon-stack-2x" style="color:white;"></i>
                      <i class="fa fa-circle-o glyphicon-stack-1x" style="color:white;"></i>
                  </span>
              </a>
            </button>
    <?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
session_write_close();
if ($ownerID != ''){
echo '<button type="button" class="btn btn-primary dropdown-toggle" style="float:right; margin-right:10px;" data-toggle="dropdown"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Filter"><span class="glyphicon glyphicon-filter" style="color:white;"></span> </a><span class="caret"></span></button><ul id="filterMenu" class="dropdown-menu dropdown-menu-right filterM"></ul>"';                    } 
?>
</div>

<?php
require_once(__DIR__."/../ajax/dbfuncs.php");
$db = new dbfuncs();

function getSideMenuItem($obj)
{
$html="";
foreach ($obj as $item):
    $nameSub = substr($item->{'name'}, 0, 20);
    $html.='<li pub="'.$item->{'publish'}.'" p="'.$item->{'perms'}.'" g="'.$item->{'group_id'}.'"><a data-toggle="modal" data-target="#addProcessModal" data-backdrop="false" href="" ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="'.$item->{'name'}.'@'.$item->{'id'}.'" ><i class="fa fa-angle-double-right"></i>'.$nameSub.'</a></li>';
endforeach;
return $html;
}

function getSideMenuPipelineItem($obj)
{
$html="";
foreach ($obj as $item):
    $nameSub = substr($item->{'name'}, 0, 20);
    $html.='<li pin="'.$item->{'pin'}.'" p="'.$item->{'perms'}.'" g="'.$item->{'group_id'}.'"><a href="index.php?np=1&id='.$item->{'id'}.'" class="pipelineItems"  ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="pipeline-'.$item->{'id'}.'" ><i class="fa fa-angle-double-right"></i>'.$nameSub.'</a></li>';
endforeach;
return $html;
}

$parentMenus = json_decode($db->getParentSideBar($ownerID));
$parentMenusPipeline = json_decode($db->getParentSideBarPipeline($ownerID));
$menuhtml='<ul id="autocompletes1" class="sidebar-menu" data-widget="tree">';
//add input/output parameters
$menuhtml.='<li class="header">INPUT/OUTPUT PARAMETERS</li>';
$menuhtml.='<li id="inputs" >  <a ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="inputparam@inPro"> <i class="fa fa-plus"></i>  <text id="text-inPro" font-family="FontAwesome" font-size="0.9em" x="-6" y="15"></text> <span> Input Parameters </span> </a></li>';  
$menuhtml.='<li id="outputs" class="treeview">  <a ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="outputparam@outPro"> <i class="fa fa-plus"></i>  <text id="text-outPro" font-family="FontAwesome" font-size="0.9em" x="-6" y="15"></text> <span> Output Parameters </span> </a></li>'; 
//Add pipelines
$menuhtml.='<li class="header">PIPELINES</li>';
foreach ($parentMenusPipeline as $parentitem):
    $nameSub = substr($parentitem->{'name'}, 0, 20);
//
    $menuhtml.='<li class="treeview">';
    $menuhtml.='<a href="" draggable="false"><i class="fa fa-spinner"></i> <span p="'.$parentitem->{'perms'}.'" g="'.$parentitem->{'group_id'}.'" >'.$nameSub.'</span>';

    $items = json_decode($db->getSubMenuFromSideBarPipe($parentitem->{'name'}, $ownerID));
    
	$menuhtml.='<i class="fa fa-angle-left pull-right"></i></a>';
    $menuhtml.='<ul id="pipeGr-'.$parentitem->{'id'}.'" class="treeview-menu">';
    $menuhtml.= getSideMenuPipelineItem($items);
    $menuhtml.='</ul>';
    $menuhtml.='</li>';
endforeach;
 
$menuhtml.='<li id="processSideHeader" class="header">PROCESSES</li>';
foreach ($parentMenus as $parentitem):
    $nameSub = substr($parentitem->{'name'}, 0, 15);

    $menuhtml.='<li class="treeview">';
    $menuhtml.='<a href="" draggable="false"><i  class="fa fa-circle-o"></i> <span p="'.$parentitem->{'perms'}.'" g="'.$parentitem->{'group_id'}.'" >'.$nameSub.'</span>';
    
    $items = json_decode($db->getSubMenuFromSideBar($parentitem->{'name'}, $ownerID));

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

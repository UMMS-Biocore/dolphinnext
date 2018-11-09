<?php



function getTitle($np)
{
  $ret="";
  if ($np==1){$ret = "Pipeline";}
  else if ($np==2){$ret = "Project";}
  else if ($np==3){$ret = "Run";}
  else if ($np==4){$ret = "Profile";}
  return $ret; 
}

function getPage($np, $login, $id)
{
  if ($np==1 && $login==1){include("php/pipeline.php"); }
  else if ($np==1 && $login!=1 && !empty($id)){include("php/publicpipeline.php"); }
  else if ($np==2 && $login==1 && empty($id)){include("php/projects.php");}
  else if ($np==2 && $login==1 && !empty($id)){include("php/projectsDetail.php");}
  else if ($np==3 && $login==1 && !empty($id)){include("php/runpipeline.php");}
  else if ($np==4 && $login==1){include("php/profile.php");}
  else {include("php/public.php");}
}

function getSidebarMenu($np,$login)
{
  if (($np==2 || $np==3 || $np==4) && $login==1){include("php/sidebarmenuproject.php"); }
    else if ($np == ''){include("php/sidebarmenumain.php");}
  else {include("php/sidebarmenu.php");}
}

function getJS($np, $login, $id)
{
  $js = "<script src=\"js/jsfuncs.js\"></script>";
    
  if ($np==1 && $login==1){$js .= "<script src=\"bower_components/d3/d3.v3.min.js\" charset=\"utf-8\"></script> 
  <script src=\"js/pipelineModal.js\"></script>
  <script src=\"js/pipelineD3.js\"></script><script src=\"js/nextflowText.js\"></script>";}
  else if ($np==1 && $login!=1 && !empty($id)){$js .= "<script src=\"bower_components/d3/d3.v3.min.js\" charset=\"utf-8\"></script> 
  <script src=\"js/publicpipeline.js\"></script>";}
  else if ($np==2 && $login==1 && empty($id)){$js .= "<script src=\"js/projects.js\"></script>"; }
  else if ($np==2 && $login==1 && !empty($id)){$js .= "<script src=\"js/projectsDetail.js\"></script>"; }
  else if ($np==3 && $login==1 && !empty($id)){$js .= "<script src=\"bower_components/d3/d3.v3.min.js\" charset=\"utf-8\"></script> 
  <script src=\"js/runpipeline.js\"></script><script src=\"js/nextflowText.js\"></script>";}
  else if ($np==4 && $login==1){$js .= "<script src=\"js/profile.js\"></script>"; }
    else {$js .= "<script src=\"js/public.js\"></script>";}
  return $js;
}


?>

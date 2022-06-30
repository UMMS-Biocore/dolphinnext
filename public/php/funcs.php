<?php

/**
 *  auto_version($file): Forces the browser to reload cached CSS/JS files when it's modified
 *  Given a file, i.e. base.css, replaces it with a string containing the
 *  file's mtime, i.e. base.1221534296.css
 *  update .htaccess: RewriteRule  ^(.*)\.[\d]{10}\.(css|js)$ public/$1.$2 [L]
 */
function auto_version($file)
{
    if (!file_exists($file)) {
        return $file;
    }
    $mtime = filemtime($file);
    return preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
}

function getTitle($np)
{
    $ret = "";
    if ($np == 1) {
        $ret = "Pipeline Generation";
    } else if ($np == 2) {
        $ret = "Project";
    } else if ($np == 3) {
        $ret = "Run Generation";
    } else if ($np == 4) {
        $ret = "Profile";
    } else if ($np == 5) {
        $ret = "Run Status";
    } else if ($np == 7) {
        $ret = "Apps";
    } else if ($np == 8) {
        $ret = "Admin Dashboard";
    }
    return $ret;
}

function getPage($np, $login, $id, $ownerID)
{
    $db = new dbfuncs();
    if ($np == 1 && (!empty($id) || $id === "0")) {
        //check if user is admin
        $userRole = $db->getUserRoleVal($ownerID);
        if ($userRole == "admin") {
            include("php/pipeline.php");
            include("php/pipelinemodal.php");
            return;
        }
        //if user not admin then checkUserPermission
        list($permCheck, $warnName) = $db->checkUserPermission("biocorepipe_save", $id, $ownerID, "r");
        if (!empty($permCheck) || $id === "0") {
            include("php/pipeline.php");
            include("php/pipelinemodal.php");
        } else {
            $val = 0;
            $tokens = isset($_SESSION['token']) ? $_SESSION['token'] : [];
            foreach ($tokens as $tk) :
                $val = $db->validateToken($tk, $id, $np);
                if (!empty($val)) {
                    break;
                }
            endforeach;
            if (!empty($val)) {
                include("php/pipeline.php");
                include("php/pipelinemodal.php");
            } else {
                include("php/error403.php");
            }
        }
    } else if ($np == 1 && $login == 1 && empty($id)) {
        include("php/public.php");
        include("php/pipelinemodal.php");
    } else if ($np == 2 && $login == 1 && empty($id)) {
        include("php/projects.php");
    } else if ($np == 2 && $login == 1 && !empty($id)) {
        include("php/projectsDetail.php");
    } else if ($np == 3 && $login == 1 && !empty($id)) {
        //check if user is admin
        $userRole = $db->getUserRoleVal($ownerID);
        if ($userRole == "admin") {
            include("php/runpipeline.php");
            return;
        }
        //if user not admin then checkUserPermission
        list($permCheck, $warnName) = $db->checkUserPermission("project_pipeline", $id, $ownerID, "r");
        if (!empty($permCheck)) {
            include("php/runpipeline.php");
        } else {
            $tokens = isset($_SESSION['token']) ? $_SESSION['token'] : [];
            foreach ($tokens as $tk) :
                $val = $db->validateToken($tk, $id, $np);
                if (!empty($val)) {
                    break;
                }
            endforeach;
            if (!empty($val)) {
                include("php/runpipeline.php");
            } else {
                include("php/error403.php");
            }
        }
    } else if ($np == 4 && $login == 1) {
        include("php/profile.php");
    } else if ($np == 5 && $login == 1 && empty($id)) {
        include("php/runstatus.php");
    } else if ($np == 6) {
        include("php/terms.php");
    } else if ($np == 7 && $login == 1 && empty($id)) {
        include("php/app.php");
    } else if ($np == 7 && $login == 1 && !empty($id)) {
        include("php/appDetail.php");
    } else if ($np == 8 && $login == 1) {
        $userRole = $db->getUserRoleVal($ownerID);
        if ($userRole == "admin") {
            include("php/adminDashboard.php");
        } else {
            include("php/error403.php");
        }
    } else {
        include("php/public.php");
    }
}

function getSidebarMenu($np, $login)
{
    if (($np == 2 || $np == 3 || $np == 4 || $np == 5 || $np == 7 || $np == 8) && $login == 1) {
        include("php/sidebarmenuproject.php");
    } else if ($np == '') {
        include("php/sidebarmenumain.php");
    } else {
        include("php/sidebarmenu.php");
    }
}

function getJS($np, $login, $id)
{
    $js = '<script src="' . auto_version("js/jsfuncs.js") . '"></script>
           <script src="' . auto_version("js/wizard.js") . '"></script>';
    if ($np == 1 && !empty($id)) {
        $js .= '<script src="bower_components/d3/d3.v3.min.js" charset="utf-8"></script> 
                <script src="' . auto_version("js/pipelineD3core.js") . '"></script>
                <script src="' . auto_version("js/pipelineD3.js") . '"></script>
                <script src="' . auto_version("js/plugins/textEditor.js") . '"></script>
                <script src="' . auto_version("js/pipelineModal.js") . '"></script>
                <script src="' . auto_version("js/import.js") . '"></script>
                <script src="' . auto_version("js/nextflowText.js") . '"></script>';
    } else if ($np == 1 && $login == 1 && empty($id)) {
        $js .= '<script src="bower_components/d3/d3.v3.min.js" charset="utf-8"></script> 
                <script src="' . auto_version("js/pipelineD3core.js") . '"></script>
                <script src="' . auto_version("js/pipelineD3.js") . '"></script>
                <script src="' . auto_version("js/plugins/textEditor.js") . '"></script>
                <script src="' . auto_version("js/pipelineModal.js") . '"></script>
                <script src="' . auto_version("js/import.js") . '"></script>
                <script src="' . auto_version("js/nextflowText.js") . '"></script>
                <script src="' . auto_version("js/public.js") . '"></script>';
    } else if ($np == 2 && $login == 1 && empty($id)) {
        $js .= '<script src="' . auto_version("js/projects.js") . '"></script>';
    } else if ($np == 2 && $login == 1 && !empty($id)) {
        $js .= '<script src="' . auto_version("js/projectsDetail.js") . '"></script>';
    } else if ($np == 3 && $login == 1 && !empty($id)) {
        $js .= '<script src="bower_components/d3/d3.v3.min.js" charset="utf-8"></script> 
                <script src="bower_components/handsontable-6.2.2/dist/handsontable.full.min.js" charset="utf-8"></script> 
                <script src="' . auto_version("js/pipelineD3core.js") . '"></script>
                <script src="' . auto_version("js/runpipeline.js") . '"></script>
                <script src="' . auto_version("js/nextflowText.js") . '"></script>';
    } else if ($np == 4 && $login == 1) {
        $js .= '<script src="' . auto_version("js/profile.js") . '"></script>';
    } else if ($np == 5 && $login == 1) {
        $js .= '<script src="' . auto_version("js/runstatus.js") . '"></script>';
    } else if ($np == 7 && $login == 1 && empty($id)) {
        $js .= '<script src="' . auto_version("js/app.js") . '"></script>';
    } else if ($np == 7 && $login == 1 && !empty($id)) {
        $js .= '<script src="' . auto_version("js/plugins/textEditor.js") . '"></script>';
        $js .= '<script src="' . auto_version("js/appDetail.js") . '"></script>';
    } else if ($np == 8 && $login == 1) {
        $js .= '<script src="bower_components/chartjs/chart3.8.0.min.js"></script>';
        $js .= '<script src="bower_components/chartjs/hammerjs2.0.8.min.js"></script>';
        $js .= '<script src="bower_components/chartjs/chartjs-plugin-zoom1.2.0.min.js"></script>';


        $js .= '<script src="' . auto_version("js/adminDashboard.js") . '"></script>';
    } else {
        $js .= '<script src="' . auto_version("js/public.js") . '"></script>';
    }
    return $js;
}

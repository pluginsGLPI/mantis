<?php


if(isset($_POST["action"])){

    switch ($_POST["action"]){

        case "testConnexionMantisWS":

            require_once('../inc/mantisWS.class.php');

            $ws = new PluginMantisMantisws();
            echo $ws->testConnectionWS($_POST["host"],$_POST["url"],$_POST["login"] , $_POST["pwd"]);

            break;


        case "updateLinkField":

            require_once('../inc/linkfield.class.php');

            $linkField = new PluginMantisLinkfield();
            echo $linkField->updateLinkField($_POST);

            break;



        default:
            echo 0;

    }

}else {
    echo 0;
}


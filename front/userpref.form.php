<?php
include ('../../../inc/includes.php');

if (isset($_POST['update'])) {
   
   if (isset($_POST['followFollow']))
      $_POST['followFollow'] = 1;
   else
      $_POST['followFollow'] = 0;
   
   if (isset($_POST['followTitle']))
      $_POST['followTitle'] = 1;
   else
      $_POST['followTitle'] = 0;
   
   if (isset($_POST['followTask']))
      $_POST['followTask'] = 1;
   else
      $_POST['followTask'] = 0;
   
   if (isset($_POST['followCategorie']))
      $_POST['followCategorie'] = 1;
   else
      $_POST['followCategorie'] = 0;
   
   if (isset($_POST['followDescription']))
      $_POST['followDescription'] = 1;
   else
      $_POST['followDescription'] = 0;
   
   if (isset($_POST['followLinkedItem']))
      $_POST['followLinkedItem'] = 1;
   else
      $_POST['followLinkedItem'] = 0;
   
   if (isset($_POST['followAttachment']))
      $_POST['followAttachment'] = 1;
   else
      $_POST['followAttachment'] = 0;
   
   $userpref = new PluginMantisUserpref();
   $userpref->update($_POST);
}

Html::back();

<?php

/*
   ------------------------------------------------------------------------
   GLPI Plugin MantisBT
   Copyright (C) 2014 by the GLPI Plugin MantisBT Development Team.

   https://forge.indepnet.net/projects/mantis
   ------------------------------------------------------------------------

   LICENSE

   This file is part of GLPI Plugin MantisBT project.

   GLPI Plugin MantisBT is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.

   GLPI Plugin MantisBT is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI Plugin MantisBT. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   GLPI Plugin MantisBT
   @author    Stanislas Kita (teclib')
   @co-author François Legastelois (teclib')
   @co-author Le Conseil d'Etat
   @copyright Copyright (c) 2014 GLPI Plugin MantisBT Development team
   @license   GPLv3 or (at your option) any later version
              http://www.gnu.org/licenses/gpl.html
   @link      https://forge.indepnet.net/projects/mantis
   @since     2014

   ------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

global $CFG_GLPI;

$root_ajax = $CFG_GLPI['root_doc']."/plugins/mantis/ajax/ajax.php";

$JS = <<<JAVASCRIPT

/**
 * function to test connection with Mantis Web Service
 * @returns {boolean}
 */
function testConnexionMantisWS() {

 var dropdown = $("#dropdown_etatMantis");
   var div = $("#infoAjax");

   $.ajax({ // fonction permettant de faire de l'ajax
      type: "POST", // methode de transmission des données au fichier php
      url: "{$root_ajax}", // url du fichier php
      data: "action=testConnexionMantisWS&" +
         "host=" + $("#host").val() + "&" +
         "url=" + $("#url").val() + "&" +
         "login=" + $("#login").val() + "&" +
         "pwd=" + $("#pwd").val(), // données à transmettre
      success: function (msg) { // si l'appel a bien fonctionné


         div.html(msg);

          if (msg.indexOf('check') != -1) {
            addStateToSelect();
          }else{
          removeOptionOfSelect(dropdown);
          }


      },
      error: function () {
         div.html('Ajax Problem !');
      }

   });
   return false; // permet de rester sur la même page à la soumission du formulaire

}

function ifExistissueWithId() {

   var div = $("#infoFindIssueMantis");
   var id = $("#idMantis").val();

   $.ajax({ // fonction permettant de faire de l'ajax
      type: "POST", // methode de transmission des données au fichier php
      url: "{$root_ajax}", // url du fichier php
      data: "action=findIssueById&" +
         "id=" + id, // données à transmettre
      success: function (msg) { // si l'appel a bien fonctionné
         div.html(msg);
      },
      error: function () {
         div.html("Ajax problem !");
      }

   });
   return false; // permet de rester sur la même page à la soumission du formulaire

}

function linkIssueglpiToIssueMantis() {

   var idMantisIssue = $("#idMantis").val();
   var idTicket = $("#idTicket").val();
   var idUser = $("#user").val();
   var date = $("#dateEscalade").val();

   var div_info = $("#infoLinIssueGlpiToIssueMantis");
   var div_wait = $("#waitForLinkIssueGlpiToIssueMantis");

   div_info.empty();
   div_wait.css('display', 'block');

   if(idMantisIssue.trim() == "" || idMantisIssue == null){
      $("#idMantis").css('border-color','red');
      div_wait.css('display', 'none');
   }else{

      $("#idMantis").css('border-color','#888888');
      $.ajax({ // fonction permettant de faire de l'ajax
         type: "POST", // methode de transmission des données au fichier php
         url: "{$root_ajax}", // url du fichier php
         data: "action=LinkIssueGlpiToIssueMantis&" +
            "idTicket=" + idTicket + "&" +
            "idMantis=" + idMantisIssue + "&" +
            "user=" + idUser + "&" +
            "dateEscalade=" + date, // données à transmettre
         success: function (msg) { // si l'appel a bien fonctionné

            if (msg == true) {
               div_wait.css('display', 'none');
               popupLinkGlpiIssuetoMantisIssue.hide();
               window.location.reload();
            }
            else {
               div_wait.css('display', 'none');
               div_info.html(msg);
            }
         },
         error: function () {
            div_wait.css('display', 'none');
            div_info.html("Probleme Ajax");
         }

      });
   }
}

function showalert() {
   alert("coco");
   return false;
}

function closePopup() {
   window.opener.location.reload(true);
   window.close();

}

function findProjectByName() {

   var td = $("#tdSearch");
   var name = $("#nameMantisProject").val();
   var img = $("#resultImg");
   var dropdown = $("#dropdown_categorie");
   var div_wait = $("#waitForLinkIssueGlpiToProjectMantis");
   var dropdownAssignation = $("#dropdown_assignation");

   div_wait.css('display', 'block');
   img.remove();

   $.ajax({ // fonction permettant de faire de l'ajax
      type: "POST", // methode de transmission des données au fichier php
      url: "{$root_ajax}", // url du fichier php
      data: "action=findProjectByName&" +
         "name=" + name, // données à transmettre
      success: function (msg) { // si l'appel a bien fonctionné

         td.append(msg);

         if (msg.indexOf('check') != -1) {
            addOptionToSelect(dropdown, name);
            addActortoSelect(dropdownAssignation , name);
            div_wait.css('display', 'none');
         } else {
            removeOptionOfSelect(dropdown);
            removeOptionOfSelect(dropdownAssignation);
            div_wait.css('display', 'none');
         }

      },
      error: function () {
         div_wait.css('display', 'none');
         td.append("Ajax problem !");
      }

   });
   return false; // permet de rester sur la même page à la soumission du formulaire

}


function addStateToSelect(){

  var dropdown = $("#dropdown_etatMantis");

       $.ajax({ // fonction permettant de faire de l'ajax
      type: "POST", // methode de transmission des données au fichier php
      url: "{$root_ajax}", // url du fichier php
      data: "action=getStateMantis&" +
         "host=" + $("#host").val() + "&" +
         "url=" + $("#url").val() + "&" +
         "login=" + $("#login").val() + "&" +
         "pwd=" + $("#pwd").val(), // données à transmettre
      success: function (msg) { // si l'appel a bien fonctionné

         if (msg == false) {

         } else {
            var myOptions = msg.toString().split(',');

            var mySelect = dropdown;

            removeOptionOfSelect(dropdown);

            $.each(myOptions, function (val, text) {
               mySelect.append(
                  $('<option></option>').val(text).html(text)
               );
            });
         }

      },
      error: function () {
         alert('pb ajax');
      }


   });


}

function addActortoSelect(dropdown,name) {

   var nameProject = name;

   $.ajax({ // fonction permettant de faire de l'ajax
      type: "POST", // methode de transmission des données au fichier php
      url: "{$root_ajax}", // url du fichier php
      dataType: "json",
      data: "action=getActorByProjectname&" +
         "name=" + name, // données à transmettre
      success: function (msg) { // si l'appel a bien fonctionné

         if (msg == false) {

         } else {

            var myOptions = msg.toString().split(',');
            var mySelect = dropdown;

            removeOptionOfSelect(dropdown);

            for(var i=0;i<msg.length; i++){
               obj = msg[i];
               mySelect.append( $('<option></option>').val(obj.id).html(obj.name) );
            }

         }

      },
      error: function () {
         alert('pb ajax');
      }

   });
   return false; // permet de rester sur la même page à la soumission du formulaire

}




function addOptionToSelect(dropdown, name) {

   var nameProject = name;

   $.ajax({ // fonction permettant de faire de l'ajax
      type: "POST", // methode de transmission des données au fichier php
      url: "{$root_ajax}", // url du fichier php
      dataType: "json",
      data: "action=getCategoryFromProjectName&" +
         "name=" + name, // données à transmettre
      success: function (msg) { // si l'appel a bien fonctionné

         if (msg == false) {

         } else {
            var myOptions = msg.toString().split(',');
            var mySelect = dropdown;

            removeOptionOfSelect(dropdown);

            $.each(myOptions, function (val, text) {
               mySelect.append(
                  $('<option></option>').val(val).html(text)             );
            });
         }

      },
      error: function () {
         alert('pb ajax');
      }


   });
   return false; // permet de rester sur la même page à la soumission du formulaire

}

function removeOptionOfSelect(dropdown) {
   dropdown.find('option').remove()
}

function linkIssueglpiToProjectMantis() {

   var nameMantisProject = $("#nameMantisProject").val();
   var cate = $("#dropdown_categorie").find(":selected").text();
   var assign = $("#dropdown_assignation").find(":selected").val();
   var resume = $("#resume").val();
   var description = $("#description").val();
   var stepToReproduce = $("#stepToReproduce").val();
   var followAttachment = $("#followAttachment").is(':checked');
   var idTicket = $("#idTicket").val();
   var idUser = $("#user").val();
   var date = $("#dateEscalade").val();

   var div_info = $("#infoLinkIssueGlpiToProjectMantis");
   var div_wait = $("#waitForLinkIssueGlpiToProjectMantis");

   div_info.empty();
   div_wait.css('display', 'block');


   if((resume == null || resume == "")||(description.length < 1)){

      if(resume == null || resume == ""){
         $("#resume").css('border-color','red');
      }else{
         $("#resume").css('border-color','#888888');
      }

      if(description.length < 1){
         $("#description").css('border-color','red');
      }else{
         $("#description").css('border-color','#888888');
      }

      div_wait.css('display', 'none');

   }else{

      $.ajax({ // fonction permettant de faire de l'ajax
         type: "POST", // methode de transmission des données au fichier php
         url: "{$root_ajax}", // url du fichier php
         data: "action=LinkIssueGlpiToProjectMantis&" +
            "idTicket=" + idTicket + "&" +
            "nameMantisProject=" + nameMantisProject + "&" +
            "user=" + idUser + "&" +
            "dateEscalade=" + date + "&" +
            "resume=" + resume + "&" +
             "assign=" + assign + "&" +
            "stepToReproduce=" + stepToReproduce + "&" +
            "followAttachment=" + followAttachment + "&" +
            "categorie=" + cate + "&" +
            "description=" + description, // données à transmettre
         success: function (msg) { // si l'appel a bien fonctionné

            if (msg == true) {
               div_wait.css('display', 'none');
               popupLinkGlpiIssuetoMantisProject.hide();
               window.location.reload();
            }
            else {
               div_wait.css('display', 'none');
               div_info.html(msg);
            }

         },
         error: function () {

            div_wait.css('display', 'none');
            div_info.html("Probleme Ajax");

         }

      });
   }
}

function deleteLinkGlpiMantis(id, idticket, idMantis, deleteAll) {

   var question = "";
   if (deleteAll) question = "Vous allez supprimer l'issue MantisBT ainsi que le lien";
   else question = "Vous allez supprimer le lien vers l'issue mantisBT";

   //alert (id+" --- "+idticket+" --- "+idMantis);

   if (confirm(question)) {

      $.ajax({ // fonction permettant de faire de l'ajax
         type: "POST", // methode de transmission des données au fichier php
         url: "{$root_ajax}", // url du fichier php
         data: "action=deleteLinkMantis&" +
            "id=" + id + "&" +
            "idMantis=" + idMantis + "&" +
            "idTicket=" + idticket,// données à transmettre
         success: function (msg) { // si l'appel a bien fonctionné

            if (msg == true) {
               window.location.reload();
            }
            else {
               alert(msg);
            }

         },
         error: function () {
            alert("Ajax problem");
         }

      });

   }

}

function delLinkAndOrIssue(id, idMantis, idTicket) {

   var checkIssue = $('#deleteIssue' + id);
   var checkLink = $('#deleteLink' + id);

   var div_wait = $('#waitDelete' + id);
   var div_info = $('#infoDel' + id);

   var popupName = "popupToDelete" + id;
   var popup = $('input[name="' + popupName + '"]');

   if (checkIssue.is(':checked') && !checkLink.is(':checked') ||
      checkIssue.is(':checked') && checkLink.is(':checked')) {

      div_wait.css('display', 'block');
      $.ajax({ // fonction permettant de faire de l'ajax
         type: "POST", // methode de transmission des données au fichier php
         url: "{$root_ajax}", // url du fichier php
         data: "action=deleteIssueMantisAndLink&" +
            "id=" + id + "&" +
            "idMantis=" + idMantis + "&" +
            "idTicket=" + idTicket,// données à transmettre
         success: function (msg) { // si l'appel a bien fonctionné

            if (msg == true) {
               div_wait.css('display', 'none');
               eval(popupName).hide();
               window.location.reload();
            }
            else {
               div_wait.css('display', 'none');
               div_info.html(msg);
            }

         },
         error: function () {
            div_wait.css('display', 'none');
            div_info.html('Problem Ajax');
         }

      });
   }

   if (!checkIssue.is(':checked') && checkLink.is(':checked')) {

      div_wait.css('display', 'block');
      $.ajax({ // fonction permettant de faire de l'ajax
         type: "POST", // methode de transmission des données au fichier php
         url: "{$root_ajax}", // url du fichier php
         data: "action=deleteLinkMantis&" +
            "id=" + id + "&" +
            "idMantis=" + idMantis + "&" +
            "idTicket=" + idTicket,// données à transmettre
         success: function (msg) { // si l'appel a bien fonctionné

            if (msg == true) {
               div_wait.css('display', 'none');
               eval(popupName).hide();
               window.location.reload();
            }
            else {
               div_wait.css('display', 'none');
               div_info.html(msg);
            }

         },
         error: function () {
            div_wait.css('display', 'none');
            div_info.html('Problem Ajax');
         }

      });
   }

}


function getBaseURL() {
   var url = location.href;
   var baseURL = url.substring(0, url.indexOf('/', 14));

   if (baseURL.indexOf('http://localhost') != -1) {
      var pathname = location.pathname;
      var index1 = url.indexOf(pathname);
      var index2 = url.indexOf("/", index1 + 1);
      var baseLocalUrl = url.substr(0, index2);

      return alert(baseLocalUrl);
   }
   else {
      return alert(baseURL);
   }

}
JAVASCRIPT;

echo $JS;

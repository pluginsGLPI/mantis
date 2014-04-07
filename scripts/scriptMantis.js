

/**
 * function to test connection with Mantis Web Service
 * @returns {boolean}
 */
function testConnexionMantisWS(){

            $.ajax({ // fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "../ajax/ajax.php", // url du fichier php
                data: "action=testConnexionMantisWS&" +
                    "host="+$("#host").val()+"&" +
                    "url="+$("#url").val()+"&" +
                    "login="+$("#login").val()+"&"+
                    "pwd="+$("#pwd").val(), // données à transmettre
                success: function(msg){ // si l'appel a bien fonctionné

                    var $div = $( "#infoAjax" );

                    if(msg==true) {
                        $div.html('<img id="img" src="../pics/check24.png" />');
                    }
                    else {
                        $div.html('<img id="img" src="../pics/cross24.png" />');
                    }

                },
                error : function(){
                    $( "#infoAjax" ).html('<img id="img" src="../pics/cross24.png" />Ajax Problem !');
                }

            });
            return false; // permet de rester sur la même page à la soumission du formulaire

}




/**
 * function to check IP Format
 * @returns {boolean}
 */
function testIP(){

    //alert();

    var ip = $("#host").val();
    var $div = $("#resultIP");

        if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ip))
        {
            $div.html('<img id="img" src="../pics/check24.png" />');
            return (true)
        }else{
            $div.html('<img id="img" src="../pics/cross24.png" />');
            return (false)
        }
}



function ifExistissueWithId(){



    var id = $("#idMantis").val();

    $.ajax({ // fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
        data: "action=findIssueById&" +
            "id="+id, // données à transmettre
        success: function(msg){ // si l'appel a bien fonctionné

            var $div = $( "#infoFindIssueMantis");

            if(msg==true) {
                $div.html('<img id="img" src="../../glpi/plugins/mantis/pics/check24.png" />');
            }
            else {
                $div.html('<img id="img" src="../../glpi/plugins/mantis/pics/cross24.png" />');
            }

        },
        error : function(){
            $( "infoFindIssueMantis" ).html("<img id='img' src='../../glpi/plugins/mantis/pics/cross24.png' /> Problème Ajax !");
        }

    });
    return false; // permet de rester sur la même page à la soumission du formulaire

}




function linkIssueglpiToIssueMantis(){

    var idMantisIssue = $("#idMantis").val();
    var idTicket = $("#idTicket").val();
    var idUser = $("#user").val();
    var date = $("#dateEscalade").val();

   var div_info = $("#infoLinIssueGlpiToIssueMantis");
   var div_wait = $("#waitForLinkIssueGlpiToIssueMantis");

   div_info.empty();
   div_wait.css('display', 'block');


    $.ajax({ // fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url:  "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
        data: "action=LinkIssueGlpiToIssueMantis&" +
              "idTicket="+idTicket+"&"+
              "idMantis="+idMantisIssue+"&"+
              "user="+idUser+"&"+
              "dateEscalade="+date , // données à transmettre
        success: function(msg){ // si l'appel a bien fonctionné



            if(msg==true) {
               div_wait.css('display', 'none');
                popupLinkGlpiIssuetoMantisIssue.hide();
                window.location.reload();
            }
            else {
               div_wait.css('display', 'none');
               div_info.html(msg);
            }
        },
        error : function(){
           div_wait.css('display', 'none');
           div_info.html("Probleme Ajax");
        }

    });


}



function showalert(){
    alert("coco");
    return false;
}

function closePopup(){

    window.opener.location.reload(true);
    window.close();

}


function findProjectByName(){

    var td = $("#tdSearch");
    var name = $("#nameMantisProject").val();
    var img = $("#resultImg");
    var dropdown = $("#dropdown_categorie");

   var div_wait = $("#waitForLinkIssueGlpiToProjectMantis");

    div_wait.css('display', 'block');
    img.remove();

    $.ajax({ // fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
        data: "action=findProjectByName&" +
            "name="+name, // données à transmettre
        success: function(msg){ // si l'appel a bien fonctionné

            if(msg==true) {
                td.append('<img id="resultImg" src="../../glpi/plugins/mantis/pics/check24.png" />');
                addOptionToSelect(dropdown,name);
                div_wait.css('display', 'none');

            }
            else {
                td.append('<img id="resultImg" src="../../glpi/plugins/mantis/pics/cross24.png" />');
                removeOptionOfSelect(dropdown);
                div_wait.css('display', 'none');
            }

        },
        error : function(){
            div_wait.css('display', 'none');
            td.append("<img id='resultImg' src='../../glpi/plugins/mantis/pics/cross24.png' /> Ajax problem !");

        }

    });
    return false; // permet de rester sur la même page à la soumission du formulaire

}




function addOptionToSelect(dropdown, name) {

   var nameProject = name;

   $.ajax({ // fonction permettant de faire de l'ajax
      type: "POST", // methode de transmission des données au fichier php
      url: "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
      dataType: "json",
      data: "action=getCategoryFromProjectName&" +
         "name=" + name, // données à transmettre
      success: function (msg) { // si l'appel a bien fonctionné

         if (msg == false) {

         }else{
            var myOptions = msg.toString().split(',');
            var mySelect = dropdown;

            removeOptionOfSelect(dropdown);

            $.each(myOptions, function (val, text) {
               mySelect.append(
                  $('<option></option>').val(val).html(text)
               );
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

   $.ajax({ // fonction permettant de faire de l'ajax
      type: "POST", // methode de transmission des données au fichier php
      url: "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
      data: "action=LinkIssueGlpiToProjectMantis&" +
         "idTicket=" + idTicket + "&" +
         "nameMantisProject=" + nameMantisProject + "&" +
         "user=" + idUser + "&" +
         "dateEscalade=" + date + "&" +
         "resume=" + resume + "&" +
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

function deleteLinkGlpiMantis(id , idticket, idMantis , deleteAll){

   var $question = "";
   if(deleteAll) $question = "Vous allez supprimer l'issue MantisBT ainsi que le lien";
   else $question = "Vous allez supprimer le lien vers l'issue mantisBT";

   //alert (id+" --- "+idticket+" --- "+idMantis);


   if (confirm($question)) {

      $.ajax({ // fonction permettant de faire de l'ajax
         type: "POST", // methode de transmission des données au fichier php
         url: "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
         data: "action=deleteLinkMantis&" +
            "id=" + id+"&"+
            "idMantis=" + idMantis+"&"+
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





function delLinkAndOrIssue(id,idMantis,idTicket){

   var checkIssue = $('#deleteIssue'+id);
   var checkLink = $('#deleteLink'+id);

   var div_wait= $('#waitDelete'+id);
   var div_info = $('#infoDel'+id);

   var popupName = "popupToDelete"+id;
   var popup = $('input[name="'+popupName+'"]');




   //alert(id+" --- "+idTicket+" --- "+idMantis);

   if(checkIssue.is(':checked') && !checkLink.is(':checked') ||
      checkIssue.is(':checked') && checkLink.is(':checked')){

      div_wait.css('display', 'block');
      $.ajax({ // fonction permettant de faire de l'ajax
         type: "POST", // methode de transmission des données au fichier php
         url: "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
         data: "action=deleteIssueMantisAndLink&" +
            "id=" + id+"&"+
            "idMantis=" + idMantis+"&"+
            "idTicket=" + idticket,// données à transmettre
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

   if (!checkIssue.is(':checked') && checkLink.is(':checked')){

      div_wait.css('display', 'block');
      $.ajax({ // fonction permettant de faire de l'ajax
         type: "POST", // methode de transmission des données au fichier php
         url: "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
         data: "action=deleteLinkMantis&" +
            "id=" + id+"&"+
            "idMantis=" + idMantis+"&"+
            "idTicket=" + idticket,// données à transmettre
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



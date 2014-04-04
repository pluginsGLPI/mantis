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
                        $div.html('<img id="img" src="../pics/check24.png" /> Connexion réussie !');
                    }
                    else {
                        $div.html('<img id="img" src="../pics/cross24.png" /> Connexion échoué !');
                    }

                },
                error : function(){
                    $( "#infoAjax" ).html('<img id="img" src="../pics/cross24.png" /> Problème Ajax !');
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

    $.ajax({ // fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url:  "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
        data: "action=LinkIssueGlpiToIssueMantis&" +
              "idTicket="+idTicket+"&"+
              "idMantis="+idMantisIssue+"&"+
              "user="+idUser+"&"+
              "dateEscalade="+date , // données à transmettre
        success: function(msg){ // si l'appel a bien fonctionné

            var $div = $( "#infoLinIssueGlpiToIssueMantis");

            if(msg==true) {
                popupLinkGlpiIssuetoMantisIssue.hide();
                window.location.reload();
            }
            else {
                $div.html(msg);
            }
        },
        error : function(){
            $( "#infoLinIssurGlpiToIssueMantis").html("Probleme Ajax");
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

            }
            else {
                td.append('<img id="resultImg" src="../../glpi/plugins/mantis/pics/cross24.png" />');
                removeOptionOfSelect(dropdown);
            }

        },
        error : function(){
            td.append("<img id='resultImg' src='../../glpi/plugins/mantis/pics/cross24.png' /> Problème Ajax !");
        }

    });
    return false; // permet de rester sur la même page à la soumission du formulaire

}


function addOptionToSelect(dropdown, name){

    var nameProject = name;




    $.ajax({ // fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
        dataType : "json",
        data: "action=getCategoryFromProjectName&" +
            "name="+name, // données à transmettre
        success: function(msg){ // si l'appel a bien fonctionné

            if(msg==false) {

            }
            else {

                var myOptions = msg.toString().split(',');
                var mySelect = dropdown;

                removeOptionOfSelect(dropdown);

                $.each(myOptions, function(val, text) {
                    mySelect.append(
                        $('<option></option>').val(val).html(text)
                    );
                });

            }

        },
        error : function(){
            alert('pb ajax');
        }


    });
    return false; // permet de rester sur la même page à la soumission du formulaire




}



function removeOptionOfSelect(dropdown) {

    dropdown.find('option').remove()

}



function linkIssueglpiToProjectMantis(){

    var nameMantisProject = $("#nameMantisProject").val();

    var cate =   $("#dropdown_categorie").find(":selected").text();
    var resume = $("#resume").val();
    var description = $("#description").val();
    var stepToReproduce = $("#stepToReproduce").val();
    var followAttachment = $("#followAttachment").is(':checked');

    var idTicket = $("#idTicket").val();
    var idUser = $("#user").val();
    var date = $("#dateEscalade").val();



    $.ajax({ // fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url:  "../../glpi/plugins/mantis/ajax/ajax.php", // url du fichier php
        data: "action=LinkIssueGlpiToProjectMantis&" +
            "idTicket="+idTicket+"&"+
            "nameMantisProject="+nameMantisProject+"&"+
            "user="+idUser+"&"+
            "dateEscalade="+date+"&"+
            "resume="+resume+"&"+
            "stepToReproduce="+stepToReproduce+"&"+
            "followAttachment="+followAttachment+"&"+
            "categorie="+cate+"&"+
            "description="+description, // données à transmettre
        success: function(msg){ // si l'appel a bien fonctionné

            var $div = $( "#infoLinIssueGlpiToProjectMantis");

            if(msg==true) {
                popupLinkGlpiIssuetoMantisProject.hide();
                window.location.reload();
            }
            else {
                $div.html(msg);
            }
        },
        error : function(){
            $( "#infoLinIssueGlpiToProjectMantis").html("Probleme Ajax");
        }

    });


}
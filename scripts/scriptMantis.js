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
 * function to update a link between glpi ticket field and mantis issue field
 * @param id of linkField Tto
 * @param element <OPTION selected='selected'></OPTION>
 * @returns {boolean}
 */
function updateLinkField(id , element){

    var idLinktoUpdate = id;
    var idValueSelected = element.selectedIndex;


    $.ajax({ // fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "../ajax/ajax.php", // url du fichier php
        data: "action=updateLinkField&" +
            "id="+idLinktoUpdate+"&" +
            "fieldMantis="+idValueSelected, // données à transmettre
        success: function(msg){ // si l'appel a bien fonctionné

            var $div = $( "#infoAjaxLink"+id );

            if(msg==true) {
                $div.html('<img id="img" src="../pics/check24.png" />');
            }
            else {
                $div.html('<img id="img" src="../pics/cross24.png" />');
            }

        },
        error : function(){
            $( "#infoAjaxLink"+id ).html('<img id="img" src="../pics/cross24.png" /> Problème Ajax !');
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


GLPI Plugin mantis
=============

Introduction
-------------

Ce plugin permet une synchronisation simple entre les tickets GLPI et les issues MantisBT.

Voici une liste de ses fonctionnalités actuelles :

* connexion via les Webservices de MantisBT (MantisConnect)

* création d'une nouvelle issue MantisBT depuis un ticket GLPI

* liaison d'une issue MantisBT existante depuis un ticket GLPI

* transmission des informations du ticket GLPI vers l'issue MantisBT créée/liéée

* transfert des pièces jointes du ticket GLPI vers l'issue MantisBT créée/liéée

* tâche automatique de mise à jour du ticket GLPI (passage à l'état résolu) lors que l'issue MantisBT est résolue

* gestion des droits sur le plugin

* gestion de la configuration de l'interconnexion


Documentation
-------------

Actuellement disponible dans le dossier *docs* du plugin téléchargé.


*Serveur MantisBT de test*

* [http://demo.teclib.net/mantis/](http://demo.teclib.net/mantis/)

* WS : [http://demo.teclib.net/mantis/api/soap/mantisconnect.php?wsdl](http://demo.teclib.net/mantis/api/soap/mantisconnect.php?wsdl)

* Utilisateur Reporter : *test-glpi* / *test-glpi*

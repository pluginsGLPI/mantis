# Plugin GLPi - Synchronisation MantisBT

## French

### Description

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

### Documentation

Actuellement disponible dans le dossier *docs*.

### Processus de développement
 
Pour développer ce plugin, nous utilisons au maximum le workflow GitFlow :
    
http://nvie.com/posts/a-successful-git-branching-model/
      
Merci de respecter ce workflow :)

## English

### Description

This plugin allows to synchronize tickets, problems and changes with the MantisBT tool:

Here is a list of current features:

* Connection via webservices of MantisBT (MantisConnect);

* Creation of a new MantisBT issue from a ticket, problem or GLPi change;

* Link an existing MantisBT issue from a ticket, problem or GLPi change;

* Transmission of information from the GLPi object to the created / linked MantisBT issue;

* Transfer and auto-update attachments of GLPi object to the MantisBT created / linked issue;

* Automatic update of GLPi object (transition to the resolved state) when the MantisBT issue is resolved;

* Management rights for the plugin;

* Configuration management of interconnection.

### Documentation

Only available in French in *docs* directory.

### Development workflow
 
To develop this plugin, we've used the GitFlow workflow:
    
http://nvie.com/posts/a-successful-git-branching-model/
      
Please respect the workflow :)

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

### Contribuer

* Ouvrez un ticket pour chaque bogue/fonctionnalité que cela puisse être discuté
* Suivez [les règles de développement](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins.html)
* Référez-vous au processus [GitFlow](http://git-flow.readthedocs.io/) pour gérer les branches
* Travaillez sur une nouvelle branche sur votre fork
* Ouvreez une PR qui sera revue par un développeur

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

### Contributing

* Open a ticket for each bug/feature so it can be discussed
* Follow [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins.html)
* Refer to [GitFlow](http://git-flow.readthedocs.io/) process for branching
* Work on a new branch on your own fork
* Open a PR that will be reviewed by a developer

Please respect the workflow :)

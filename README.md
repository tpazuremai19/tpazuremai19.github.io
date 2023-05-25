# tpazuremai19.github.io

![image](https://github.com/tpazuremai19/tpazuremai19.github.io/assets/134396376/7a3158e3-0494-4b82-af6d-13801d6e4de3) 


*******

## Table des matières

 - [INTRODUCTION](https://github.com/tpazuremai19/tpazuremai19.github.io/edit/master/README.md#introduction)
 - [PRÉREQUIS](#PRÉREQUIS)
 - [Sécurité](#sécurité)
 - [Inscription GitLab](#inscription)
 - [Créer un projet](#créer-un-projet)
 - [Fourcher (forker) un projet](#fourcher-forker-un-projet)
 - [Gestion des fichiers](#gestion-des-fichiers)
 - [Demandes de fusion](#demandes-de-fusion)
 - [Le format Markdown](#am%C3%A9liorer-ses-textes-avec-le-format-markdown)
 - [Gestion des issues](#les-issues)
 - [FAQ](#faq)
 - [Liens](#liens)
 - [Glossaire](#glossaire)


*******


## INTRODUCTION


Dans une logique d'intégration continue, nous avons besoin d’un outil de vérification du code à des fins de fiabilisation et de sécurisation de celui-ci.

Pour ce faire, SonarQube et Jenkins ont été recommandés et mis en place pour tester notre code sur Github.






## PRÉREQUIS


### Création d'une infrastructure Azure cloud avec :
  - Une machine linux avec docker, pour y installer Sonarqube & Jenkins
      sous debian 11 x64
      2 Vcpu
      8 Go RAM
      30 Go stockage

  - Une machine Linux avec LAMP, pour y installer notre serveur web et notre bdd. (nous avons été limité à deux machines, le mieux est de créer une machine par service)
    sous Ubuntu 20.04 LTS x64
    2 Vcpu
    8 Go RAM
    30 Go stockage



## CONFIGURATION WEB & BDD


Création d'un site web en PHP, avec des failles XSS & SQL pour tester le fonctionnement **(voir fichier index.php)**

Voici les commande pour installer __apache2, mysql et php__ :
```
sudo apt-get update
sudo apt-get install apache2 php libapache2-mod-php mariadb-server php-mysql
```

### Installer les modules php :
```
sudo apt-get install php-curl php-gd php-intl php-json php-mbstring php-xml php-zip
```
On insère la configuration du site dans le fichier **/etc/apache2/sites-available/001-klite.conf :**

```
<VirtualHost *:80>
    	#ServerName exemple.fr
    	#ServerAlias *.exemple.fr
    	DocumentRoot "/var/www/klite"
    	DirectoryIndex index.php
    	<Directory "/var/www/klite">
            	Options -Indexes +FollowSymLinks
            	AllowOverride none
            	Require all granted
    	</Directory>
    	ErrorLog ${APACHE_LOG_DIR}/klite-error.log
    	CustomLog ${APACHE_LOG_DIR}/klite-access.log combined
</VirtualHost>
```

On désactive le site par défaut et on active notre site, puis on redémarre apache :
```
a2dissite 000-default.conf

a2ensite 001-klite.conf

systemctl restart apache2
```

Ensuite on créer une règle crontab pour importer notre code depuis github vers notre serveur web (nous utilisons la crontab de l’utilisateur standard pour des questions de sécurité).
On créer le fichier suivant et on donne les droits à l’utilisateur sur le fichier du site web :
```
touch /var/www/klite/index.php
chown mael: /var/www/klite/index.php
```
on édite la crontab depuis l’utilisateur en question, ici ça sera mael :
```
su mael
crontab -e
```
On insère la commande suivante à la fin de crontab :
```
*/1 * * * * curl https://raw.githubusercontent.com/tpazuremai19/tpazuremai19.github.io/master/index.php > /var/www/klite/index.php
```


### Serveur BDD :
  - Installation de phpmyadmin
  - Création de la base de donnée
  - Création des différentes tables 
  - Création des utilisateurs dans les tables, et de leurs informations


### Installation de phpmyadmin : 
```
apt install phpmyadmin
```

On laisse les paramètres par défaut et on choisit notre utilisateur et notre mot de passe.
Il est possible de reconfigurer phpmyadmin avec la commande suivante :
```
dpkg-reconfigure phpmyadmin
```

On donne maintenant les droits sur notre BDD à notre utilisateur (dans notre cas on donne tous les droits à l’utilisateur car il n’y aura que notre site web sur cette BDD sinon on aurait du remplacer *.* par mydatabase.*) :
```
mariadb
CREATE USER 'mael'@'localhost' identified by 'yourmdp';
GRANT ALL ON *.* TO 'mael'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
QUIT;
```


### Liaison du serveur web à la base de donnée :
  - Ajout des informations de connexion à la base de donnée dans le code php
  - Mise en place de la connexion BDD avec PDO


   ##                INSTALLATION SONARQUBE & JENKINS


Sur notre machine debian, nous allons installer docker à l’aide du script disponible sur : 
https://get.docker.com/

Ensuite, il faut pull les images de jenkins & SonarQube : 
```
docker pull sonarsource/sonar-scanner-cli
docker pull jenkins:latest
```

Une fois installés, il nous faut les lancer. par défaut, SonarQube tourne sur le port 9000 et jenkins le 8080 : 
```
sudo docker run -d --name sonarqube -p 9000:9000 -p 9092:9092 sonarqube
sudo docker run -p 8080:8080 --name=jenkins-master -d jenkins/jenkins jenkins:jenkins
```


## CONFIGURATION SONARQUBE


Sur l’interface, il faut aller dans Administration>Projects>Management>Create Project

Nous créons notre projet avec un nom et une clée.
Ensuite, nous cliquons sur le projet, et il nous propose de configurer SonarQube avec une CI. dans notre cas, avec Jenkins.
Nous choisissons ensuite Github.

nous arrivons aux prérequis : 
Avoir  SonarQube scanner plugin for Jenkins, il doit etre installé en version 2.11 ou plus
ce site montre comment l’installer : https://docs.sonarqube.org/10.0/analyzing-source-code/ci-integration/jenkins-integration/

Créer un pipeline Job : 
1. From Jenkins' dashboard, click New Item and create a Pipeline Job.
2. Under Build Triggers, choose Trigger builds remotely. You must set a unique, secret token     for this field.

3. Under Pipeline, make sure the parameters are set as follows:
Definition: Pipeline script from SCM
SCM: Configure your SCM. Make sure to only build your main branch. For example, if your main branch is called "main", put "*/main" under Branches to build.
Script Path: Jenkinsfile

4. Click Save.


Créer un Github Webhook, avec ces infos : 
***JENKINS_SERVER_URL***/job/***JENKINS_JOB_NAME***/build?token=***JENKINS_BUILD_TRIGGER_TOKEN***

Création du Jenkinsfile : 
spécifier Maven, il va ensuite vous donner un fichier, que vous devrez insérer dans votre projet Github.
On fait ensuite suivant, il ne reste plus qu'à lancer le build depuis jenkins



## CONFIGURATION JENKINS



Installer le plug-in SonarQube Scanner

Tableau de bord > Administrer Jenkins > Plugins > Available plugins>SonarQube Scanner for Jenkins

qui va nous servir de lui entre jenkins et sonarQube 

Générer un jeton dans SonarQube Server

l'icône de votre compte administrateur > Mon compte > Sécurité > Generate Tokens 

Configurer SonarQube dans Jenkins

Tableau de bord > Administrer Jenkins > Configurer le système > configuration de SonarQube > cliquez sur Ajouter SonarQube > Server authentication token > Ajouter > Jenkins 

Domaine : Identifiants Globaux 
Type : Secret text 
	Portée:  Global 
	Secret : Token generate
	ID: nom interne
	Description : nom interne

Puis ajouter


Configurer le scanner SonarQube

Tableau de bord > Administrer Jenkins > Tools > Scanner SonarQube


 Ajouter un scanner SonarQube : 
SonarQube Scanner Name : Name 
SONAR_RUNNER_HOME : /opt/sonarqube/bin 

Décochez installer automatiquement

Nouveau Item > Pipeline >  

cocher GitHub hook trigger for GITScm polling

Definition : Pipeline script from SCM 
	SCM: Git 
	Repository URL : https://github.com/tpazuremai19/tpazuremai19.github.io.git
	Credentials :  Token 
	Branch Specifier (blank for 'any') : */master branch Github
	Script Path : Jenkinsfile-1 





 ## BIBLIOGRAPHIE


lien pour docker: 
```
https://get.docker.com/
```
lien pour download SonarQube: 
```
https://www.sonarsource.com/products/sonarqube/downloads/success-download-community-edition/
```

intégrez Jenkins:
```
https://docs.sonarqube.org/latest/analyzing-source-code/scanners/jenkins-extension-sonarqube/
```
```
https://www.youtube.com/watch?v=KsTMy0920go
```
```
https://intellitech.pro/fr/integrer-sonarqube-avec-jenkins/
```
```
https://computingforgeeks.com/how-to-integrate-sonarqube-with-jenkins/?utm_content=cmp-true
```

lien pour installe OWASP Dependency-Check:
```
https://plugins.jenkins.io/dependency-check-jenkins-plugin/
```




## ANNEXES


![image](https://github.com/tpazuremai19/tpazuremai19.github.io/assets/134396376/a7822600-34ec-4f63-b3a3-24dbda713131)

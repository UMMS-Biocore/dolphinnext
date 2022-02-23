************
Introduction
************

DolphinNext's main server is located at https://dolphinnext.umassmed.edu. DolphinNext can also be run as a standalone application using a docker container.

First docker image need to be build unless you want to use prebuild from dockerhub. So, any change in the Dockerfile requires to build the image. But if you want to use prebuild version just skip building it and start the container.

Build docker image
------------------

1. To build docker image first clone one of the latest dolphinnext-docker::

    git clone https://github.com/UMMS-Biocore/dolphinnext-studio.git

2. Build the image::
  
    cd dolphinnext-studio 
    docker build -t dolphinnext-studio .

Start the container
-------------------

1. We move database outside of the container to be able to keep the changes in the database everytime you start the container. Please choose a directory in your machine to mount and replace ``/path/to/mount`` with your path:: 

    mkdir -p /path/to/mount
    
.. note:: Please don't change the target directory(``/export``) in the docker image.
    

2. Running the container::

    docker run -m 10G -p 8080:80 -v /path/to/mount:/export -ti dolphinnext-docker /bin/bash

* if you want to run a pre-build::

    docker run -m 10G -p 8080:80 -v /path/to/mount:/export -ti ummsbiocore/dolphinnext-studio /bin/bash

3. After you start the container, you need to start the mysql and apache server usign the command below::

    startup

4. Verify that **dolphinnext** and **mysql** folders located inside of the **/export** folder::

    ls /export

5. Now, you can open your browser to access dolphinnext using the url below::

    http://localhost:8080/dolphinnext


Configuration of the .sec file
------------------------------

This is the main DolphinNext configuration file and located in the ``dolphinnext/config/.sec`` file. 

.. tip:: If you're planing to use DolphinNext on the Amazon or Google Cloud, please check `Running on the Amazon or Google Cloud <admin_faq.html#running-on-the-amazon-or-google-cloud>`_ section.

``.sec`` file contains the following configuration directives, lets start with **Dolphinnext** section::

    [Dolphinnext]
    DB=dolphinnext
    DBUSER=docker
    DBPASS=docker
    DBHOST=localhost
    DBPORT=3306
    SSHPATH=/export/.dolphinnext/.ssh
    AMZPATH=/export/.dolphinnext/.amz
    GOOGPATH=/export/.dolphinnext/.goog
    AMAZON=z7***********
    SALT=23******
    PEPPER=3d******
    MASTER=u7******
    VERIFY=2s******

* **DB:** Database name.
* **DBUSER:** Name for a database user.
* **DBPASS:** Password for a database user.
* **DBHOST:** MySQL Server Hostname.
* **DBPORT:** MySQL Server Port.
* **SSHPATH:** Secure path to save ssh files.
* **AMZPATH:** Secure path to save amazon files.
* **GOOGPATH:** Secure path to save google files.
* **AMAZON:** It is random data (salt) that is used for encrypting Amazon keys.
* **SALT,PEPPER,MASTER,VERIFY:** It is random data that is used for user authentication.

Next section called **CONFIG**.::
    
    [CONFIG]
    ENV_PATH=""
    TIMEZONE=America/New_York
    RUNPATH=../tmp/pub
    TEMPPATH=../tmp
    API_URL = http://localhost/dolphinnext
    BASE_PATH = http://localhost:8080/dolphinnext
    PUBWEB_URL = http://localhost:8080/dolphinnext/tmp/pub
    OCPU_URL = http://localhost
    DEBROWSER_URL = http://localhost
    OCPU_PUBWEB_URL = http://localhost/dolphinnext/tmp/pub
    NEXTFLOW_VERSION = 19.10.0
    LDAP_SERVER=test
    DN_STRING=test
    BIND_USER= SVCLinuxLDAPAuth
    BIND_PASS=test
    EMAIL_SENDER=test@test.edu
    EMAIL_ADMIN=test@test.edu
    

* **ENV_PATH** is an optional profile path to be sourced before executing any commands. (eg. /home/.bashrc)
* **TIMEZONE:** Sets the default timezone used by all date/time functions.
* **RUNPATH:** Relative path to keep run logs.
* **TEMPPATH:** Relative path to keep temporary created files.
* **API_URL** DolphinNext URL inside of the docker container. It will be used when API calls are received.
* **BASE_PATH** DolphinNext URL outside of the docker container. 
* **PUBWEB_URL:** URL to reach public web directory (eg. http://localhost:8080/dolphinnext/tmp/pub for localhost)
* **OCPU_URL:** URL to reach local OCPU server (eg. http://localhost for localhost in which http://localhost/ocpu exist in the server)
* **DEBROWSER_URL:** URL to reach DEBrowser server (eg. http://localhost for localhost in which http://localhost/debrowser exist in the server)
* **OCPU_PUBWEB_URL:** URL to reach local pubweb directory (eg. http://localhost/dolphinnext/tmp/pub for localhost) 
* **NEXTFLOW_VERSION:** NEXTFLOW version to be used.
* **LDAP_SERVER,DN_STRING,BIND_USER,BIND_PASS:** Configuration parameters for LDAP Server.
* **EMAIL_SENDER:** The e-mail of the sender when DolphinNext sends e-mail.
* **EMAIL_ADMIN:** The e-mail(s) of the admin who will receive notification from DolphinNext server.

.. note:: RUNPATH, OCPU_PUBWEB_URL and PUBWEB_URL should end with same directory structure (tmp/pub)


Last section called **UICONFIG**.::

    [UICONFIG]
    COMPANY_NAME=Test Server
    ALLOW_SIGNUP=true
    ALLOW_SIGNUPGOOGLE=true
    SHOW_WIZARD=true
    ; User Preferences for profile page 
    SHOW_AMAZON_KEYS=true
    SHOW_GOOGLE_KEYS=true
    SHOW_SSH_KEYS=true
    SHOW_GROUPS=true
    SHOW_GITHUB=true
    ; User Preferences for run page 
    SHOW_RUN_LOG=true
    SHOW_RUN_TIMELINE=true
    SHOW_RUN_REPORT=true
    SHOW_RUN_DAG=true
    SHOW_RUN_TRACE=true
    SHOW_RUN_NEXTFLOWLOG=true  
    SHOW_RUN_NEXTFLOWNF=true
    SHOW_RUN_NEXTFLOWCONFIG=true
    
    
* **COMPANY_NAME:** Name of the company that will be used in the webpage.
* **ALLOW_SIGNUP:** Toogle the sign-up button in the home page.
* **ALLOW_SIGNUPGOOGLE:** Toogle the google sign-in button in the home page.
* **SHOW_WIZARD:** Toogle wizard in the home page.
* **SHOW_AMAZON_KEYS:** Toogle Amazon Keys tab in the profile section.
* **SHOW_GOOGLE_KEYS:** Toogle Google Keys tab in the profile section.
* **SHOW_SSH_KEYS:** Toogle SSH Keys tab in the profile section.
* **SHOW_GROUPS:** Toogle Groups tab in the profile section.
* **SHOW_GITHUB:** Toogle Github tab in the profile section.
* **SHOW_RUN_LOG:** Toogle Log.txt file in the Log tab of the run page.
* **SHOW_RUN_TIMELINE:** Toogle timeline file in the Log tab of the run page.
* **SHOW_RUN_REPORT:** Toogle report file in the Log tab of the run page.
* **SHOW_RUN_DAG:** Toogle DAG file in the Log tab of the run page.
* **SHOW_RUN_TRACE:** Toogle trace file in the Log tab of the run page.
* **SHOW_RUN_NEXTFLOWLOG:** Toogle .nextflow.log file in the Log tab of the run page. 
* **SHOW_RUN_NEXTFLOWNF:** Toogle nextflow.nf file in the Log tab of the run page.
* **SHOW_RUN_NEXTFLOWCONFIG:** Toogle nextflow.config file in the Log tab of the run page.



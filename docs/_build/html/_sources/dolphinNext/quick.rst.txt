*****************
Quick Start Guide
*****************

Signing Up
==========

This guide will walk you through how to start using DolphinNext pipelines. First off, you need to enter DolphinNext web page: https://dolphinnext.umassmed.edu/ and click **Sign Up** or **Sign in with Google** buttons. You will be asked to enter your institute information. An email will be sent to you upon verification of your information. 

.. image:: dolphinnext_images/sign_in.png
	:align: center
	:width: 99%

Creating Profile
================

Once you enter DolphinNext platform, you need to complete following steps to submit jobs to specifed hosts. 

.. note:: If you have any issues/questions about creating profiles please contact us on: support@dolphinnext.com

1. First install software dependencies in the host machine: 
    -  Install `Singularity (Version 3) <https://sylabs.io/guides/3.0/user-guide/installation.html#install-on-linux>`_ or `Docker <https://docs.docker.com/install/linux/docker-ce/ubuntu/>`_ for pipelines::
    
        ## Remove old version of Singularity
        # sudo rm -rf /usr/local/libexec/singularity /usr/local/var/singularity /usr/local/etc/singularity /usr/local/bin/singularity /usr/local/bin/run-singularity /usr/local/etc/bash_completion.d/singularity
        
        ## Install Singularity Version 3
        apt-get install -y build-essential libssl-dev uuid-dev libgpgme11-dev libseccomp-dev pkg-config squashfs-tools
        wget https://dl.google.com/go/go1.12.7.linux-amd64.tar.gz
        tar -C /usr/local -xzf go1.12.7.linux-amd64.tar.gz
        export PATH=$PATH:/usr/local/go/bin
        export VERSION=3.2.1 
        wget https://github.com/sylabs/singularity/releases/download/v${VERSION}/singularity-${VERSION}.tar.gz
        tar -xzf singularity-${VERSION}.tar.gz
        cd singularity
        ./mconfig && make -C ./builddir 
        sudo make -C ./builddir install
        
        ## Uninstall Old Versions of Docker
        # sudo apt-get remove docker docker-engine docker.io
        
        ## Install Docker
        sudo apt install docker.io
    
    -  Install `Java v8+ <https://www.java.com/en/download/help/linux_x64_install.xml#install>`_ for Nextflow::
    
        apt-get install -y openjdk-8-jdk && \
        apt-get install -y ant && \
        apt-get clean;

        # Fix certificate issues
        apt-get update && \
        apt-get install ca-certificates-java && \
        apt-get clean && \
        update-ca-certificates -f;
        export JAVA_HOME=/usr/lib/jvm/java-8-openjdk-amd64/
    
    
    -  Install `Nextflow <https://www.nextflow.io/>`_::
    
        ## To install to your ~/bin directory:
        mkdir ~/bin
        cd ~/bin
        curl -fsSL get.nextflow.io | bash

        # Add Nextflow binary to your bin PATH or any accessible path in your environment:
        chmod 755 nextflow
        mv nextflow ~/bin/
        # OR system-wide installation:
        # sudo mv nextflow /usr/local/bin
        

2. Second, add SSH Key.
    -  Click on the "**Profile**" button at the top right of the screen, and click "**SSH Keys**" tab in profile page.
    -  Click on “**Add SSH Key**” button.
    -  You can **Create New** or **Enter Existing** SSH key pairs in the opened window.
    -  Please check our `Tutorial Video <https://youtu.be/7wH2NjXSebA>`_ or `Documention regarding to SSH Keys <profile.html#ssh-keys>`_ for details. 

3. Go to host machine and paste public SSH key into ``~/.ssh/authorized_keys`` file. 
    -  Please check our `Tutorial Video <https://youtu.be/7wH2NjXSebA>`_ which shows how to edit ``authorized_keys`` file. 

4. Go to **run environments** section and click add "**Add Environment**" button and specify the hosts you want to use.

    .. image:: dolphinnext_images/profile_tabs2.png
	   :align: center
    
    
    -  **Username/Hostname:** You should enter your username and hostname of the host which you would like to connect. For instance, for us2r@ghpcc06.umassrc.org::
    
        Username: us2r
        Hostname: ghpcc06.umassrc.org
    
    -  **SSH Keys:** choose SSH keys defined in the SSH keys tab.
    -  **Run Command:** You can run the commands before the nextflow job starts. Please make sure you have added Singularity/Docker and JAVA to $PATH environment before starting nextflow. E.g. You can use following command to load modules:: 
    
        source /etc/bashrc && module load java/1.8.0_77 && module load singularity/singularity-3.4.0
    
    -  **Nextflow Path:** If nextflow path is not added to $PATH environment, you can define the path in this block. e.g.:: 
    
        ~/bin
        
    -  **Profile Variables:** Since most of the pipelines uses genome reference and index files, you can define a download directory to keep these files. If multiple people are going to use DolphinNext, we would suggest using a shared path in your cluster. e.g.::
        
        params.DOWNDIR="/share/dolphinnext/downloads"
        
    -  **Executor Settings for Nextflow:** You can set the system where nextflow itself is initiated. Currently local, sge, slurm and lsf executors are supported by DolphinNext to initiate nextflow and it will be only used for running nextflow itself::
    
        Suggested parameters: long 8GB 1CPU 5000-8000min
    
    -  **Executor of Nextflow Jobs:** This setting will be used if you don’t set any parameter in advanced section of your run page::
    
        Suggested parameters: short 20GB 1CPU 240min

Once you complete these steps, you're now able to submit jobs to specifed hosts.

.. warning:: Please note that DolphinNext doesn't provide free access to our UMASS clusters. Instead, you need to use your run environment (High Performance Computing (HPC) environments, Amazon cloud services or personal workstations) to execute pipelines. 


Running Pipelines
=================

.. raw:: html

    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; height: auto;">
        <iframe src="https://www.youtube.com/embed/gaq_LwewFPA" frameborder="0" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
    </div>
    </br>


1. The easiest way to run pipeline is using main page by clicking the **Biocore DolphinNext** button at the top left of the screen. Now, you can investigate publicly available pipelines as shown at below and select the pipeline you want run by clicking **Learn More** button.

    .. image:: dolphinnext_images/main_page.png
	   :align: center


2. Once pipeline is loaded, you will notice "Run" button at the right top of the page.


    .. image:: dolphinnext_images/project_runbutton.png
	   :align: center
	   :width: 35%


3. This button opens new window where you can create new project by clicking "Create a Project" button. After entering and saving the name of the project, it will be added to your project list. Now you can select your project by clicking on the project as shown in the figure below.

    .. image:: dolphinnext_images/project_pipeselect.png
	   :align: center

4. Now, you may proceed with entering run name which will be added to your run list of the project. Clicking "Save run" will redirect to "run page".

5. Initially, in the header of the run page, orange ``Waiting`` button will be shown. In order to initiate run, following data need to be entered:

    .. image:: dolphinnext_images/run_header_waiting.png
	   :align: center

    A. **Work Directory:**  Full path of the directory, where nextflow runs will be executed.
    
        .. image:: dolphinnext_images/run_params_work.png
	   :align: center
	   :width: 99%
    
    B. **Run Environment:** Profile that is created in the `profile <profile.html>`_  page. If `Amazon profile <profile.html#b-defining-amazon-profile>`_  is selected, then status of the profile should to be at the stage of **running**.
    
        .. image:: dolphinnext_images/run_params_env.png
	   :align: center
	   :width: 99%
    
    C. **Inputs:** Value and path of the files need to be entered. 

        .. image:: dolphinnext_images/run_params_inputs.png
	   :align: center
	   :width: 50%

        For detailed information about adding files, you can check our tutorial video:
        
        .. raw:: html

            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; height: auto;">
                <iframe src="https://www.youtube.com/embed/3QaAqdyB11w" frameborder="0" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
            </div>
            </br>
 
 
 
6. Once all requirements are satisfied, ``Waiting`` button will turn in to green ``ready to run`` button as shown below. You can initiate your run by clicking ``ready to run`` button. Please go through `run page <run.html>`_ for detailed explanation about each module is used.
    
.. image:: dolphinnext_images/run_header_ready.png
	:align: center




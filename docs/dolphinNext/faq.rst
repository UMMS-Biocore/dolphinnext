**************************
Frequently Asked Questions
**************************

.. contents:: Table of Contents

Installation Guide
==================

How can I install singularity?
------------------------------
Install `Singularity (Version 3) <https://sylabs.io/guides/3.0/user-guide/installation.html#install-on-linux>`_ for pipelines::
    
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
        

How can I install docker?
-------------------------
Installing `Docker <https://docs.docker.com/install/linux/docker-ce/ubuntu/>`_ for pipelines::

    ## Uninstall Old Versions of Docker
    # sudo apt-get remove docker docker-engine docker.io
        
    ## Install Docker
    sudo apt install docker.io



How can I install nextflow?
---------------------------
`JAVA (v8+) <faq.html#how-can-i-install-java>`_ should be installed before installing Nextflow. 
Installing  `Nextflow <https://www.nextflow.io/>`_::

    ## To install to your ~/bin directory:
    mkdir ~/bin
    cd ~/bin
    curl -fsSL get.nextflow.io | bash

    # Add Nextflow binary to your bin PATH or any accessible path in your environment:
    chmod 755 nextflow
    mv nextflow ~/bin/
    # OR system-wide installation:
    # sudo mv nextflow /usr/local/bin


How can I install JAVA?
-----------------------
Installing `Java v8+ <https://www.java.com/en/download/help/linux_x64_install.xml#install>`_ for Nextflow::

    apt-get install -y openjdk-8-jdk && \
    apt-get install -y ant && \
    apt-get clean;

    # Fix certificate issues
    apt-get update && \
    apt-get install ca-certificates-java && \
    apt-get clean && \
    update-ca-certificates -f;
    export JAVA_HOME=/usr/lib/jvm/java-8-openjdk-amd64/
    

Connection Issues
=================

Why can't I validate my SSH Keys?
---------------------------------

Please check following guide to find out the issue.

    1. While copying and pasting you ssh keys, please don't forget to copy initial part of the ssh key (eg. ``ssh-rsa``). 
        It should cover all of the following example key file::
        
                    ssh-rsa
                    AA1AB3N4nX3a....................
                    ................................
                    ................................
                    ...............b9Rj @dolphinnext

    2. The SSH protocol requires following file/directory permissions to establish secure connections. So please execute following commands to make sure SSH related files are not writeable by other users::
    
        chmod 700 ~/.ssh
        chmod 600 ~/.ssh/authorized_keys
    
    
    3. Your **home directory** shoudn't be **writeable** by other users. If you need to share your files with everyone, don't set permission of your home directory to 777. It creates security issues and blocks ssh connection. You can set to more secure options such as 750, 755 or 754.

How can I create SSH keys in my computer?
-----------------------------------------
You can reach your key pairs in your computer at default location: ``~/.ssh/id_rsa`` for private and ``~/.ssh/id_rsa.pub`` for public key. If these files are not exist or you want to create new ones, then on the command line, enter::

    ssh-keygen -t rsa

You will be prompted to supply a filename and a password. In order to accept the default filename (and location) for your key pair, press Enter without entering a filename. Your SSH keys will be generated using the default filename (``id_rsa`` and ``id_rsa.pub``).


Run Questions
=============

I can not reach my files in the file window
-------------------------------------------

There might be a connection issue, please check following steps:

    1. The SSH protocol requires following file/directory permissions to establish secure connections. So please execute following commands to make sure SSH related files are not writeable by other users::
    
        chmod 700 ~/.ssh
        chmod 600 ~/.ssh/authorized_keys
    
    
    2. Your **home directory** shoudn't be **writeable** by other users. If you need to share your files with everyone, don't set permission of your home directory to 777. It creates security issues and blocks ssh connection. You can set to more secure options such as 750, 755 or 754.


Error: Run directory cannot be created
--------------------------------------

There might be a connection issue, please check `Why can't I validate my SSH Keys <faq.html#why-can-t-i-validate-my-ssh-keys>`_ section.


Profile Questions
=================

How should I configure my executor settings?
-------------------------------------------- 

In DolphinNext, there are 4 different section to control your executor settings. First two fields are defined in **profile -> run environment**, and rest of the fields are adjusted in the **advanced tab** of run page. If any option other than local and ignite, is selected, additional settings will be prompt for ``Queue/Partition``, ``Memory(GB)``, ``CPU`` and ``Time(min.)``.

**1. Executor of Nextflow (in the profile -> run environment)**:

    Nextflow itself is initiated with this method. Currently, local, sge, slurm, and lsf executors are supported by DolphinNext to initiate nextflow. For `sge`, `slurm`, and `lsf` executors, it will be only used for running nextflow itself, so the time limit should be long enough to execute all of the processes in the pipeline. For `local` execution, it will limit the total amount of memory and CPU could be used by the run, so these values should be close enough to the maximum capacity of the memory and CPU.  
    
    - e.g. suggested parameters for sge/slurm/lsf: long (queue) 8 (GB Memory) 1 (CPU) 5000-8000 (min, Time)
    - e.g. suggested parameters for local: 100 (GB Memory) 8 (CPU) 
    
**2. Executor of Nextflow Jobs (in the profile -> run environment)**:

    This setting will be used as default setting for submitted jobs by Nextflow. If you don’t set any parameter in advanced section of your run page.
    
    - e.g. suggested parameters for sge/slurm/lsf: short (queue) 20 (GB Memory) 1 (CPU) 240 (min, Time)
    - e.g. suggested parameters for local/ignite: 20 (GB Memory) 1 (CPU) 
    
**3. Executor Settings for All Processes (in the advanced tab of run page)**:

    This setting will overwrite Executor of Nextflow Jobs (in the profile) and set default setting for Nextflow Jobs. 
    
    - e.g. suggested parameters for sge/slurm/lsf: short (queue) 20 (GB Memory) 1 (CPU) 240 (min, Time)
    - e.g. suggested parameters for local/ignite: 20 (GB Memory) 1 (CPU)
    
**4. Executor Settings for Each Process (in the advanced tab of run page)**:

    If particular process requires different parameters other than the defaults (which are defined in **Executor Settings for All Processes** or **Executor of Nextflow Jobs** sections), you can overwrite the general settings by clicking the checkbox of process that you want to change. This will only affect the settings of clicked process and keep the original settings for the rest of the processes.

    - e.g. suggested parameters for sge/slurm/lsf: long (queue) 20 (GB Memory) 4 (CPU) 1000-5000 (min, Time)
    - e.g. suggested parameters for local/ignite: 20 (GB Memory) 4 (CPU)

.. note::  In case of non-standart resources or settings is required for executor, then you can specify these parameters by using **Other options** box. For instance, to submit SGE job with 3 CPU by using paralel environments, you may enter ``-pe orte 3`` (to use MPI for distributed-memory machines) or ``-pe smp 3`` (to use OpenMP for shared-memory machines) in the **Other options** box and just leave the CPU box empty.
